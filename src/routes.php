<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\DB;

return function (\Slim\App $app) {

    // Create user
    $app->post('/users', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? null;

        if (!$username) {
            $response->getBody()->write(json_encode(['error' => 'Username is required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $pdo = DB::getInstance();
        try {
            $stmt = $pdo->prepare('INSERT INTO users (username) VALUES (:username)');
            $stmt->execute(['username' => $username]);
            $userId = $pdo->lastInsertId();

            $response->getBody()->write(json_encode(['id' => $userId, 'username' => $username]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['error' => 'Username already exists']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }
    });

    // Create group
    $app->post('/groups', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $groupName = $data['name'] ?? null;

        if (!$groupName) {
            $response->getBody()->write(json_encode(['error' => 'Group name is required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $pdo = DB::getInstance();

        try {
            $stmt = $pdo->prepare('INSERT INTO chat_groups (name) VALUES (:name)');
            $stmt->execute(['name' => $groupName]);
            $groupId = $pdo->lastInsertId();

            $response->getBody()->write(json_encode(['id' => $groupId, 'name' => $groupName]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['error' => 'Group already exists']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }
    });

    // Join group
    $app->post('/groups/{id}/join', function (Request $request, Response $response, $args) {
        $groupId = $args['id'];
        $data = $request->getParsedBody();
        $userId = $data['user_id'] ?? null;

        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'user_id is required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $pdo = DB::getInstance();

        // Check if group exists
        $stmt = $pdo->prepare('SELECT id FROM chat_groups WHERE id = :id');
        $stmt->execute(['id' => $groupId]);
        if (!$stmt->fetch()) {
            $response->getBody()->write(json_encode(['error' => 'Group not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Check if user exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        if (!$stmt->fetch()) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Try to insert into group_memberships
        try {
            $stmt = $pdo->prepare('INSERT INTO group_memberships (user_id, group_id) VALUES (:user_id, :group_id)');
            $stmt->execute([
                'user_id' => $userId,
                'group_id' => $groupId
            ]);

            $response->getBody()->write(json_encode([
                'message' => "User $userId joined group $groupId"
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['error' => 'User already joined this group']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }
    });

    // Send message to group
    $app->post('/groups/{id}/messages', function (Request $request, Response $response, $args) {
        $groupId = $args['id'];
        $data = $request->getParsedBody();

        $userId = $data['user_id'] ?? null;
        $content = $data['content'] ?? null;

        if (!$userId || !$content) {
            $response->getBody()->write(json_encode(['error' => 'user_id and content are required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $pdo = DB::getInstance();

        // Check if user is in group
        $stmt = $pdo->prepare('SELECT * FROM group_memberships WHERE user_id = :user_id AND group_id = :group_id');
        $stmt->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        if (!$stmt->fetch()) {
            $response->getBody()->write(json_encode(['error' => 'User is not a member of this group']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Insert message
        $stmt = $pdo->prepare('INSERT INTO messages (user_id, group_id, content) VALUES (:user_id, :group_id, :content)');
        $stmt->execute([
            'user_id' => $userId,
            'group_id' => $groupId,
            'content' => $content
        ]);

        $response->getBody()->write(json_encode(['message' => 'Message sent']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    });

    // List group messages
    $app->get('/groups/{id}/messages', function (Request $request, Response $response, $args) {
        $groupId = $args['id'];
        $pdo = DB::getInstance();

        // Check if group exists
        $stmt = $pdo->prepare('SELECT id FROM chat_groups WHERE id = :id');
        $stmt->execute(['id' => $groupId]);
        if (!$stmt->fetch()) {
            $response->getBody()->write(json_encode(['error' => 'Group not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Fetch messages with usernames
        $stmt = $pdo->prepare('
            SELECT messages.id, users.username, messages.content, messages.created_at
            FROM messages
            JOIN users ON messages.user_id = users.id
            WHERE messages.group_id = :group_id
            ORDER BY messages.created_at ASC
        ');
        $stmt->execute(['group_id' => $groupId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });


};

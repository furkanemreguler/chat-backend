<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;

$container = new Container();
AppFactory::setContainer($container);

$app = AppFactory::create();

// add Json body parse middleware
$app->addBodyParsingMiddleware();

// Test route
$app->get('/', function ($request, $response) {
    $response->getBody()->write("Chat backend is running 🚀");
    return $response;
});

// Routes
(require __DIR__ . '/../src/routes.php')($app);

$app->run();


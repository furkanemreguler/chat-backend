# Chat Backend API

A lightweight RESTful backend application for group-based chat functionality, built using PHP, Slim Framework, and SQLite.

## Features

- User creation
- Chat group creation
- Joining users to groups
- Sending messages to groups
- Listing messages within a group
- Input validation and error handling
- HTTP status code consistency
- Organized routing and file structure

## Technology Stack

- PHP 8.4
- Slim Framework 4
- SQLite (via PDO)
- Composer for dependency management
- PSR-4 autoloading

## Project Structure

```
- database/
  - schema.sql        # SQLite schema definition
- public/
  - index.php         # Slim application entry point
- src/
  - DB.php            # Singleton database connection
  - routes.php        # All endpoint definitions
- .gitignore          # Excludes database and vendor files
- composer.json       # Dependency definitions
- README.md           # Project documentation
```

## Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/furkanemreguler/chat-backend.git
   cd chat-backend
   ```
2. **Install dependencies**
   ```bash
   composer install
   ```
3. **Initialize the database**
   ```bash
   sqlite3 database/database.sqlite < database/schema.sql
   ```
4. **Start the development server**
   ```bash
   php -S localhost:8000 -t public
   ```

## API Endpoints

### Create User

**POST** `/users`

- Payload:
  ```json
  { "username": "your_username" }
  ```

### Create Group

**POST** `/groups`

- Payload:
  ```json
  { "name": "group_name" }
  ```

### Join Group

**POST** `/groups/{id}/join`

- Path Parameter: `id` = group ID
- Payload:
  ```json
  { "user_id": 1 }
  ```

### Send Message

**POST** `/groups/{id}/messages`

- Path Parameter: `id` = group ID
- Payload:
  ```json
  {
    "user_id": 1,
    "content": "Your message here"
  }
  ```

### List Messages

**GET** `/groups/{id}/messages`

- Path Parameter: `id` = group ID
- Example Response:
  ```json
  [
    {
      "id": 1,
      "username": "username",
      "content": "Hello everyone!",
      "created_at": "2025-07-09 12:00:00"
    }
  ]
  ```

## Error Handling

- **400 Bad Request**: Missing required fields
- **404 Not Found**: Group or user not found
- **409 Conflict**: Duplicate username or group name, or user already in group

## Author

- **Furkan Emre Güler**
- Computer Science & Engineering Graduate
- GitHub: [furkanemreguler](https://github.com/furkanemreguler)

---

*This README is intended to provide a clear overview and setup guide for the Chat Backend API.*


# Flappy Bird

PHP Flappy Bird game with account registration, login, Banana API challenges, and saved player scores.

## Requirements

- PHP 8.0 or newer with PDO MySQL and cURL extensions
- MySQL or MariaDB (XAMPP includes both)

## Setup with XAMPP

1. Put the project in the XAMPP `htdocs` directory and start Apache and MySQL.
2. Import [`database/schema.sql`](database/schema.sql) in phpMyAdmin or run it with the MySQL client.
3. Create `config/database.php` locally (it is intentionally gitignored) with this connection configuration:

   ```php
   <?php

   $host = getenv("DB_HOST") ?: "localhost";
   $database = getenv("DB_NAME") ?: "flappy_bird_db";
   $username = getenv("DB_USER") ?: "root";
   $password = getenv("DB_PASSWORD") ?: "";

   return new PDO(
       "mysql:host={$host};dbname={$database};charset=utf8mb4",
       $username,
       $password,
       [
           PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
           PDO::ATTR_EMULATE_PREPARES => false,
       ]
   );
   ```

4. Open `http://localhost/Flappy_Bird/public/` and register a player.

The database settings can be supplied through `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`. The ignored local config file is loaded by the pages and score API.

## Project structure

- `public/` — web pages, static assets, and JSON API endpoints
- `src/Auth/` — session and authentication helpers
- `src/Models/` — user and score database operations
- `src/Services/` — integration with the Banana challenge API
- `config/database.php` — local PDO connection (ignored by Git)
- `database/schema.sql` — users and scores tables
- `tests/run_tests.php` — lightweight SQLite-backed model tests

## Tests

Run the model tests from the project root:

```sh
php tests/run_tests.php
```

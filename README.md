# Flappy Bird

A PHP and JavaScript Flappy Bird game for a university assignment. Players register and log in, solve a Banana puzzle to unlock a game, and save scores to a leaderboard.

## Architecture

The browser renders the pages and game, and sends JSON requests to the PHP backend. The backend calls the external Banana API through `BananaApiService`, keeps the puzzle solution on the server, checks answers, and uses the database for accounts and scores. The browser communicates with our own JSON API under `public/api/v1/` for Banana challenges, answer checks, and score operations.

## Setup with XAMPP

1. Put the project folder in XAMPP's `htdocs` directory. Start Apache and MySQL from the XAMPP control panel.
2. Open phpMyAdmin, select **Import**, and import [`database/schema.sql`](database/schema.sql). This creates the `flappy_bird_db` database with the users and scores tables.
3. Copy `config/database.example.php` to `config/database.php`.
4. Edit `config/database.php` with your local MySQL host, database name, username, and password. The example uses obvious placeholders; replace them with your local settings. The local file is gitignored and should not be committed.
5. Open `http://localhost/Flappy_Bird/public/` and create an account.

Database values can also be supplied through the `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` environment variables.

## Folder structure

```text
public/
  index.php, login.php, register.php, profile.php, logout.php
  assets/css/style.css
  assets/js/game.js, api.js
  api/v1/banana.php, check_answer.php, scores.php
src/
  Auth/SessionManager.php
  Models/User.php, Score.php
  Services/BananaApiService.php
config/
  database.php             # Local config; ignored by Git
  database.example.php     # Safe template to copy
database/
  schema.sql
tests/
  run_tests.php
README.md
.gitignore
```

## Assignment themes

- **Version control:** `.gitignore` keeps local database credentials out of Git; the project can be tracked and committed as you develop.
- **Event-driven programming:** `public/assets/js/game.js` responds to keyboard and click events and runs the game loop.
- **Interoperability:** `src/Services/BananaApiService.php` communicates with the external Banana API; `public/api/v1/` exposes this project's own JSON endpoints.
- **Virtual identity:** `src/Auth/SessionManager.php` manages authenticated player sessions, and `src/Models/User.php` and `public/profile.php` provide player identity and profile details.

## Tests

Run the test suite from the project root:

```sh
php tests/run_tests.php
```

The model tests use an in-memory SQLite database and do not require the XAMPP MySQL service.

## GitHub Copilot-generated code

These functions have a `// Generated with GitHub Copilot` marker in the source:

- `SessionManager::csrfToken()` and `SessionManager::validateCsrfToken()` in `src/Auth/SessionManager.php`
- `loadLeaderboard()` and `checkBananaAnswer()` in `public/assets/js/api.js`

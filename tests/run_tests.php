<?php

require_once dirname(__DIR__) . "/src/Models/User.php";
require_once dirname(__DIR__) . "/src/Models/Score.php";

use App\Models\Score;
use App\Models\User;

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = new PDO("sqlite::memory:");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec(
    "CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )"
);
$pdo->exec(
    "CREATE TABLE scores (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        score INTEGER NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )"
);

$users = new User($pdo);
$userId = $users->create("player-one", "player@example.test", "secret123");
$user = $users->findByEmail("player@example.test");
expect($user !== null, "Created user should be found by email.");
expect(password_verify("secret123", $user["password"]), "User password should be hashed.");
expect($users->findById($userId)["username"] === "player-one", "User should be found by ID.");
expect($users->isRegistered("player-one", "other@example.test"), "Existing username should be detected.");

$scores = new Score($pdo);
$scores->save($userId, 3);
$scores->save($userId, 7);
expect($scores->bestForUser($userId) === 7, "Best score should be the highest saved score.");
expect(
    $scores->leaderboard()[0]["username"] === "player-one"
        && (int) $scores->leaderboard()[0]["best_score"] === 7,
    "Leaderboard should rank each player's best score."
);

echo "All tests passed.\n";

<?php

require_once dirname(__DIR__) . "/src/Auth/SessionManager.php";
require_once dirname(__DIR__) . "/src/Models/User.php";
require_once dirname(__DIR__) . "/src/Models/Score.php";
$pdo = require dirname(__DIR__) . "/config/database.php";

use App\Auth\SessionManager;
use App\Models\Score;
use App\Models\User;

SessionManager::requireLogin();
$user = (new User($pdo))->findById($_SESSION["user_id"]);

if (!$user) {
    SessionManager::logout();
    header("Location: login.php");
    exit;
}

$bestScore = (new Score($pdo))->bestForUser($_SESSION["user_id"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="profile-page">
    <main class="profile-card">
        <div class="avatar">👤</div>
        <h2>Player Profile</h2>
        <div class="details">
            <p><strong>Username:</strong><br>
                <?= htmlspecialchars($user["username"], ENT_QUOTES, "UTF-8") ?></p>
            <p><strong>Email:</strong><br>
                <?= htmlspecialchars($user["email"], ENT_QUOTES, "UTF-8") ?></p>
            <p><strong>Account created:</strong><br>
                <?= htmlspecialchars($user["created_at"], ENT_QUOTES, "UTF-8") ?></p>
            <p><strong>Best score:</strong><br><?= $bestScore ?></p>
        </div>
        <a href="index.php">← Back to Game</a>
    </main>
</body>
</html>

<?php

require_once dirname(__DIR__) . "/src/Auth/SessionManager.php";
use App\Auth\SessionManager;

SessionManager::requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(SessionManager::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
    <title>Flappy Bird Challenge</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="game-page">
    <div class="top-bar">
        <button type="button" class="profile-button" id="profileButton"
                aria-label="Open profile menu" aria-expanded="false">👤</button>
        <div class="profile-menu" id="profileMenu">
            <div class="profile-name">
                <?= htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8") ?>
            </div>
            <a href="profile.php">👤 Player Details</a>
            <a href="logout.php" class="logout-link">🚪 Logout</a>
        </div>
    </div>

    <h1>🐦 Flappy Bird Challenge</h1>
    <main id="main-content">
        <section class="game-section" aria-label="Flappy Bird game">
            <div id="game-container">
                <div id="score">Score: 0</div>
                <div id="bird"></div>
                <div id="message">Solve the Banana puzzle to play!</div>
            </div>
            <button id="startButton">Load New Challenge</button>
            <p id="scoreSaveStatus" role="status"></p>
            <p>Press SPACE or click inside the game to flap!</p>
        </section>

        <section id="bananaChallenge" aria-labelledby="bananaTitle">
            <h2 id="bananaTitle">🍌 Banana API Challenge</h2>
            <p id="apiStatus">Click "Load Banana Challenge" to begin.</p>
            <img id="bananaImage" alt="Banana API Challenge">
            <div id="answerSection" hidden>
                <p>Enter your answer:</p>
                <input type="number" id="answerInput" placeholder="Answer">
                <button id="submitAnswer">Submit Answer</button>
                <p id="challengeMessage" role="status"></p>
            </div>
        </section>

        <section id="leaderboard" aria-labelledby="leaderboardTitle">
            <h2 id="leaderboardTitle">🏆 Leaderboard</h2>
            <p id="leaderboardStatus" role="status" aria-live="polite">Loading scores...</p>
            <ol id="leaderboardList"></ol>
        </section>
    </main>
    <script src="assets/js/api.js"></script>
    <script src="assets/js/game.js"></script>
</body>
</html>

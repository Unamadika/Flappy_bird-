
<?php
require_once "auth.php";
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Flappy Bird Challenge</title>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    padding: 20px;
    font-family: Arial, sans-serif;
    background: #70c5ce;
    text-align: center;
}

.top-bar {
    position: absolute;
    top: 18px;
    right: 25px;
    z-index: 100;
}

.profile-button {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: 2px solid white;
    background: white;
    color: #333;
    font-size: 22px;
    cursor: pointer;
}

.profile-menu {
    display: none;
    position: absolute;
    top: 55px;
    right: 0;
    width: 190px;
    background: white;
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    text-align: left;
}

.profile-menu.show {
    display: block;
}

.profile-name {
    font-weight: bold;
    padding: 8px;
    overflow-wrap: anywhere;
}

.profile-menu a {
    display: block;
    padding: 11px;
    text-decoration: none;
    color: #333;
    border-radius: 6px;
}

.profile-menu a:hover {
    background: #eee;
}

.profile-menu .logout-link {
    color: #d93025;
}

h1 {
    color: white;
    text-shadow: 2px 2px #555;
    margin-top: 55px;
}

#main-content {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 25px;
    flex-wrap: wrap;
}

#game-container {
    position: relative;
    width: 400px;
    height: 600px;
    background: linear-gradient(#70c5ce 75%, #ded895 75%);
    border: 4px solid white;
    border-radius: 10px;
    overflow: hidden;
}

#bird {
    position: absolute;
    width: 35px;
    height: 30px;
    left: 80px;
    top: 250px;
    background: #ffd700;
    border: 2px solid #d49b00;
    border-radius: 50%;
    z-index: 5;
}

#bird::after {
    content: "";
    position: absolute;
    right: -8px;
    top: 8px;
    width: 12px;
    height: 8px;
    background: orange;
    border-radius: 50%;
}

.pipe {
    position: absolute;
    width: 65px;
    background: #36b54a;
    border: 3px solid #218c36;
}

#score {
    position: absolute;
    top: 15px;
    left: 0;
    width: 100%;
    color: white;
    font-size: 28px;
    font-weight: bold;
    text-shadow: 2px 2px #333;
    z-index: 10;
}

#message {
    position: absolute;
    top: 48%;
    width: 100%;
    color: white;
    font-size: 23px;
    font-weight: bold;
    text-shadow: 2px 2px #333;
    z-index: 10;
}

#startButton, #submitAnswer {
    padding: 12px 20px;
    margin: 12px;
    border: none;
    border-radius: 8px;
    background: #f4a623;
    color: #222;
    font-weight: bold;
    cursor: pointer;
}

#bananaChallenge {
    width: 380px;
    max-width: 100%;
    background: white;
    padding: 22px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

#bananaImage {
    display: none;
    max-width: 100%;
    width: 300px;
    height: auto;
    margin: 15px auto;
}

#answerInput {
    width: 80%;
    padding: 12px;
    border: 1px solid #aaa;
    border-radius: 6px;
}

#apiStatus { color: #555; }
#challengeMessage { font-weight: bold; }

@media (max-width: 850px) {
    #main-content {
        flex-direction: column;
        align-items: center;
    }

    #game-container {
        max-width: 100%;
    }

    .top-bar {
        right: 15px;
        top: 12px;
    }
}
</style>
</head>

<body>

<!-- PROFILE BUTTON -->
<div class="top-bar">
    <button
        type="button"
        class="profile-button"
        id="profileButton"
        aria-label="Open profile menu"
        aria-expanded="false"
    >
        👤
    </button>

    <div class="profile-menu" id="profileMenu">
        <div class="profile-name">
            <?= htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8") ?>
        </div>

        <a href="profile.php">👤 Player Details</a>
        <a href="logout.php" class="logout-link">🚪 Logout</a>
    </div>
</div>

<h1>🐦 Flappy Bird Challenge</h1>

<div id="main-content">

    <!-- GAME -->
    <div>
        <div id="game-container">
            <div id="score">Score: 0</div>
            <div id="bird"></div>
            <div id="message">
                Solve the Banana puzzle to play!
            </div>
        </div>

        <button id="startButton">
            Load New Challenge
        </button>

        <p>Press SPACE or click inside the game to flap!</p>
    </div>

    <!-- BANANA API -->
    <div id="bananaChallenge">
        <h2>🍌 Banana API Challenge</h2>

        <p id="apiStatus">
            Click "Load Banana Challenge" to begin.
        </p>

        <img id="bananaImage" alt="Banana API Challenge">

        <div id="answerSection" style="display: none;">
            <p>Enter your answer:</p>

            <input
                type="number"
                id="answerInput"
                placeholder="Answer"
            >

            <button id="submitAnswer">
                Submit Answer
            </button>

            <p id="challengeMessage"></p>
        </div>
    </div>

</div>

<script>
const profileButton = document.getElementById("profileButton");
const profileMenu = document.getElementById("profileMenu");

profileButton.addEventListener("click", function(event) {
    event.stopPropagation();

    profileMenu.classList.toggle("show");

    profileButton.setAttribute(
        "aria-expanded",
        profileMenu.classList.contains("show")
    );
});

document.addEventListener("click", function(event) {
    if (!profileMenu.contains(event.target) &&
        !profileButton.contains(event.target)) {
        profileMenu.classList.remove("show");
        profileButton.setAttribute("aria-expanded", "false");
    }
});
</script>

<script src="api.js"></script>
<script src="game.js"></script>

</body>
</html>

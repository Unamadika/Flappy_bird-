// File purpose: run the Flappy Bird game and connect its buttons to the game actions.

// Find the page elements the game needs to update.
const bird = document.getElementById("bird");
const gameContainer = document.getElementById("game-container");
const scoreDisplay = document.getElementById("score");
const message = document.getElementById("message");
const startButton = document.getElementById("startButton");
const submitAnswerButton = document.getElementById("submitAnswer");
const scoreSaveStatus = document.getElementById("scoreSaveStatus");
const profileButton = document.getElementById("profileButton");
const profileMenu = document.getElementById("profileMenu");

let birdY = 250;
let velocity = 0;
const gravity = 0.5;
let gameRunning = false;
let score = 0;
let pipes = [];
let gameLoop;
let pipeLoop;

// Stop the game and ask the player to solve a new Banana puzzle.
function loadChallenge() {
    gameRunning = false;
    clearInterval(gameLoop);
    clearInterval(pipeLoop);
    message.textContent = "Solve the Banana puzzle to play!";
    startButton.textContent = "Load New Challenge";
    scoreSaveStatus.textContent = "";
    loadBananaChallenge();
}

// Reset the bird and start the movement and pipe timers.
function startGame() {
    const oldPipes = document.querySelectorAll(".pipe");
    for (let index = 0; index < oldPipes.length; index++) {
        oldPipes[index].remove();
    }

    birdY = 250;
    velocity = 0;
    score = 0;
    pipes = [];
    gameRunning = true;
    bird.style.top = birdY + "px";
    scoreDisplay.textContent = "Score: 0";
    message.textContent = "";
    scoreSaveStatus.textContent = "";
    startButton.textContent = "Playing...";
    clearInterval(gameLoop);
    clearInterval(pipeLoop);
    gameLoop = setInterval(updateGame, 20);
    pipeLoop = setInterval(createPipe, 2000);
}

// Give the bird an upward push while a game is running.
function flap() {
    if (gameRunning) {
        velocity = -8;
    }
}

// Apply gravity and check whether the bird hits the top or ground.
function updateGame() {
    velocity += gravity;
    birdY += velocity;
    bird.style.top = birdY + "px";

    if (birdY <= 0 || birdY >= 570) {
        endGame();
        return;
    }

    movePipes();
}

// Add a pair of pipes at the right side of the game area.
function createPipe() {
    const gap = 160;
    const minHeight = 50;
    const maxTopHeight = 350;
    const topHeight = Math.floor(Math.random() * (maxTopHeight - minHeight)) + minHeight;
    const bottomHeight = 600 - topHeight - gap;
    const topPipe = document.createElement("div");
    topPipe.classList.add("pipe");
    topPipe.style.height = topHeight + "px";
    topPipe.style.left = "400px";
    topPipe.style.top = "0px";

    // The lower pipe uses the remaining height below the gap.
    const bottomPipe = document.createElement("div");
    bottomPipe.classList.add("pipe");
    bottomPipe.style.height = bottomHeight + "px";
    bottomPipe.style.left = "400px";
    bottomPipe.style.bottom = "0px";

    gameContainer.appendChild(topPipe);
    gameContainer.appendChild(bottomPipe);
    pipes.push({ top: topPipe, bottom: bottomPipe, scored: false });
}

// Move pipes left, award points, and remove pipes that leave the screen.
function movePipes() {
    for (let index = pipes.length - 1; index >= 0; index--) {
        const pipePair = pipes[index];
        const newLeft = parseInt(pipePair.top.style.left, 10) - 3;
        pipePair.top.style.left = newLeft + "px";
        pipePair.bottom.style.left = newLeft + "px";

        if (!pipePair.scored && newLeft + 65 < 80) {
            score++;
            scoreDisplay.textContent = "Score: " + score;
            pipePair.scored = true;
        }

        if (checkCollision(pipePair)) {
            endGame();
            return;
        }

        if (newLeft < -70) {
            pipePair.top.remove();
            pipePair.bottom.remove();
            pipes.splice(index, 1);
        }
    }
}

// Compare the bird rectangle with both pipe rectangles.
function checkCollision(pipePair) {
    const birdRect = bird.getBoundingClientRect();
    const topRect = pipePair.top.getBoundingClientRect();
    const bottomRect = pipePair.bottom.getBoundingClientRect();

    const hitsTopPipe = (
        birdRect.right > topRect.left &&
        birdRect.left < topRect.right &&
        birdRect.bottom > topRect.top &&
        birdRect.top < topRect.bottom
    );
    const hitsBottomPipe = (
        birdRect.right > bottomRect.left &&
        birdRect.left < bottomRect.right &&
        birdRect.bottom > bottomRect.top &&
        birdRect.top < bottomRect.bottom
    );

    return hitsTopPipe || hitsBottomPipe;
}

// Stop the timers, save the score, refresh the leaderboard, and load a new puzzle.
function endGame() {
    if (!gameRunning) return;

    gameRunning = false;
    clearInterval(gameLoop);
    clearInterval(pipeLoop);
    message.textContent = "Game Over! Score: " + score;
    scoreSaveStatus.textContent = "Saving score...";

    saveScore(score)
        .then(function () {
            scoreSaveStatus.textContent = "Score saved to your profile.";
            return loadLeaderboard();
        })
        .catch(function (error) {
            console.error(error);
            scoreSaveStatus.textContent = "Could not save score. Please try again later.";
        });

    setTimeout(loadChallenge, 1000);
}

// Connect the page controls to their actions.
startButton.addEventListener("click", loadChallenge);
submitAnswerButton.addEventListener("click", checkBananaAnswer);

document.addEventListener("keydown", function (event) {
    if (event.code === "Space" && gameRunning) {
        event.preventDefault();
        flap();
    }
});

gameContainer.addEventListener("click", flap);

// Toggle the profile menu when its button is clicked.
profileButton.addEventListener("click", function (event) {
    event.stopPropagation();
    profileMenu.classList.toggle("show");
    profileButton.setAttribute("aria-expanded", profileMenu.classList.contains("show"));
});

// Close the profile menu when the player clicks outside it.
document.addEventListener("click", function (event) {
    if (!profileMenu.contains(event.target) && !profileButton.contains(event.target)) {
        profileMenu.classList.remove("show");
        profileButton.setAttribute("aria-expanded", "false");
    }
});

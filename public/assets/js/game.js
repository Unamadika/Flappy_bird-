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

function loadChallenge() {
    gameRunning = false;
    clearInterval(gameLoop);
    clearInterval(pipeLoop);
    message.textContent = "Solve the Banana puzzle to play!";
    startButton.textContent = "Load New Challenge";
    scoreSaveStatus.textContent = "";
    loadBananaChallenge();
}

function startGame() {
    document.querySelectorAll(".pipe").forEach(pipe => pipe.remove());
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

function flap() {
    if (gameRunning) {
        velocity = -8;
    }
}

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

    const bottomPipe = document.createElement("div");
    bottomPipe.classList.add("pipe");
    bottomPipe.style.height = bottomHeight + "px";
    bottomPipe.style.left = "400px";
    bottomPipe.style.bottom = "0px";

    gameContainer.appendChild(topPipe);
    gameContainer.appendChild(bottomPipe);
    pipes.push({ top: topPipe, bottom: bottomPipe, scored: false });
}

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

function checkCollision(pipePair) {
    const birdRect = bird.getBoundingClientRect();
    const topRect = pipePair.top.getBoundingClientRect();
    const bottomRect = pipePair.bottom.getBoundingClientRect();

    return (
        birdRect.right > topRect.left &&
        birdRect.left < topRect.right &&
        birdRect.bottom > topRect.top &&
        birdRect.top < topRect.bottom
    ) || (
        birdRect.right > bottomRect.left &&
        birdRect.left < bottomRect.right &&
        birdRect.bottom > bottomRect.top &&
        birdRect.top < bottomRect.bottom
    );
}

function endGame() {
    if (!gameRunning) return;

    gameRunning = false;
    clearInterval(gameLoop);
    clearInterval(pipeLoop);
    message.textContent = "Game Over! Score: " + score;
    scoreSaveStatus.textContent = "Saving score...";

    saveScore(score)
        .then(() => {
            scoreSaveStatus.textContent = "Score saved to your profile.";
            return loadLeaderboard();
        })
        .catch(error => {
            console.error(error);
            scoreSaveStatus.textContent = "Could not save score. Please try again later.";
        });

    setTimeout(loadChallenge, 1000);
}

startButton.addEventListener("click", loadChallenge);
submitAnswerButton.addEventListener("click", checkBananaAnswer);

document.addEventListener("keydown", event => {
    if (event.code === "Space" && gameRunning) {
        event.preventDefault();
        flap();
    }
});

gameContainer.addEventListener("click", flap);

profileButton.addEventListener("click", event => {
    event.stopPropagation();
    profileMenu.classList.toggle("show");
    profileButton.setAttribute("aria-expanded", profileMenu.classList.contains("show"));
});

document.addEventListener("click", event => {
    if (!profileMenu.contains(event.target) && !profileButton.contains(event.target)) {
        profileMenu.classList.remove("show");
        profileButton.setAttribute("aria-expanded", "false");
    }
});

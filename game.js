const bird = document.getElementById("bird");
const gameContainer = document.getElementById("game-container");
const scoreDisplay = document.getElementById("score");
const message = document.getElementById("message");
const startButton = document.getElementById("startButton");

let birdY = 250;
let velocity = 0;
let gravity = 0.5;
let gameRunning = false;
let score = 0;
let pipes = [];
let gameLoop;
let pipeLoop;

function startGame() {
    // Remove old pipes
    document.querySelectorAll(".pipe").forEach(pipe => pipe.remove());

    birdY = 250;
    velocity = 0;
    score = 0;
    pipes = [];
    gameRunning = true;

    bird.style.top = birdY + "px";
    scoreDisplay.textContent = "Score: " + score;
    message.textContent = "";

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

    // Bird hits top or bottom
    if (birdY <= 0 || birdY >= 570) {
        endGame();
    }

    movePipes();
}

function createPipe() {
    const gap = 160;
    const minHeight = 50;
    const maxTopHeight = 350;

    const topHeight =
        Math.floor(Math.random() * (maxTopHeight - minHeight)) + minHeight;

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

    pipes.push({
        top: topPipe,
        bottom: bottomPipe,
        scored: false
    });
}

function movePipes() {
    pipes.forEach((pipePair, index) => {
        const currentLeft =
            parseInt(pipePair.top.style.left);

        const newLeft = currentLeft - 3;

        pipePair.top.style.left = newLeft + "px";
        pipePair.bottom.style.left = newLeft + "px";

        // Increase score
        if (!pipePair.scored && newLeft + 65 < 80) {
            score++;
            scoreDisplay.textContent = "Score: " + score;
            pipePair.scored = true;
        }

        // Collision detection
        if (checkCollision(pipePair)) {
            endGame();
        }

        // Remove pipes that leave the screen
        if (newLeft < -70) {
            pipePair.top.remove();
            pipePair.bottom.remove();
            pipes.splice(index, 1);
        }
    });
}

function checkCollision(pipePair) {
    const birdRect = bird.getBoundingClientRect();
    const topPipeRect = pipePair.top.getBoundingClientRect();
    const bottomPipeRect = pipePair.bottom.getBoundingClientRect();

    return (
        birdRect.right > topPipeRect.left &&
        birdRect.left < topPipeRect.right &&
        birdRect.bottom > topPipeRect.top &&
        birdRect.top < topPipeRect.bottom
    ) ||
    (
        birdRect.right > bottomPipeRect.left &&
        birdRect.left < bottomPipeRect.right &&
        birdRect.bottom > bottomPipeRect.top &&
        birdRect.top < bottomPipeRect.bottom
    );
}

function endGame() {
    gameRunning = false;

    clearInterval(gameLoop);
    clearInterval(pipeLoop);

    message.textContent =
        "Game Over! Your score: " + score +
        ". Press START to play again.";
}

// EVENT-DRIVEN PROGRAMMING

// Click event
startButton.addEventListener("click", startGame);

// Keyboard event
document.addEventListener("keydown", function(event) {
    if (event.code === "Space") {
        event.preventDefault();

        if (!gameRunning) {
            startGame();
        }

        flap();
    }
});

// Mouse click event
gameContainer.addEventListener("click", flap);

let bananaAnswer = null;

async function loadBananaChallenge() {
    const status = document.getElementById("apiStatus");
    const image = document.getElementById("bananaImage");
    const answerSection = document.getElementById("answerSection");
    const answerInput = document.getElementById("answerInput");
    const challengeMessage = document.getElementById("challengeMessage");

    bananaAnswer = null;
    status.textContent = "Loading a new Banana puzzle...";
    image.style.display = "none";
    answerSection.style.display = "none";
    answerInput.value = "";
    challengeMessage.textContent = "";

    try {
        const response = await fetch(
            "https://marcconrad.com/uob/banana/api.php"
        );

        if (!response.ok) {
            throw new Error("Banana API request failed");
        }

        const data = await response.json();

        if (!data.question || data.solution === undefined) {
            throw new Error("Invalid API response");
        }

        bananaAnswer = Number(data.solution);

        image.onload = () => {
            image.style.display = "block";
        };

        image.onerror = () => {
            status.textContent = "Could not display the puzzle image.";
        };

        image.src = data.question;

        answerSection.style.display = "block";
        status.textContent = "Solve this puzzle to play Flappy Bird!";

        console.log("Banana API challenge loaded:", data);

    } catch (error) {
        console.error(error);
        status.textContent =
            "Could not load puzzle. Click Load Banana Challenge to retry.";
    }
}

function checkBananaAnswer() {
    const answerInput = document.getElementById("answerInput");
    const challengeMessage =
        document.getElementById("challengeMessage");

    if (bananaAnswer === null) {
        challengeMessage.textContent = "Load a puzzle first.";
        return;
    }

    if (answerInput.value.trim() === "") {
        challengeMessage.textContent = "Enter your answer first.";
        return;
    }

    const playerAnswer = Number(answerInput.value);

    if (playerAnswer === bananaAnswer) {
        challengeMessage.textContent = "Correct answer! 🎉";

        document.getElementById("answerSection").style.display = "none";

        // Prevent reusing the same answer
        bananaAnswer = null;

        startGame();
    } else {
        challengeMessage.textContent = "Wrong answer! Try again. ❌";
        answerInput.value = "";
        answerInput.focus();
    }
}

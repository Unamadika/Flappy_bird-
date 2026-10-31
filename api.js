let bananaAnswer = null;

// Load a challenge from the Banana API
async function loadBananaChallenge() {
    const status = document.getElementById("apiStatus");
    const image = document.getElementById("bananaImage");
    const answerSection = document.getElementById("answerSection");
    const answerInput = document.getElementById("answerInput");
    const challengeMessage = document.getElementById("challengeMessage");

    try {
        status.textContent = "Loading Banana challenge...";
        challengeMessage.textContent = "";
        answerInput.value = "";
        answerSection.style.display = "none";
        image.style.display = "none";

        // Request a challenge from the external Banana API
        const response = await fetch(
            "https://marcconrad.com/uob/banana/api.php"
        );

        if (!response.ok) {
            throw new Error("Could not connect to the Banana API.");
        }

        // Convert API response to JSON
        const data = await response.json();

        // Store the correct answer
        bananaAnswer = Number(data.solution);

        // Display the image returned by the API
        image.src = data.question;
        image.style.display = "block";

        answerSection.style.display = "block";

        status.textContent =
            "Solve the challenge correctly to start the game!";

        console.log("Banana API response received successfully.");

    } catch (error) {
        console.error("Banana API error:", error);

        status.textContent =
            "Could not load the challenge. Please try again.";

        bananaAnswer = null;
    }
}

// Check the player's answer
function checkBananaAnswer() {
    const answerInput = document.getElementById("answerInput");
    const challengeMessage =
        document.getElementById("challengeMessage");

    const playerAnswer = Number(answerInput.value);

    if (bananaAnswer === null) {
        challengeMessage.textContent =
            "Please load a Banana challenge first.";
        return;
    }

    if (answerInput.value === "") {
        challengeMessage.textContent =
            "Please enter an answer.";
        return;
    }

    if (playerAnswer === bananaAnswer) {
        challengeMessage.textContent =
            "Correct! 🎉 Starting the game...";

        // Start the Flappy Bird game
        startGame();

    } else {
        challengeMessage.textContent =
            "Wrong answer ❌ Try again!";
    }
}
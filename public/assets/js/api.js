// Generated with GitHub Copilot
async function loadLeaderboard() {
    const status = document.getElementById("leaderboardStatus");
    const list = document.getElementById("leaderboardList");

    try {
        const response = await fetch("api/v1/scores.php");
        if (!response.ok) {
            throw new Error("Leaderboard request failed.");
        }

        const data = await response.json();
        if (!Array.isArray(data.scores)) {
            throw new Error("Invalid leaderboard response.");
        }

        list.replaceChildren();
        if (data.scores.length === 0) {
            status.textContent = "No scores yet. Be the first to play!";
            return;
        }

        data.scores.slice(0, 10).forEach(entry => {
            const item = document.createElement("li");
            const username = document.createElement("span");
            const score = document.createElement("strong");

            username.textContent = String(entry.username ?? "Player");
            score.textContent = String(entry.best_score ?? 0);
            item.append(username, score);
            list.appendChild(item);
        });

        status.textContent = "";
    } catch (error) {
        console.error(error);
        list.replaceChildren();
        status.textContent = "Leaderboard is unavailable right now. Please try again later.";
    }
}

async function loadBananaChallenge() {
    const status = document.getElementById("apiStatus");
    const image = document.getElementById("bananaImage");
    const answerSection = document.getElementById("answerSection");
    const answerInput = document.getElementById("answerInput");
    const challengeMessage = document.getElementById("challengeMessage");

    status.textContent = "Loading a new Banana puzzle...";
    image.style.display = "none";
    answerSection.hidden = true;
    answerInput.value = "";
    challengeMessage.textContent = "";

    try {
        const response = await fetch("api/v1/banana.php");
        if (!response.ok) {
            throw new Error("Banana API request failed");
        }

        const data = await response.json();
        if (!data.question) {
            throw new Error("Invalid API response");
        }

        image.onload = () => {
            image.style.display = "block";
        };
        image.onerror = () => {
            status.textContent = "Could not display the puzzle image.";
        };
        image.src = data.question;
        answerSection.hidden = false;
        status.textContent = "Solve this puzzle to play Flappy Bird!";
    } catch (error) {
        console.error(error);
        status.textContent = "Could not load puzzle. Click Load New Challenge to retry.";
    }
}

// Generated with GitHub Copilot
async function checkBananaAnswer() {
    const answerInput = document.getElementById("answerInput");
    const challengeMessage = document.getElementById("challengeMessage");

    if (answerInput.value.trim() === "") {
        challengeMessage.textContent = "Enter your answer first.";
        return;
    }

    challengeMessage.textContent = "Checking your answer...";
    try {
        const response = await fetch("api/v1/check_answer.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ answer: Number(answerInput.value) })
        });

        if (!response.ok) {
            throw new Error("Could not check your answer.");
        }

        const result = await response.json();
        if (result.correct === true) {
            challengeMessage.textContent = "Correct answer! 🎉";
            document.getElementById("answerSection").hidden = true;
            startGame();
            return;
        }

        challengeMessage.textContent = "Wrong answer! Load a new challenge to try again. ❌";
    } catch (error) {
        console.error(error);
        challengeMessage.textContent = "Could not check your answer. Please try again.";
    }

    answerInput.value = "";
    if (challengeMessage.textContent.startsWith("Wrong")) {
        answerInput.focus();
    }
}

async function saveScore(score) {
    const response = await fetch("api/v1/scores.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ score })
    });

    if (!response.ok) {
        throw new Error("Could not save your score.");
    }

    return response.json();
}

loadLeaderboard();

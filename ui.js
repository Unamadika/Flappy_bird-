// ui.js
// Connects the page to api.js: request -> display -> feedback.

const els = {
  newBtn:   document.getElementById("newPuzzleBtn"),
  image:    document.getElementById("puzzleImage"),
  status:   document.getElementById("statusMessage"),
  feedback: document.getElementById("feedback"),
  pad:      document.getElementById("answerPad"),
  request:  document.getElementById("requestInfo"),
  raw:      document.getElementById("rawResponse")
};

let currentPuzzle = null; // the puzzle currently on screen, or null

// ---------- small helpers ----------

function setStatus(text, kind = "info") {
  els.status.textContent = text;
  els.status.dataset.kind = kind;
  els.status.hidden = false;
}

function setPadEnabled(enabled) {
  els.pad.querySelectorAll("button").forEach(b => { b.disabled = !enabled; });
}

function resetPad() {
  els.pad.querySelectorAll("button").forEach(b => {
    b.classList.remove("correct", "wrong");
  });
  setPadEnabled(false);
}

function buildAnswerPad() {
  for (let d = 0; d <= 9; d++) {
    const b = document.createElement("button");
    b.type = "button";
    b.className = "digit";
    b.textContent = d;
    b.dataset.digit = d;
    b.disabled = true;
    els.pad.appendChild(b);
  }
}

// Developer panel: shows the real request and response for evidence/debugging
function renderDevInfo(result) {
  const code = result.status === null ? "no response" : `HTTP ${result.status}`;
  els.request.textContent = `GET ${result.requestUrl} → ${code} in ${result.ms} ms`;
  els.raw.textContent = result.raw || "(empty)";
}

// ---------- main flow ----------

async function loadPuzzle() {
  // Reset the screen and block double-clicks while the request is in flight
  currentPuzzle = null;
  els.newBtn.disabled = true;
  els.image.hidden = true;
  els.feedback.textContent = "";
  els.feedback.dataset.kind = "";
  resetPad();
  setStatus("Loading puzzle from the Banana API…");

  const result = await fetchBananaPuzzle();
  renderDevInfo(result);

  if (result.ok) {
    showPuzzle(result);
  } else {
    setStatus(result.error, "error");
  }

  els.newBtn.textContent = "New puzzle";
  els.newBtn.disabled = false;
}

function showPuzzle(result) {
  // Set handlers BEFORE src so we never miss the load/error event
  els.image.onload = () => {
    currentPuzzle = result;
    els.status.hidden = true;
    els.image.hidden = false;
    setPadEnabled(true);
  };
  els.image.onerror = () => {
    setStatus("The puzzle image could not be loaded. Press “New puzzle” to try again.", "error");
  };
  els.image.src = result.imageUrl;
}

function checkAnswer(digit) {
  if (!currentPuzzle) return;

  const correct = digit === currentPuzzle.answer;
  setPadEnabled(false);

  els.pad.querySelector(`[data-digit="${digit}"]`)
    .classList.add(correct ? "correct" : "wrong");

  if (correct) {
    els.feedback.textContent = "Correct!";
    els.feedback.dataset.kind = "good";
  } else {
    els.pad.querySelector(`[data-digit="${currentPuzzle.answer}"]`)
      .classList.add("correct");
    els.feedback.textContent = `Not quite. The answer was ${currentPuzzle.answer}.`;
    els.feedback.dataset.kind = "bad";
  }
}

// ---------- wiring ----------

buildAnswerPad();

els.newBtn.addEventListener("click", loadPuzzle);

// One listener on the container handles all ten digit buttons (event delegation)
els.pad.addEventListener("click", (e) => {
  const button = e.target.closest("button[data-digit]");
  if (button) checkAnswer(Number(button.dataset.digit));
});
<?php

// File purpose: check a puzzle answer without sending the solution to the browser.
require_once dirname(__DIR__, 3) . "/src/Auth/SessionManager.php";

use App\Auth\SessionManager;

header("Content-Type: application/json; charset=utf-8");
SessionManager::start();

// Keep API authentication failures in JSON format.
if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["error" => "Authentication required."]);
    exit;
}

// Answers are submitted as JSON using POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

// Reject requests that do not have this session's CSRF token.
$csrfToken = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? null;
if (!is_string($csrfToken) || !SessionManager::validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid CSRF token."]);
    exit;
}

// Read the JSON body and report malformed JSON clearly.
try {
    $body = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    http_response_code(400);
    echo json_encode(["error" => "Request body must be valid JSON."]);
    exit;
}

// Accept only a whole-number answer.
$answer = is_array($body) ? ($body["answer"] ?? null) : null;
if (!is_int($answer)) {
    http_response_code(400);
    echo json_encode(["error" => "Answer must be an integer."]);
    exit;
}

// A submitted answer needs a puzzle solution stored by banana.php.
if (!isset($_SESSION["banana_solution"])) {
    http_response_code(409);
    echo json_encode(["error" => "Load a challenge before submitting an answer."]);
    exit;
}

// Use the stored answer once, then discard it so it cannot be replayed.
$correct = $answer === (int) $_SESSION["banana_solution"];
unset($_SESSION["banana_solution"]);

// A correct answer grants one score submission for the game.
if ($correct) {
    $_SESSION["play_token"] = bin2hex(random_bytes(32));
}

// Only report whether the answer was correct.
echo json_encode(["correct" => $correct], JSON_THROW_ON_ERROR);

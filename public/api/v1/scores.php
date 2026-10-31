<?php

// File purpose: return the leaderboard and save verified player scores.
require_once dirname(__DIR__, 3) . "/src/Auth/SessionManager.php";
require_once dirname(__DIR__, 3) . "/src/Models/Score.php";
$pdo = require dirname(__DIR__, 3) . "/config/database.php";

use App\Auth\SessionManager;
use App\Models\Score;

header("Content-Type: application/json; charset=utf-8");
SessionManager::start();

// The leaderboard and score submissions are private to signed-in players.
if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["error" => "Authentication required."]);
    exit;
}

$scores = new Score($pdo);

// GET only reads and returns the top players.
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    echo json_encode(["scores" => $scores->leaderboard()], JSON_THROW_ON_ERROR);
    exit;
}

// Score changes must use POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: GET, POST");
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

// Check the browser's CSRF token before changing score data.
$csrfToken = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? null;
if (!is_string($csrfToken) || !SessionManager::validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid CSRF token."]);
    exit;
}

// A score can only be saved after a correct puzzle answer.
if (empty($_SESSION["play_token"])) {
    http_response_code(403);
    echo json_encode(["error" => "A valid play token is required to save a score."]);
    exit;
}

// Read the score from the JSON request.
try {
    $body = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    http_response_code(400);
    echo json_encode(["error" => "Request body must be valid JSON."]);
    exit;
}

// Reject non-integers and scores outside the allowed game range.
$score = is_array($body) ? ($body["score"] ?? null) : null;
if (!is_int($score) || $score < 0 || $score > 1000000) {
    http_response_code(422);
    echo json_encode(["error" => "Score must be an integer from 0 to 1000000."]);
    exit;
}

// Save the score and consume the one-use play token only after saving succeeds.
$scores->save($_SESSION["user_id"], $score);
unset($_SESSION["play_token"]);
echo json_encode(["saved" => true, "best_score" => $scores->bestForUser($_SESSION["user_id"])]);

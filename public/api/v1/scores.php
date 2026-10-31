<?php

require_once dirname(__DIR__, 3) . "/src/Auth/SessionManager.php";
require_once dirname(__DIR__, 3) . "/src/Models/Score.php";
$pdo = require dirname(__DIR__, 3) . "/config/database.php";

use App\Auth\SessionManager;
use App\Models\Score;

header("Content-Type: application/json; charset=utf-8");
SessionManager::start();

if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["error" => "Authentication required."]);
    exit;
}

$scores = new Score($pdo);

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    echo json_encode(["scores" => $scores->leaderboard()], JSON_THROW_ON_ERROR);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: GET, POST");
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

if (empty($_SESSION["play_token"])) {
    http_response_code(403);
    echo json_encode(["error" => "A valid play token is required to save a score."]);
    exit;
}

try {
    $body = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    http_response_code(400);
    echo json_encode(["error" => "Request body must be valid JSON."]);
    exit;
}

$score = is_array($body) ? ($body["score"] ?? null) : null;
if (!is_int($score) || $score < 0 || $score > 1000000) {
    http_response_code(422);
    echo json_encode(["error" => "Score must be an integer from 0 to 1000000."]);
    exit;
}

$scores->save($_SESSION["user_id"], $score);
unset($_SESSION["play_token"]);
echo json_encode(["saved" => true, "best_score" => $scores->bestForUser($_SESSION["user_id"])]);

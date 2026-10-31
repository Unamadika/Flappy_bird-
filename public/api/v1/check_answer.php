<?php

require_once dirname(__DIR__, 3) . "/src/Auth/SessionManager.php";

use App\Auth\SessionManager;

header("Content-Type: application/json; charset=utf-8");
SessionManager::start();

if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["error" => "Authentication required."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

$csrfToken = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? null;
if (!is_string($csrfToken) || !SessionManager::validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid CSRF token."]);
    exit;
}

try {
    $body = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    http_response_code(400);
    echo json_encode(["error" => "Request body must be valid JSON."]);
    exit;
}

$answer = is_array($body) ? ($body["answer"] ?? null) : null;
if (!is_int($answer)) {
    http_response_code(400);
    echo json_encode(["error" => "Answer must be an integer."]);
    exit;
}

if (!isset($_SESSION["banana_solution"])) {
    http_response_code(409);
    echo json_encode(["error" => "Load a challenge before submitting an answer."]);
    exit;
}

$correct = $answer === (int) $_SESSION["banana_solution"];
unset($_SESSION["banana_solution"]);

if ($correct) {
    $_SESSION["play_token"] = bin2hex(random_bytes(32));
}

echo json_encode(["correct" => $correct], JSON_THROW_ON_ERROR);

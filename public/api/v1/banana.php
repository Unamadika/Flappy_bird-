<?php

// File purpose: return a puzzle image while keeping its answer in the session.
require_once dirname(__DIR__, 3) . "/src/Auth/SessionManager.php";
require_once dirname(__DIR__, 3) . "/src/Services/BananaApiService.php";

use App\Auth\SessionManager;
use App\Services\BananaApiService;

header("Content-Type: application/json; charset=utf-8");
SessionManager::start();

// APIs return JSON instead of redirecting when a player is not logged in.
if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["error" => "Authentication required."]);
    exit;
}

// This endpoint only loads puzzles, so reject other request methods.
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Allow: GET");
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

try {
    // Keep the solution in the server session; only send the image URL to JavaScript.
    $challenge = (new BananaApiService())->getChallenge();
    $_SESSION["banana_solution"] = $challenge["solution"];

    echo json_encode(["question" => $challenge["question"]], JSON_THROW_ON_ERROR);
} catch (RuntimeException $exception) {
    // Log details for the developer without exposing them to the browser.
    error_log($exception->getMessage());
    http_response_code(502);
    echo json_encode(["error" => "Could not load a Banana challenge."]);
}

<?php

require_once dirname(__DIR__) . "/src/Auth/SessionManager.php";
require_once dirname(__DIR__) . "/src/Models/User.php";
$pdo = require dirname(__DIR__) . "/config/database.php";

use App\Auth\SessionManager;
use App\Models\User;

SessionManager::start();
$csrfToken = SessionManager::csrfToken();
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedToken = $_POST["csrf_token"] ?? null;
    if (!is_string($submittedToken) || !SessionManager::validateCsrfToken($submittedToken)) {
        http_response_code(403);
        $message = "Security token expired. Please submit the form again.";
    } else {
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";
        $user = (new User($pdo))->findByEmail($email);

        if ($user && password_verify($password, $user["password"])) {
            SessionManager::login((string) $user["id"], $user["username"]);
            header("Location: index.php");
            exit;
        }

        $message = "Incorrect email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Flappy Bird</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="form-page">
    <main class="form-box">
        <h1>🐦 Player Login</h1>
        <p class="message"><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8") ?>">
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>New player? <a href="register.php">Create an account</a></p>
    </main>
</body>
</html>

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
        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($username === "" || $email === "" || $password === "") {
            $message = "Please fill in every field.";
        } elseif (strlen($username) > 50 || strlen($email) > 100) {
            $message = "Username or email is too long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
        } elseif (strlen($password) < 6) {
            $message = "Password must be at least 6 characters.";
        } else {
            $users = new User($pdo);

            if ($users->isRegistered($username, $email)) {
                $message = "Username or email is already registered.";
            } else {
                try {
                    $userId = $users->create($username, $email, $password);
                    SessionManager::login($userId, $username);
                    header("Location: index.php");
                    exit;
                } catch (PDOException $exception) {
                    if ($exception->getCode() !== "23000") {
                        throw $exception;
                    }

                    $message = "Username or email is already registered.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Flappy Bird</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="form-page">
    <main class="form-box">
        <h1>🐦 Create Account</h1>
        <p>Join Flappy Bird!</p>
        <p class="message"><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8") ?>">
            <input type="text" name="username" placeholder="Choose username" maxlength="50" required>
            <input type="email" name="email" placeholder="Email address" maxlength="100" required>
            <input type="password" name="password" placeholder="Password (minimum 6 characters)" required>
            <button type="submit">Register</button>
        </form>
        <p>Already registered? <a href="login.php">Login here</a></p>
    </main>
</body>
</html>

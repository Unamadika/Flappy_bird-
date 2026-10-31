
<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $player = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $pass = $_POST["password"] ?? "";

    if ($player === "" || $email === "" || $pass === "") {
        $message = "Please fill in every field.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($pass) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        try {
            $check = $pdo->prepare(
                "SELECT id FROM users WHERE username = ? OR email = ?"
            );
            $check->execute([$player, $email]);

            if ($check->fetch()) {
                $message = "Username or email is already registered.";
            } else {
                $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

                $insert = $pdo->prepare(
                    "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
                );
                $insert->execute([$player, $email, $hashedPassword]);

                session_regenerate_id(true);
                $_SESSION["user_id"] = $pdo->lastInsertId();
                $_SESSION["username"] = $player;

                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $message = "Registration failed. Please try again.";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #70c5ce;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 320px;
            text-align: center;
        }
        input, button {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin: 9px 0;
        }
        button {
            background: #f4a623;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .message { color: #c0392b; }
    </style>
</head>
<body>
    <div class="form-box">
        <h1>🐦 Create Account</h1>
        <p>Join Flappy Bird!</p>

        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>

        <form method="POST">
            <input type="text" name="username"
                   placeholder="Choose username" maxlength="50" required>

            <input type="email" name="email"
                   placeholder="Email address" maxlength="100" required>

            <input type="password" name="password"
                   placeholder="Password (minimum 6 characters)" required>

            <button type="submit">Register</button>
        </form>

        <p>Already registered? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>

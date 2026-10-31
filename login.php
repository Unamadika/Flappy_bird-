
<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT id, username, password FROM users WHERE email = ?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($pass, $user["password"])) {
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];

        header("Location: index.php");
        exit;
    } else {
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
        <h1>🐦 Player Login</h1>
        <p class="message"><?= htmlspecialchars($message) ?></p>

        <form method="POST">
            <input type="email" name="email"
                   placeholder="Email address" required>

            <input type="password" name="password"
                   placeholder="Password" required>

            <button type="submit">Login</button>
        </form>

        <p>New player? <a href="register.php">Create an account</a></p>
    </div>
</body>
</html>


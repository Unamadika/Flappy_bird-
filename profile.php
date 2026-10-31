
<?php
require_once "auth.php";
requireLogin();
require_once "db.php";

$stmt = $pdo->prepare(
    "SELECT username, email, created_at FROM users WHERE id = ?"
);

$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Player Profile</title>

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

.profile-card {
    background: white;
    padding: 35px;
    width: 340px;
    max-width: 90%;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.avatar {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    background: #70c5ce;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    margin: 0 auto 15px;
}

.details {
    text-align: left;
    background: #f5f5f5;
    padding: 15px;
    border-radius: 10px;
    overflow-wrap: anywhere;
}

.details p {
    margin: 12px 0;
}

a {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 20px;
    background: #f4a623;
    color: #222;
    text-decoration: none;
    border-radius: 7px;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="profile-card">

    <div class="avatar">👤</div>

    <h2>Player Profile</h2>

    <div class="details">
        <p>
            <strong>Username:</strong><br>
            <?= htmlspecialchars($user["username"], ENT_QUOTES, "UTF-8") ?>
        </p>

        <p>
            <strong>Email:</strong><br>
            <?= htmlspecialchars($user["email"], ENT_QUOTES, "UTF-8") ?>
        </p>

        <p>
            <strong>Account created:</strong><br>
            <?= htmlspecialchars($user["created_at"], ENT_QUOTES, "UTF-8") ?>
        </p>
    </div>

    <a href="index.php">← Back to Game</a>

</div>

</body>
</html>

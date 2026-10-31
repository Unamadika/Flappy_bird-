<?php

$host = getenv("DB_HOST") ?: "YOUR_DATABASE_HOST";
$database = getenv("DB_NAME") ?: "YOUR_DATABASE_NAME";
$username = getenv("DB_USER") ?: "YOUR_DATABASE_USERNAME";
$password = getenv("DB_PASSWORD") ?: "YOUR_DATABASE_PASSWORD";

try {
    return new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    error_log("Database connection failed: " . $exception->getMessage());
    throw new RuntimeException(
        "Database connection failed. Check the XAMPP service and database configuration.",
        0,
        $exception
    );
}

<?php

namespace App\Models;

use PDO;

final class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, username, email, password, created_at FROM users WHERE email = ?"
        );
        $statement->execute([$email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findById(int|string $id): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, username, email, created_at FROM users WHERE id = ?"
        );
        $statement->execute([$id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function isRegistered(string $username, string $email): bool
    {
        $statement = $this->pdo->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1"
        );
        $statement->execute([$username, $email]);

        return (bool) $statement->fetch();
    }

    public function create(string $username, string $email, string $password): string
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
        );
        $statement->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
        ]);

        return $this->pdo->lastInsertId();
    }
}

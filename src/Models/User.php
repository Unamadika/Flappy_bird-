<?php

// File purpose: read user records and safely create new accounts.
namespace App\Models;

use PDO;

class User
{
    private $pdo;

    // Keep the database connection so each method can run a prepared query.
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Find a login account by email, including its password hash.
    public function findByEmail(string $email)
    {
        $statement = $this->pdo->prepare(
            "SELECT id, username, email, password, created_at FROM users WHERE email = ?"
        );
        $statement->execute([$email]);
        $user = $statement->fetch();

        // PDO returns false when no account matches.
        return $user ?: null;
    }

    // Find profile details by user ID without returning the password hash.
    public function findById($id)
    {
        $statement = $this->pdo->prepare(
            "SELECT id, username, email, created_at FROM users WHERE id = ?"
        );
        $statement->execute([$id]);
        $user = $statement->fetch();

        // Return null when the user ID does not exist.
        return $user ?: null;
    }

    // Check whether either the username or email is already in use.
    public function isRegistered(string $username, string $email): bool
    {
        $statement = $this->pdo->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1"
        );
        $statement->execute([$username, $email]);

        if ($statement->fetch()) {
            return true;
        }

        return false;
    }

    // Hash the password before saving a new account.
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

        // Return the database ID so the new account can be logged in.
        return $this->pdo->lastInsertId();
    }
}

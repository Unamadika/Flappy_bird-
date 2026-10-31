<?php

// File purpose: save scores and look up player best scores and leaderboard.
namespace App\Models;

use PDO;

class Score
{
    private $pdo;

    // Store the database connection for score queries.
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Save a score for its player using a prepared statement.
    public function save($userId, int $score): void
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO scores (user_id, score) VALUES (?, ?)"
        );
        $statement->execute([$userId, $score]);
    }

    // Return the player's highest score, or zero when there are no scores.
    public function bestForUser($userId): int
    {
        $statement = $this->pdo->prepare(
            "SELECT MAX(score) FROM scores WHERE user_id = ?"
        );
        $statement->execute([$userId]);

        return (int) $statement->fetchColumn();
    }

    // Return each player's best score, from highest to lowest.
    public function leaderboard(int $limit = 10): array
    {
        // Keep the requested result count within a safe range.
        $limit = max(1, min($limit, 100));
        $statement = $this->pdo->query(
            "SELECT users.username, MAX(scores.score) AS best_score
             FROM scores
             INNER JOIN users ON users.id = scores.user_id
             GROUP BY users.id, users.username
             ORDER BY best_score DESC, users.username ASC
             LIMIT {$limit}"
        );

        return $statement->fetchAll();
    }
}

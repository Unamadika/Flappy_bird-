<?php

namespace App\Models;

use PDO;

final class Score
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(int|string $userId, int $score): void
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO scores (user_id, score) VALUES (?, ?)"
        );
        $statement->execute([$userId, $score]);
    }

    public function bestForUser(int|string $userId): int
    {
        $statement = $this->pdo->prepare(
            "SELECT MAX(score) FROM scores WHERE user_id = ?"
        );
        $statement->execute([$userId]);

        return (int) $statement->fetchColumn();
    }

    public function leaderboard(int $limit = 10): array
    {
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

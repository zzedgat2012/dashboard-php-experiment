<?php

namespace App\Models;

/**
 * Manages password reset tokens, allowing users to safely change credentials.
 */
use App\Database;
use DateTimeImmutable;
use PDO;

class PasswordResetRepository
{
    private PDO $pdo;
    private static bool $tableReady = false;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->initializeTable();
    }

    public function createForUser(int $userId, string $token, DateTimeImmutable $expiresAt): void
    {
        $this->deleteByUser($userId);

        $stmt = $this->pdo->prepare('
            INSERT INTO password_resets (user_id, token, expires_at)
            VALUES (:user_id, :token, :expires_at)
        ');

        $stmt->execute([
            ':user_id'    => $userId,
            ':token'      => $token,
            ':expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function findValidByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT pr.*, u.email
            FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token = :token
              AND pr.used_at IS NULL
              AND pr.expires_at >= datetime("now")
            LIMIT 1
        ');

        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function markAsUsed(int $id): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE password_resets
            SET used_at = datetime("now")
            WHERE id = :id
        ');

        $stmt->execute([':id' => $id]);
    }

    private function deleteByUser(int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
    }

    private function initializeTable(): void
    {
        if (self::$tableReady) {
            return;
        }

        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS password_resets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token TEXT NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                used_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ');

        self::$tableReady = true;
    }
}

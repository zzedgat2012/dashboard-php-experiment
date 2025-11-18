<?php

namespace App\Models;

/**
 * Persists and retrieves magic-link records for passwordless authentication.
 */
use App\Database;
use PDO;
use DateTimeImmutable;

class MagicLinkRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * create magic link for a user.
     */
    public function createMagicLink(int $userId, string $token, string $code, DateTimeImmutable $expiresAt): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO magic_links (user_id, token, code, expires_at)
            VALUES (:user_id, :token, :code, :expires_at)
        ');

        $stmt->execute([
            ':user_id'   => $userId,
            ':token'     => $token,
            ':code'      => $code,
            ':expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function createForUser(int $userId, string $token, string $code, DateTimeImmutable $expiresAt): void
    {
        $this->createMagicLink($userId, $token, $code, $expiresAt);
    }

    /**
     * Find a valid (not expired, not used) magick link by token
     */
    public function findValidByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT ml.*, u.email, u.first_name, u.second_name
            FROM magic_links ml
            JOIN users u ON u.id = ml.user_id
            WHERE ml.token = :token
              AND ml.used_at IS NULL
              AND ml.expires_at >= datetime('now')
            LIMIT 1
        ");

        $stmt->execute([':token' => $token]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * Mark a magic link as used
     */
    public function markAsUsed(int $id): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE magic_links
            SET used_at = datetime('now')
            WHERE id = :id
        ");

        $stmt->execute([':id' => $id]);
    }
}

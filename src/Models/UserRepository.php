<?php

namespace App\Models;

/**
 * Encapsulates CRUD access to the users table for account management.
 */
use App\Database;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(string $firstName, string $secondName, string $email, string $passwordHash): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (first_name, second_name, email, password_hash)
            VALUES (:first, :second, :email, :pass)
        ');

        $stmt->execute([
            ':first' => $firstName,
            ':second' => $secondName,
            ':email' => $email,
            ':pass' => $passwordHash,
        ]);
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE users
            SET password_hash = :password_hash
            WHERE id = :id
        ');

        $stmt->execute([
            ':password_hash' => $passwordHash,
            ':id'            => $userId,
        ]);
    }
}

<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database;

try {
    $pdo = Database::getConnection();

    $query = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT NOT NULL,
            second_name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ";

    $pdo->exec($query);

    echo "✔ users table created successfully!" . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed ❌: " . $e->getMessage());
}

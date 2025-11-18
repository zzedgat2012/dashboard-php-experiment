<?php

declare(strict_types=1);

/**
 * CLI migration script that provisions password reset tokens storage.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;

$pdo = Database::getConnection();

$sql = '
    CREATE TABLE IF NOT EXISTS password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
';

$pdo->exec($sql);

echo '✔ password_resets table created successfully!' . PHP_EOL;

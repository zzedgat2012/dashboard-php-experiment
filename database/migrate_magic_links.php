<?php

declare(strict_types=1);

/**
 * CLI migration script that provisions the magic_links table supporting
 * passwordless authentication tokens for users.
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Database;

$pdo = Database::getConnection();

$sql = '
    CREATE TABLE IF NOT EXISTS magic_links (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL UNIQUE,
        code TEXT NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
';

$pdo->exec($sql);

echo '✔ magic_links table created successfully!' . PHP_EOL;

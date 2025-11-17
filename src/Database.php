<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    public static function getConnection(): PDO
    {
        try {
            $pdo = new PDO('sqlite:' . __DIR__ . '/../database/app.db');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die('DB error: ' . $e->getMessage());
        }
    }
}

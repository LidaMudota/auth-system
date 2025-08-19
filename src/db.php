<?php
function db() {
    static $pdo;
    if ($pdo) {
        return $pdo;
    }
    $config = require __DIR__ . '/../config/config.php';
    $db = $config['db'];
    $pdo = new PDO($db['dsn'], $db['user'], $db['password'], $db['options'] ?? []);
    return $pdo;
}
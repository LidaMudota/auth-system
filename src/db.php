<?php
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $c = require __DIR__ . '/../config/config.php';
        $dsn = 'mysql:host=' . $c['db']['host'] . ';dbname=' . $c['db']['dbname'] . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $c['db']['user'], $c['db']['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }
    return $pdo;
}
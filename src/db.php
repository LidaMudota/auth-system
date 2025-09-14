<?php
$config = require __DIR__ . '/../config/config.php';

function db(): PDO
{
    static $pdo;
    global $config;
    if ($pdo === null) {
        $pdo = new PDO(
            $config['db']['dsn'],
            $config['db']['user'],
            $config['db']['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}
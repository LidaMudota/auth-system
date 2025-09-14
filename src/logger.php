<?php
$config = require __DIR__ . '/../config/config.php';

function log_failed_login(string $login, string $ip): void
{
    global $config;
    $line = '[' . date('Y-m-d H:i:s') . "] FAILED LOGIN: login=\"$login\", IP=$ip" . PHP_EOL;
    file_put_contents($config['logs']['auth'], $line, FILE_APPEND | LOCK_EX);
}
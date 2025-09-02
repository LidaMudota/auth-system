<?php
function log_failed_login(string $login, string $reason): void {
    $config = require __DIR__ . '/../config/config.php';
    $file = $config['log']['auth_log_path'];
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $line = sprintf(
        "[%s] FAIL login=\"%s\" ip=\"%s\" reason=\"%s\"\n",
        date('Y-m-d H:i:s'),
        $login,
        $ip,
        $reason
    );
    file_put_contents($file, $line, FILE_APPEND);
}
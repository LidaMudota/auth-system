<?php
// /auth-system/src/logger.php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function log_failed_login(string $login, string $reason): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $line = sprintf(
        "[%s] FAIL login=\"%s\" ip=%s reason=%s ua=\"%s\"\n",
        date('c'),
        $login,
        $ip,
        $reason,
        $ua
    );
    if (!is_dir(dirname(AUTH_LOG_FILE))) {
        @mkdir(dirname(AUTH_LOG_FILE), 0775, true);
    }
    file_put_contents(AUTH_LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

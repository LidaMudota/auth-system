<?php
function logAuthFail(string $login): void {
    $cfg = require __DIR__ . '/../config/config.php';
    $line = '[' . date('Y-m-d H:i:s') . "] Failed login attempt: $login\n";
    file_put_contents($cfg['log_file'], $line, FILE_APPEND);
}
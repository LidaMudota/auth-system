<?php
function log_fail($login) {
    $config = require __DIR__ . '/../config/config.php';
    $file = $config['log_file'];
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $line = sprintf(
        "%s | %s | login=%s | result=fail\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'CLI',
        $login
    );
    file_put_contents($file, $line, FILE_APPEND);
}
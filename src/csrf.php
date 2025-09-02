<?php
require_once __DIR__ . '/auth.php';

function csrf_generate(): string {
    start_session_once();
    $config = require __DIR__ . '/../config/config.php';
    $len = $config['security']['csrf_token_len'];
    $token = bin2hex(random_bytes($len));
    $_SESSION[$config['security']['csrf_key']] = $token;
    return $token;
}

function csrf_get(): string {
    start_session_once();
    $config = require __DIR__ . '/../config/config.php';
    $key = $config['security']['csrf_key'];
    if (empty($_SESSION[$key])) {
        return csrf_generate();
    }
    return $_SESSION[$key];
}

function csrf_check(string $token): bool {
    start_session_once();
    $config = require __DIR__ . '/../config/config.php';
    $key = $config['security']['csrf_key'];
    return isset($_SESSION[$key]) && hash_equals($_SESSION[$key], $token);
}
<?php
// /auth-system/src/csrf.php
declare(strict_types=1);

function csrf_start_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function csrf_token(): string {
    csrf_start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="'.$t.'">';
}

function csrf_validate_or_die(?string $token): void {
    csrf_start_session();
    if (!isset($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(400);
        exit('Bad request (invalid CSRF token).');
    }
    // Одноразовый токен на сабмит
    unset($_SESSION['csrf_token']);
}

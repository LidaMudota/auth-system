<?php
require_once __DIR__ . '/db.php';

function start_session_once(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function current_user(): ?array {
    start_session_once();
    return $_SESSION['user'] ?? null;
}

function login_user(int $userId, string $role): void {
    start_session_once();
    $_SESSION['user'] = ['id' => $userId, 'role' => $role];
    session_regenerate_id(true);
}

function is_role(string $role): bool {
    $user = current_user();
    return $user !== null && $user['role'] === $role;
}

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function issue_remember_token(int $userId): void {
    $config = require __DIR__ . '/../config/config.php';
    $token = base64url_encode(random_bytes($config['security']['remember_token_len']));
    $expires = (new DateTime('+7 days'))->format('Y-m-d H:i:s');
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE users SET remember_token = :t, remember_token_expires_at = :e WHERE id = :id');
    $stmt->execute([':t' => $token, ':e' => $expires, ':id' => $userId]);
    setcookie(
        $config['security']['remember_cookie_name'],
        $token,
        [
            'expires' => time() + $config['security']['remember_cookie_lifetime'],
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

function require_auth(): void {
    start_session_once();
    if (current_user()) {
        return;
    }
    $config = require __DIR__ . '/../config/config.php';
    $cookieName = $config['security']['remember_cookie_name'];
    if (!empty($_COOKIE[$cookieName])) {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, role, remember_token_expires_at FROM users WHERE remember_token = :t');
        $stmt->execute([':t' => $_COOKIE[$cookieName]]);
        $user = $stmt->fetch();
        if ($user && strtotime($user['remember_token_expires_at']) > time()) {
            login_user((int)$user['id'], $user['role']);
            return;
        }
    }
    header('Location: login.php');
    exit;
}

function logout_user(): void {
    start_session_once();
    $config = require __DIR__ . '/../config/config.php';
    $user = current_user();
    if ($user) {
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE users SET remember_token = NULL, remember_token_expires_at = NULL WHERE id = :id');
        $stmt->execute([':id' => $user['id']]);
    }
    $cookieName = $config['security']['remember_cookie_name'];
    if (isset($_COOKIE[$cookieName])) {
        setcookie($cookieName, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    $_SESSION = [];
    session_destroy();
}
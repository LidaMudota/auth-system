<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/db.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function login_user(int $id, string $role): void
{
    $_SESSION['user'] = ['id' => $id, 'role' => $role];
}

function logout_user(): void
{
    session_unset();
    session_destroy();
}

function user_role(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

function require_auth(): void
{
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function attempt_cookie_login(): void
{
    if (current_user() || empty($_COOKIE['remember'])) {
        return;
    }
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, role FROM users WHERE remember_token = ?');
    $stmt->execute([$_COOKIE['remember']]);
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        login_user($user['id'], $user['role']);
    }
}
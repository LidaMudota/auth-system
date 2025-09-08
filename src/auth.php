<?php
session_start();
require_once __DIR__ . '/db.php';

(function () {
    if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember'])) {
        $s = db()->prepare('SELECT id, role FROM users WHERE remember_token = ?');
        $s->execute([$_COOKIE['remember']]);
        if ($u = $s->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['role'] = $u['role'];
        }
    }
})();

function isAuth(): bool {
    return !empty($_SESSION['user_id']);
}

function userRole(): ?string {
    return $_SESSION['role'] ?? null;
}

function requireAuth(): void {
    if (!isAuth()) {
        header('Location: login.php');
        exit;
    }
}
<?php
function start_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function login_user($user_id, $role) {
    start_session();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = $role;
    $_SESSION['auth'] = true;
}

function logout_user() {
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function require_auth() {
    start_session();
    if (empty($_SESSION['auth'])) {
        header('Location: login.php');
        exit;
    }
}

function current_role() {
    start_session();
    return $_SESSION['role'] ?? null;
}
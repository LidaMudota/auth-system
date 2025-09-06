<?php
// /auth-system/src/auth.php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';

function auth_start_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * Безопасная установка куки с поддержкой SameSite.
 */
function set_cookie(string $name, string $value, int $lifetime): void {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $params = [
        'expires'  => time() + $lifetime,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => REMEMBER_SAMESITE,
    ];
    setcookie($name, $value, $params);
}

/**
 * Удаление куки «наверняка».
 */
function drop_cookie(string $name): void {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie($name, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => REMEMBER_SAMESITE,
    ]);
}

function current_user(): ?array {
    auth_start_session();
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    // Попытка «запомнить меня»
    if (!empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
        $raw = $_COOKIE[REMEMBER_COOKIE_NAME];
        // Формат: userId:token
        if (strpos($raw, ':') !== false) {
            [$userId, $token] = explode(':', $raw, 2);
            if (ctype_digit($userId) && is_string($token) && strlen($token) >= 40) {
                $pdo = db();
                $stmt = $pdo->prepare('SELECT id, login, role, remember_token_hash, remember_token_expiry FROM users WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => (int)$userId]);
                $u = $stmt->fetch();
                if ($u && $u['remember_token_hash'] && $u['remember_token_expiry'] && new DateTime() < new DateTime($u['remember_token_expiry'])) {
                    $calc = hash('sha256', $token);
                    if (hash_equals($u['remember_token_hash'], $calc)) {
                        $_SESSION['user'] = [
                            'id'    => (int)$u['id'],
                            'login' => $u['login'],
                            'role'  => $u['role'],
                        ];
                        return $_SESSION['user'];
                    }
                }
            }
        }
        // Если что-то не так — гасим куку
        drop_cookie(REMEMBER_COOKIE_NAME);
    }
    return null;
}

function login_user(int $userId): void {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, login, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $u = $stmt->fetch();
    auth_start_session();
    $_SESSION['user'] = [
        'id'    => (int)$u['id'],
        'login' => $u['login'],
        'role'  => $u['role'],
    ];
}

function set_remember_me(int $userId): void {
    // Генерируем независимый токен
    $token = bin2hex(random_bytes(32)); // 64 hex chars
    $hash  = hash('sha256', $token);
    $expiry = (new DateTimeImmutable())->modify('+' . REMEMBER_COOKIE_LIFETIME . ' seconds')->format('Y-m-d H:i:s');

    $pdo = db();
    $pdo->prepare('UPDATE users SET remember_token_hash = :h, remember_token_expiry = :e WHERE id = :id')
        ->execute([':h' => $hash, ':e' => $expiry, ':id' => $userId]);

    // Сохраняем в куку «сырой» токен (как положено), вместе с id
    set_cookie(REMEMBER_COOKIE_NAME, $userId . ':' . $token, REMEMBER_COOKIE_LIFETIME);
}

function clear_remember_me(int $userId): void {
    $pdo = db();
    $pdo->prepare('UPDATE users SET remember_token_hash = NULL, remember_token_expiry = NULL WHERE id = :id')
        ->execute([':id' => $userId]);
    drop_cookie(REMEMBER_COOKIE_NAME);
}

function logout_user(): void {
    $u = current_user();
    if ($u) {
        clear_remember_me((int)$u['id']);
    }
    auth_start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

function require_auth(): array {
    $u = current_user();
    if (!$u) {
        header('Location: /auth-system/public/login.php');
        exit;
    }
    return $u;
}

function is_vk_role(?array $u): bool {
    return isset($u['role']) && $u['role'] === 'vk';
}

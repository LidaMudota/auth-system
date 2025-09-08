<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

$c = require __DIR__ . '/../config/config.php';

// 1) Проверим code и state (если сохраняли)
$code  = $_GET['code']  ?? '';
$state = $_GET['state'] ?? '';
if ($code === '') {
    http_response_code(400);
    exit('Missing code');
}
if (!empty($_SESSION['vk_oauth_state']) && $state !== $_SESSION['vk_oauth_state']) {
    http_response_code(400);
    exit('Invalid state');
}

// 2) Обмен кода на токены через VK ID (POST)
$code_verifier = $_SESSION['vk_pkce_verifier'] ?? null;
if (!$code_verifier) {
    http_response_code(400);
    exit('Missing PKCE verifier');
}

$post = [
    'grant_type'    => 'authorization_code',
    'code'          => $code,
    'redirect_uri'  => $c['vk']['redirect_uri'],
    'client_id'     => $c['vk']['client_id'],
    'client_secret' => $c['vk']['client_secret'], // для Web-приложения секрет обязателен
    'code_verifier' => $code_verifier,
];

$ch = curl_init($c['vk']['token_url']);
curl_setopt_array($ch, [
    CURLOPT_POST            => true,
    CURLOPT_POSTFIELDS      => http_build_query($post),
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_TIMEOUT         => 15,
]);
$raw = curl_exec($ch);
if ($raw === false) {
    http_response_code(502);
    exit('Token request failed');
}
$resp = json_decode($raw, true) ?: [];

if (isset($resp['error'])) {
    http_response_code(401);
    $desc = $resp['error_description'] ?? $resp['error'];
    exit('VK error: ' . htmlspecialchars($desc));
}

// Ответ VK ID может содержать id_token (JWT) и/или access_token.
// E-mail чаще приходит отдельным полем или внутри id_token в claim'ах (если выдан).
$email     = $resp['email']     ?? null;
$user_id   = $resp['user_id']   ?? null;   // поле может отсутствовать — не критично
$id_token  = $resp['id_token']  ?? null;
$acc_token = $resp['access_token'] ?? null;

// Фолбэк на логин: email либо стабильный vk_<id>, иначе — отказ
$login = $email ?: ($user_id ? ('vk_' . $user_id) : null);
if (!$login) {
    // попробуем достать sub (uid) из id_token (если есть)
    if ($id_token && preg_match('~^[A-Za-z0-9\-\_=]+\.[A-Za-z0-9\-\_=]+~', $id_token)) {
        [$h,$p] = explode('.', $id_token, 3);
        $claims = json_decode(base64_decode(strtr($p,'-_','+/')), true);
        if (!empty($claims['sub'])) $login = 'vk_' . $claims['sub'];
    }
}
if (!$login) {
    http_response_code(500);
    exit('Cannot resolve user login (no email / user_id)');
}

// 3) Создаём/находим пользователя роли vk
$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT id, role FROM users WHERE login = :login LIMIT 1');
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $stmt = $pdo->prepare('INSERT INTO users (login, password_hash, role) VALUES (:login, NULL, "vk")');
        $stmt->execute([':login' => $login]);
        $userId = (int)$pdo->lastInsertId();
        $role   = 'vk';
    } else {
        $userId = (int)$user['id'];
        $role   = $user['role'];
        if ($role !== 'vk') {
            $pdo->prepare('UPDATE users SET role = "vk" WHERE id = :id')->execute([':id' => $userId]);
            $role = 'vk';
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    exit('DB error');
}

// 4) Авторизация в сессии и редирект
$_SESSION['user_id'] = $userId;
$_SESSION['role']    = $role;

header('Location: /protected.php', true, 302);
exit;

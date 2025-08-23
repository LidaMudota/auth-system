<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

$config = require __DIR__ . '/../config/config.php';

$code  = $_GET['code']  ?? '';
$state = $_GET['state'] ?? '';
if (!$code || !$state || $state !== ($_SESSION['vk_state'] ?? '')) {
    exit('Ошибка');
}

$verifier = $_SESSION['vk_code_verifier'] ?? null;
$nonce    = $_SESSION['vk_nonce'] ?? null;
unset($_SESSION['vk_state'], $_SESSION['vk_code_verifier'], $_SESSION['vk_nonce']);

if (!$verifier || !$nonce) exit('Ошибка');

$body = [
    'grant_type'    => 'authorization_code',
    'code'          => $code,
    'redirect_uri'  => $config['vk']['redirect_uri'],
    'client_id'     => $config['vk']['client_id'],
    'code_verifier' => $verifier, // ВАЖНО: без client_secret
];

$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded",
        'content' => http_build_query($body),
        'timeout' => 10,
    ]
];

$resp = file_get_contents('https://id.vk.com/oauth2/token', false, stream_context_create($opts));
if ($resp === false) exit('Ошибка токена');
$data = json_decode($resp, true);

$idToken = $data['id_token'] ?? null;
if (!$idToken) exit('Нет id_token');

[$h, $p, $s] = explode('.', $idToken);
$payload = json_decode(base64_decode(strtr($p, '-_', '+/')), true);
if (!is_array($payload)) exit('Повреждённый id_token');

if (($payload['nonce'] ?? '') !== $nonce) exit('Неверный nonce');

$vkId = $payload['sub'] ?? null;
if (!$vkId) exit('Нет sub в id_token');

$stmt = db()->prepare('SELECT id FROM users WHERE vk_user_id = ?');
$stmt->execute([$vkId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $stmt = db()->prepare("INSERT INTO users (login, password_hash, role, vk_user_id) VALUES (NULL, NULL, 'vk', ?)");
    $stmt->execute([$vkId]);
    $userId = db()->lastInsertId();
} else {
    $userId = $user['id'];
}

login_user($userId, 'vk');
header('Location: protected.php');
exit;
<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

$cfg = require __DIR__ . '/../config/config.php';
$vk  = $cfg['vk'];

$code      = $_GET['code']      ?? '';
$state     = $_GET['state']     ?? '';
$device_id = $_GET['device_id'] ?? '';   // <— ВАЖНО: приходит от VK ID

if ($code === '')  { http_response_code(400); exit('Нет кода авторизации'); }
if ($state === '' || $state !== ($_SESSION['vk_oauth_state'] ?? '')) {
  http_response_code(400); exit('Неверный state');
}
$code_verifier = $_SESSION['vk_pkce_verifier'] ?? '';
if ($code_verifier === '') { http_response_code(400); exit('Нет PKCE verifier'); }

// Обмен код→токен (VK ID требует POST + device_id)
$post = [
  'grant_type'    => 'authorization_code',
  'client_id'     => $vk['client_id'],
  'client_secret' => $vk['client_secret'],
  'redirect_uri'  => $vk['redirect_uri'],
  'code'          => $code,
  'code_verifier' => $code_verifier,
];

// device_id обязателен в новом VK ID потоке — передаём, если есть
if ($device_id !== '') {
  $post['device_id'] = $device_id;
}

$ch = curl_init($vk['token_url']);
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => http_build_query($post),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT        => 20,
]);
$raw = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($raw === false) { http_response_code(502); exit('Сбой запроса токена: ' . $err); }
$data = json_decode($raw, true);
if (isset($data['error'])) {
  http_response_code(400);
  exit('VK error: ' . $data['error'] . ' ' . ($data['error_description'] ?? ''));
}

$vk_id = $data['user_id'] ?? null;
if (!$vk_id && !empty($data['id_token'])) {
  // fallback: достанем sub из id_token (JWT) — если user_id не пришёл
  $parts = explode('.', $data['id_token']);
  if (count($parts) === 3) {
    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    $vk_id = $payload['sub'] ?? null;
  }
}
if (!$vk_id) { http_response_code(400); exit('VK не вернул идентификатор'); }

// login / create
$pdo = db();
$stmt = $pdo->prepare('SELECT id, role FROM users WHERE vk_id = ? LIMIT 1');
$stmt->execute([$vk_id]);
if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
  login_user((int)$user['id'], $user['role']);
} else {
  $pdo->prepare('INSERT INTO users (vk_id, role) VALUES (?, "vk")')->execute([$vk_id]);
  login_user((int)$pdo->lastInsertId(), 'vk');
}

header('Location: /protected.php');
exit;

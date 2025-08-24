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

$nonce = $_SESSION['vk_nonce'] ?? null;
unset($_SESSION['vk_state'], $_SESSION['vk_nonce']);

if (!$nonce) exit('Ошибка');

$tokenUrl = 'https://id.vk.com/oauth2/token';

$body = http_build_query([
  'grant_type'   => 'authorization_code',
  'code'         => $code,
  'redirect_uri' => $config['vk']['redirect_uri'],
  'client_id'    => $config['vk']['client_id'],
  'client_secret'=> $config['vk']['client_secret'],
]);

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => $body,
  CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded','Accept: application/json'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT        => 15,
]);
$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200) {
    header('text/plain');
    die("Ошибка токена HTTP $http:\n$resp");
}

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
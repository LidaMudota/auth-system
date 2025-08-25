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

$nonce    = $_SESSION['vk_nonce']    ?? null;
$verifier = $_SESSION['vk_verifier'] ?? null;
unset($_SESSION['vk_state'], $_SESSION['vk_nonce'], $_SESSION['vk_verifier']);

if (!$nonce || !$verifier) exit('Ошибка');

$tokenUrl = 'https://id.vk.com/oauth2/token'; // <-- ВАЖНО: /token

$body = http_build_query([
    'grant_type'    => 'authorization_code',
    'code'          => $code,
    'redirect_uri'  => $config['vk']['redirect_uri'], // должен в точности совпадать с authorize
    'client_id'     => $config['vk']['client_id'],
    'code_verifier' => $verifier,
    // если вернёт invalid_client — добавь секрет:
    // 'client_secret' => $config['vk']['client_secret'],
]);

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => $body,
  CURLOPT_HTTPHEADER     => [
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: application/json',
],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT        => 15,
]);
$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200) { header('text/plain'); exit("HTTP $http\n$resp"); }

$data    = json_decode($resp, true);
$idToken = $data['id_token']     ?? null;
$access  = $data['access_token'] ?? null;

if (!$idToken && $access) {
    // получить sub через userinfo
    $ch = curl_init('https://id.vk.com/oauth2/userinfo');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $access, 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $uResp = curl_exec($ch);
    $uHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($uHttp !== 200) {
        header('text/plain; charset=utf-8');
        exit("Ошибка userinfo HTTP $uHttp:\n$uResp");
    }

    $payload = json_decode($uResp, true);
    $vkId    = $payload['sub'] ?? null;
} elseif ($idToken) {
    // как у тебя было — распарсить id_token и достать sub
    [$h, $p, $s] = explode('.', $idToken);
    $payload = json_decode(base64_decode(strtr($p, '-_', '+/')), true);
    if (($payload['nonce'] ?? '') !== $nonce) exit('Неверный nonce');
    $vkId = $payload['sub'] ?? null;
} else {
    header('text/plain; charset=utf-8');
    exit("Не получили ни id_token, ни access_token:\n$resp");
}

if (!$vkId) exit('Не получили sub от VK ID');

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
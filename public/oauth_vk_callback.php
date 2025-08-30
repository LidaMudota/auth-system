<?php
$config = require __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
session_start();

if (!isset($_GET['code'])) {
    exit('No code');
}

$clientId = $config['vk']['client_id'];
$clientSecret = $config['vk']['client_secret'];
$redirectUri = $config['vk']['redirect_uri'];

$params = [
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'code'          => $_GET['code'],
    'redirect_uri'  => $redirectUri,
  ];

  $content = @file_get_contents('https://oauth.vk.com/access_token?' . http_build_query($params));
  if ($content === false) { exit('HTTP error'); }

  $response = json_decode($content, true);
  if (isset($response['error'])) { exit('VK error: ' . $response['error']); }

$vkId = (int)$response['user_id'];

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
header('Location: /auth-system/public/protected.php');
exit;

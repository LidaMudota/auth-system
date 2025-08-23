<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

$config = require __DIR__ . '/../config/config.php';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

if (!$code || !$state || $state !== ($_SESSION['vk_state'] ?? '')) {
    exit('Ошибка');
}
unset($_SESSION['vk_state']);

$params = [
    'client_id'     => $config['vk']['client_id'],
    'client_secret' => $config['vk']['client_secret'],
    'redirect_uri'  => $config['vk']['redirect_uri'],
    'code'          => $code,
    'grant_type'    => 'authorization_code',
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($params)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents('https://id.vk.com/oauth2/token', false, $context);
$data = json_decode($response, true);

if (!empty($data['user_id'])) {
    $vk_id = $data['user_id'];
    $stmt = db()->prepare('SELECT id FROM users WHERE vk_user_id = ?');
    $stmt->execute([$vk_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $stmt = db()->prepare("INSERT INTO users (login, password_hash, role, vk_user_id) VALUES (NULL, NULL, 'vk', ?)");
        $stmt->execute([$vk_id]);
        $user_id = db()->lastInsertId();
    } else {
        $user_id = $user['id'];
    }
    login_user($user_id, 'vk');
    header('Location: protected.php');
    exit;
}

echo 'Ошибка';
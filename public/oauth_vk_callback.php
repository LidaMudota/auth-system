<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

$config = require __DIR__ . '/../config/config.php';
$code = $_GET['code'] ?? '';

if (!$code) {
    exit('Ошибка');
}

$params = [
    'client_id' => $config['vk']['client_id'],
    'client_secret' => $config['vk']['client_secret'],
    'redirect_uri' => $config['vk']['redirect_uri'],
    'code' => $code,
];

$url = 'https://id.vk.com/access_token?' . http_build_query($params);
$response = file_get_contents($url);
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
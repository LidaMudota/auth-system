<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
$config = require __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['code'])) {
    $params = [
        'client_id'     => $config['vk']['client_id'],
        'client_secret' => $config['vk']['client_secret'],
        'redirect_uri'  => $config['vk']['redirect_uri'],
        'code'          => $_GET['code'],
    ];
    $url = 'https://oauth.vk.com/access_token?' . http_build_query($params);

    $json = @file_get_contents($url);
    if ($json === false) { http_response_code(502); exit; }
    $resp = json_decode($json, true);
    if (!empty($resp['error'])) { http_response_code(401); exit; }

    $userId = $resp['user_id'] ?? null;
    if (!$userId) { http_response_code(400); exit; }

    $login = 'vk_' . preg_replace('/[^0-9]/','',(string)$userId);
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE login=:login AND role="vk"');
    $stmt->execute([':login'=>$login]);
    $row = $stmt->fetch();
    $id = $row ? (int)$row['id'] : (function() use($pdo,$login){
        $ins = $pdo->prepare('INSERT INTO users (login,password_hash,role) VALUES (:l,NULL,"vk")');
        $ins->execute([':l'=>$login]);
        return (int)$pdo->lastInsertId();
    })();

    login_user($id,'vk');
    header('Location: protected.php'); exit;
}

/* твой прежний POST JSON сценарий оставляем: */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw,true);
    $userId = $data['user_id'] ?? $data['vk_user_id'] ?? null;
    if (!$userId) { http_response_code(400); exit; }
    $login = 'vk_' . preg_replace('/[^0-9]/','',(string)$userId);
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE login=:login AND role="vk"');
    $stmt->execute([':login'=>$login]);
    $row = $stmt->fetch();
    $id = $row ? (int)$row['id'] : (function() use($pdo,$login){
        $ins = $pdo->prepare('INSERT INTO users (login,password_hash,role) VALUES (:l,NULL,"vk")');
        $ins->execute([':l'=>$login]);
        return (int)$pdo->lastInsertId();
    })();
    login_user($id,'vk');
    http_response_code(204); exit;
}

http_response_code(405);

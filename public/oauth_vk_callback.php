<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

start_session();

$raw = file_get_contents('php://input');
if ($raw) {
    $j = json_decode($raw, true);
    $vkId = isset($j['vk_user_id']) ? (int)$j['vk_user_id'] : 0;

    if ($vkId > 0) {
        $stmt = db()->prepare('SELECT id FROM users WHERE vk_user_id = ?');
        $stmt->execute([$vkId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $stmt = db()->prepare(
              "INSERT INTO users (login, password_hash, role, vk_user_id)
               VALUES (NULL, NULL, 'vk', ?)"
            );
            $stmt->execute([$vkId]);
            $userId = db()->lastInsertId();
        } else {
            $userId = $user['id'];
        }

        login_user($userId, 'vk');
        http_response_code(204);
        exit;
    }
}

echo 'No data';

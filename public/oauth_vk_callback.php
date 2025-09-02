<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$userId = $data['user_id'] ?? $data['vk_user_id'] ?? null;
if (!$userId) {
    http_response_code(400);
    exit;
}
$login = 'vk_' . preg_replace('/[^0-9]/', '', (string)$userId);
$pdo = db();
$stmt = $pdo->prepare('SELECT id FROM users WHERE login = :login AND role = "vk"');
$stmt->execute([':login' => $login]);
$user = $stmt->fetch();
if ($user) {
    $id = (int)$user['id'];
} else {
    $stmt = $pdo->prepare('INSERT INTO users (login, password_hash, role) VALUES (:login, NULL, "vk")');
    $stmt->execute([':login' => $login]);
    $id = (int)$pdo->lastInsertId();
}
login_user($id, 'vk');
http_response_code(204);
exit;
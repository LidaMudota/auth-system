<?php
require_once __DIR__ . '/../src/db.php';
$pdo = db();
$login = 'admin';
$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
$stmt->execute([$login]);
if (!$stmt->fetch()) {
    $pdo->prepare('INSERT INTO users (login, password_hash, role) VALUES (?,?,"admin")')
        ->execute([$login, $hash]);
    echo "Создан администратор $login";
} else {
    echo "Администратор уже существует";
}
<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

start_session();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($login === '' || $password === '') {
        $error = 'Заполните все поля.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = 'Логин уже занят.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare("INSERT INTO users (login, password_hash, role, vk_user_id) VALUES (?, ?, 'user', NULL)");
            $stmt->execute([$login, $hash]);
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="assets/voltage.css">
</head>
<body>
    <div class="scene">
        <div class="cube">
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
        </div>
    </div>
    <?php if ($error): ?><p class="alert"><?=htmlspecialchars($error)?></p><?php endif; ?>
    <form method="post" class="frame">
        <label>Логин: <input type="text" name="login" required></label>
        <label>Пароль: <input type="password" name="password" required></label>
        <button class="brutalist-button" type="submit">
            <span class="button-text"><span></span><span>Регистрация</span></span>
        </button>
    </form>
    <p>
        <a class="brutalist-button" href="oauth_vk_start.php">
            <span class="button-text"><span></span><span>VK</span></span>
        </a>
    </p>
</body>
</html>
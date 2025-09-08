<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($login === '' || $pass === '') {
        $error = 'Заполните все поля';
    } else {
        $s = db()->prepare('SELECT id FROM users WHERE login = ?');
        $s->execute([$login]);
        if ($s->fetch()) {
            $error = 'Логин уже занят';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            db()->prepare('INSERT INTO users (login, password_hash, role) VALUES (?,?,"user")')->execute([$login, $hash]);
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="assets/voltage.css">
<title>Регистрация</title>
</head>
<body>
<form method="post">
    <input type="text" name="login" placeholder="Логин" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <button>Зарегистрироваться</button>
    <a href="oauth_vk_start.php">Авторизоваться через VK</a>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
</form>
</body>
</html>
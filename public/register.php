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
</head>
<body>
<?php if ($error): ?><p><?=htmlspecialchars($error)?></p><?php endif; ?>
<form method="post">
    <label>Логин: <input type="text" name="login" required></label><br>
    <label>Пароль: <input type="password" name="password" required></label><br>
    <button type="submit">Зарегистрироваться</button>
</form>
<p><a href="oauth_vk_start.php">Авторизоваться через VK</a></p>
</body>
</html>
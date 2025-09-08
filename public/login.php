<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/logger.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'Неверный CSRF';
    } else {
        $login = $_POST['login'] ?? '';
        $pass = $_POST['password'] ?? '';
        $s = db()->prepare('SELECT id, password_hash, role FROM users WHERE login = ?');
        $s->execute([$login]);
        $u = $s->fetch(PDO::FETCH_ASSOC);
        if ($u && $u['password_hash'] && password_verify($pass, $u['password_hash'])) {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['role'] = $u['role'];
            if (!empty($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                db()->prepare('UPDATE users SET remember_token = ? WHERE id = ?')->execute([$token, $u['id']]);
                setcookie('remember', $token, time() + 86400 * 30, '/', '', false, true);
            }
            header('Location: protected.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
            logAuthFail($login);
        }
    }
}
$token = csrf_token();
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="assets/voltage.css">
<title>Вход</title>
</head>
<body>
<form method="post">
    <input type="hidden" name="csrf" value="<?= $token ?>">
    <input type="text" name="login" placeholder="Логин" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <label><input type="checkbox" name="remember"> Запомнить меня</label>
    <button>Войти</button>
    <a href="oauth_vk_start.php">Авторизоваться через VK</a>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
</form>
</body>
</html>
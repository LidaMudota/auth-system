<?php
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/logger.php';
$config = require __DIR__ . '/../config/config.php';

$pdo = db();
attempt_cookie_login();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['token'] ?? '')) {
        $message = 'CSRF';
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);
        $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $user['password_hash'])) {
                login_user($user['id'], $user['role']);
                if ($remember) {
                    $token = bin2hex(random_bytes(16));
                    $pdo->prepare('UPDATE users SET remember_token = ? WHERE id = ?')->execute([$token, $user['id']]);
                    setcookie('remember', $token, time() + $config['cookie_lifetime'], '/', '', false, true);
                }
                header('Location: /protected.php');
                exit;
            } else {
                log_failed_login($login, $_SERVER['REMOTE_ADDR'] ?? '');
                $message = 'Неверный пароль';
            }
        } else {
            $message = 'Пользователь не найден';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Вход</title>
<link rel="stylesheet" href="/assets/voltage.css">
</head>
<body>
<div class="header">Вход</div>
<div class="box">
<?php if($message) echo '<p>'.$message.'</p>'; ?>
<form method="post">
<input type="hidden" name="token" value="<?=htmlspecialchars(csrf_token())?>">
<input type="text" name="login" placeholder="Логин">
<input type="password" name="password" placeholder="Пароль">
<label><input type="checkbox" name="remember"> Запомнить меня</label>
<button type="submit">Войти</button>
</form>
<p><a href="/oauth_vk_start.php">Войти через VK</a></p>
</div>
</body>
</html>
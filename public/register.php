<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

$pdo = db();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($login && $password) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $message = 'Логин занят';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (login, password_hash, role) VALUES (?,?,"local")')
                ->execute([$login, $hash]);
            $id = $pdo->lastInsertId();
            login_user($id, 'local');
            header('Location: /protected.php');
            exit;
        }
    } else {
        $message = 'Заполните поля';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Регистрация</title>
<link rel="stylesheet" href="/assets/voltage.css">
</head>
<body>
<div class="header">Регистрация</div>
<div class="box">
<?php if($message) echo '<p>'.$message.'</p>'; ?>
<form method="post">
<input type="text" name="login" placeholder="Логин">
<input type="password" name="password" placeholder="Пароль">
<button type="submit">Зарегистрироваться</button>
</form>
<p><a href="/oauth_vk_start.php">Войти через VK</a></p>
</div>
</body>
</html>
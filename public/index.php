<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
attempt_cookie_login();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Главная</title>
<link rel="stylesheet" href="assets/voltage.css">
</head>
<body>
<div class="header">Auth System</div>
<div class="box">
<a href="register.php">Регистрация</a> |
<a href="login.php">Вход</a> |
<a href="protected.php">Закрытая</a> |
<a href="role_demo.php">Кнопка</a> |
<a href="messages.php">Сообщения</a>
</div>
</body>
</html>
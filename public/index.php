<?php
require_once __DIR__ . '/../src/auth.php';
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="assets/voltage.css">
<title>Главная</title>
</head>
<body>
<nav>
    <a href="register.php">Регистрация</a>
    <a href="login.php">Вход</a>
    <a href="protected.php">Закрытая страница</a>
    <?php if (isAuth()): ?>
    <a href="logout.php">Выход</a>
    <?php endif; ?>
</nav>
</body>
</html>
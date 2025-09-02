<?php
require_once __DIR__ . '/../src/auth.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная</title>
    <link rel="stylesheet" href="assets/voltage.css">
</head>
<body>
<div class="container">
    <ul>
        <li><a href="register.php">Регистрация</a></li>
        <li><a href="login.php">Войти</a></li>
        <li><a href="protected.php">Закрытая страница</a></li>
        <?php if ($user): ?>
            <li><a href="logout.php">Выйти</a></li>
        <?php endif; ?>
    </ul>
</div>
</body>
</html>
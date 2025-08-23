<?php
require_once __DIR__ . '/../src/auth.php';
require_auth();
$role = current_role();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Закрытая страница</title>
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
    <p>Тихое место для своих.</p>
    <?php if ($role === 'vk'): ?>
    <img src="assets/vk-only.jpg" alt="VK">
    <?php endif; ?>
    <p>
        <a class="brutalist-button" href="logout.php">
            <span class="button-text"><span></span><span>Выход</span></span>
        </a>
    </p>
</body>
</html>
<?php
require_once __DIR__ . '/../src/auth.php';
require_auth();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Закрытая</title>
    <link rel="stylesheet" href="assets/voltage.css">
</head>
<body>
<div class="container">
    <p>Доступ только для авторизованных пользователей.</p>
    <?php if (is_role('vk')): ?>
        <img src="assets/vk-only.jpg" alt="VK">
    <?php endif; ?>
</div>
</body>
</html>
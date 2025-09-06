<?php
// /auth-system/public/protected.php
declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';

$user = require_auth();
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Закрытая страница</title>
</head>
<body>
  <h1>Секретная зона</h1>
  <p>Этот абзац виден всем авторизованным пользователям.</p>

  <?php if (is_vk_role($user)): ?>
    <p>Ниже — контент только для роли <strong>vk</strong>:</p>
    <img src="/auth-system/public/assets/vk-only.jpg" alt="VK only" width="420">
  <?php else: ?>
    <p><em>Картинка доступна только для роли «vk».</em></p>
  <?php endif; ?>

  <p>
    <a href="/auth-system/public/index.php">Главная</a> —
    <a href="/auth-system/public/logout.php">Выйти</a>
  </p>
</body>
</html>

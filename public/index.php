<?php
// /auth-system/public/index.php
declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';

$user = current_user();
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Auth System — Главная</title>
</head>
<body>
  <h1>Добро пожаловать</h1>
  <?php if ($user): ?>
    <p>Вы вошли как <strong><?= htmlspecialchars($user['login']) ?></strong> (роль: <?= htmlspecialchars($user['role']) ?>)</p>
    <p><a href="/auth-system/public/protected.php">Закрытая страница</a></p>
    <p><a href="/auth-system/public/role_demo.php">Демо ролей (кнопка с ACL)</a></p>
    <p><a href="/auth-system/public/logout.php">Выйти</a></p>
  <?php else: ?>
    <p><a href="/auth-system/public/register.php">Регистрация</a></p>
    <p><a href="/auth-system/public/login.php">Войти</a></p>
  <?php endif; ?>
</body>
</html>

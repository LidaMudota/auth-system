<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
attempt_cookie_login();
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
<div class="header">Секрет</div>
<div class="box">
<p>Это видят все авторизованные.</p>
<?php if (user_role() === 'vk'): ?>
    <img src="assets/vk-only.jpg" alt="Только для VK">
<?php endif; ?>
<p><a href="logout.php">Выход</a></p>
</div>
</body>
</html>
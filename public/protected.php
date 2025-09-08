<?php
require_once __DIR__ . '/../src/auth.php';
requireAuth();
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="assets/voltage.css">
<title>Закрытая страница</title>
</head>
<body>
<p>Привет, это закрытая зона.</p>
<?php if (userRole() === 'vk'): ?>
<img src="assets/vk-only.jpg" alt="VK">
<?php endif; ?>
</body>
</html>
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
</head>
<body>
<p>Тихое место для своих.</p>
<?php if ($role === 'vk'): ?>
<img src="assets/vk-only.jpg" alt="VK">
<?php endif; ?>
</body>
</html>
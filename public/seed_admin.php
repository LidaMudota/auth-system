<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/navigation_band.php';

$pdo = db();
attempt_cookie_login();

$login = 'admin';
$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
$stmt->execute([$login]);
if (!$stmt->fetch()) {
    $pdo->prepare('INSERT INTO users (login, password_hash, role) VALUES (?,?,"admin")')
        ->execute([$login, $hash]);
        $message = "Создан администратор $login";
} else {
    $message = 'Администратор уже существует';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Создание администратора</title>
<link rel="stylesheet" href="assets/voltage.css">
</head>
<body>
<?php navigation_band(); ?>
<div class="header">Создание администратора</div>
<div class="box">
<p><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
<p><a href="protected.php">Перейти в закрытую зону</a></p>
</div>
</body>
</html>
<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
attempt_cookie_login();
require_auth();
$isAdmin = user_role() === 'admin';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Демонстрация роли</title>
<link rel="stylesheet" href="assets/voltage.css">
<script>
function greet(){
    alert('Добро пожаловать');
}
</script>
</head>
<body>
<div class="header">Роль</div>
<div class="box">
<button <?= $isAdmin ? '' : 'class="disabled" disabled' ?> onclick="greet()">Нажми меня</button>
<p><a href="index.php">Главная</a></p>
</div>
</body>
</html>
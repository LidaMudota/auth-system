<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

if (isAuth()) {
    db()->prepare('UPDATE users SET remember_token = NULL WHERE id = ?')->execute([$_SESSION['user_id']]);
}
setcookie('remember', '', time() - 3600, '/', '', false, true);
session_unset();
session_destroy();
header('Location: index.php');
exit;
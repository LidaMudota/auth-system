<?php
require_once __DIR__ . '/../src/auth.php';
logout_user();
setcookie('remember', '', time()-3600, '/');
header('Location: index.php');
exit;
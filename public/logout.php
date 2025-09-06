<?php
// /auth-system/public/logout.php
declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';

logout_user();
header('Location: /auth-system/public/index.php');
exit;

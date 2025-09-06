<?php
// /auth-system/public/oauth_vk_start.php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

http_response_code(501);
header('Content-Type: text/plain; charset=utf-8');
echo "VK OAuth пока не подключён.\nНастрой client_id/secret/redirect_uri в /auth-system/config/config.php и реализуй обмен в oauth_vk_callback.php.";

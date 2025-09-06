<?php
// /auth-system/public/oauth_vk_callback.php
declare(strict_types=1);

http_response_code(501);
header('Content-Type: text/plain; charset=utf-8');
echo "handle VK callback here (обмен code -> access_token, поиск/создание пользователя с ролью 'vk').";

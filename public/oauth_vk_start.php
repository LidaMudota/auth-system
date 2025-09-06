<?php
// /auth-system/public/oauth_vk_start.php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$authUrl = 'https://oauth.vk.com/authorize?' . http_build_query([
    'client_id'     => VK_CLIENT_ID,
    'redirect_uri'  => VK_REDIRECT_URI,
    'response_type' => 'code',
    'v'             => '5.199',     // версия API
    'scope'         => 'email',     // нам достаточно email; добавишь, если нужно
    // 'state'      => bin2hex(random_bytes(8)), // опционально, для защиты и возврата состояния
]);

header('Location: ' . $authUrl);
exit;
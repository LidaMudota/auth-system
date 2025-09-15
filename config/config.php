<?php
return [
    'db' => [
        'dsn'  => 'mysql:host=localhost;dbname=auth;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
    ],

    'vk' => [
        'client_id'     => 'YOUR_VK_CLIENT_ID',
        'client_secret' => 'YOUR_VK_CLIENT_SECRET',
        'redirect_uri'  => 'https://your-domain/oauth_vk_callback.php',
    
        'auth_url'      => 'https://id.vk.com/authorize',
        'token_url'     => 'https://id.vk.com/oauth2/auth',
    
        'scope'         => 'openid email',
    ],    

    'logs' => [
        'auth' => __DIR__ . '/../storage/logs/auth.log',
    ],

    'cookie_lifetime' => 60 * 60 * 24 * 7,
];

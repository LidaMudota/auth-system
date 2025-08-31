<?php
return [
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=auth;charset=utf8mb4',
        'user' => 'root',
        'password' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ],
    ],
    'vk' => [
        'client_id' => '54095571',
        'client_secret' => 'WRLxvjS5XiYFjnorE1aj',
        'redirect_uri' => 'http://localhost/auth-system/public/oauth_vk_callback.php',
        'version' => '5.126',
    ],
    'log_file' => __DIR__ . '/../storage/logs/auth.log',
];
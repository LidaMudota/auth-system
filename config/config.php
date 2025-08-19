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
        'client_id' => '54061173',
        'client_secret' => '0ikovGgaDYXDIQMzK7rD',
        'redirect_uri' => (($_SERVER['HTTP_HOST'] ?? '') === 'localhost')
        ? 'http://localhost/auth-system/public/oauth_vk_callback.php'
        : 'https://tri-sobaki.ru/oauth_vk_callback.php',
        'version' => '5.126',
    ],
    'log_file' => __DIR__ . '/../storage/logs/auth.log',
];
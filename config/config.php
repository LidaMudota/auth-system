<?php
return [
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'auth',
        'charset' => 'utf8mb4',
    ],
    'vk' => [
        'client_id' => 54095571,
        'client_secret' => 'WRLxvjS5XiYFjnorE1aj',
        'redirect_uri' => 'http://localhost/auth-system/public/oauth_vk_callback.php',
    ],
    'log' => [
        'auth_log_path' => __DIR__ . '/../storage/logs/auth.log',
    ],
    'security' => [
        'csrf_key' => 'csrf_token',
        'remember_cookie_name' => 'remember_token',
        'remember_cookie_lifetime' => 60*60*24*7,
        'remember_token_len' => 64,
        'csrf_token_len' => 32,
    ],
];
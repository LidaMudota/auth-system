<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$ambientEnv = Dotenv::createImmutable(dirname(__DIR__));
$ambientEnv->safeLoad();

return [
    'db' => [
        'dsn'  => 'mysql:host=localhost;dbname=auth;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
    ],

    'vk' => [
        'client_id'     => $_ENV['VK_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['VK_CLIENT_SECRET'] ?? '',
        'redirect_uri'  => $_ENV['VK_REDIRECT_URI'] ?? '',
    
        'auth_url'      => 'https://id.vk.com/authorize',
        'token_url'     => 'https://id.vk.com/oauth2/auth',
    
        'scope'         => 'openid email',
    ],    

    'logs' => [
        'auth' => __DIR__ . '/../storage/logs/auth.log',
    ],

    'cookie_lifetime' => 60 * 60 * 24 * 7,
];

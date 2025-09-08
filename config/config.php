<?php
return [
    'db' => [
        'host'     => 'localhost',
        'user'     => 'root',
        'password' => '',
        'dbname'   => 'auth_system',
    ],
    'vk' => [
        'client_id'     => '54095571',
        'client_secret' => 'WRLxvjS5XiYFjnorE1aj',
        // ОБНОВИ при смене LT-домена:
        'redirect_uri'  => 'https://grumpy-dogs-shop.loca.lt/oauth_vk_callback.php',

        // новое VK ID
        'auth_url'      => 'https://id.vk.com/authorize',
        'token_url'     => 'https://id.vk.com/oauth2/auth',
        // минимально нужно openid; email добавим по возможности
        'scopes'        => ['openid','email'],
    ],
    'log_file' => __DIR__ . '/../storage/logs/auth.log',
];

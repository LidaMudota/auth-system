<?php
$config = require __DIR__ . '/../config/config.php';
$clientId = $config['vk']['client_id'];
$redirectUri = $config['vk']['redirect_uri'];

$params = [
    'client_id'     => $clientId,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'v'             => '5.126',
    'scope'         => ''
];

header('Location: https://oauth.vk.com/authorize?' . http_build_query($params));
exit;

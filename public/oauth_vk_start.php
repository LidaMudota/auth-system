<?php
session_start();
$config = require __DIR__ . '/../config/config.php';

$state = bin2hex(random_bytes(16));
$nonce = bin2hex(random_bytes(16));

$_SESSION['vk_state'] = $state;
$_SESSION['vk_nonce'] = $nonce;

$params = [
    'client_id'     => $config['vk']['client_id'],
    'redirect_uri'  => $config['vk']['redirect_uri'],
    'response_type' => 'code',
    'scope'         => 'openid',
    'state'         => $state,
    'nonce'         => $nonce,
];

header('Location: https://id.vk.com/authorize?' . http_build_query($params));
exit;

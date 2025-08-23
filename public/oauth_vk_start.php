<?php
session_start();
$config = require __DIR__ . '/../config/config.php';
$state = bin2hex(random_bytes(16));
$_SESSION['vk_state'] = $state;
$params = [
    'client_id'    => $config['vk']['client_id'],
    'redirect_uri' => $config['vk']['redirect_uri'],
    'response_type'=> 'code',
    'scope'        => 'openid',
    'state'        => $state,
];
$auth_url = 'https://id.vk.com/authorize?' . http_build_query($params);
header("Location: $auth_url");
exit;

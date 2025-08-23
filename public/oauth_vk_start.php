<?php
$config = require __DIR__ . '/../config/config.php';
$params = [
    'client_id' => $config['vk']['client_id'],
    'redirect_uri' => $config['vk']['redirect_uri'],
    'response_type' => 'code',
    'v' => $config['vk']['version'],
    'scope' => ''
];
header('Location: https://id.vk.com/authorize?' . http_build_query($params));
exit;
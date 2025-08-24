<?php
session_start();
$config = require __DIR__ . '/../config/config.php';

$state = bin2hex(random_bytes(16));
$nonce = bin2hex(random_bytes(16));
$verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
$challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

$_SESSION['vk_state'] = $state;
$_SESSION['vk_nonce'] = $nonce;
$_SESSION['vk_verifier'] = $verifier;

$params = [
    'client_id'             => $config['vk']['client_id'],
    'redirect_uri'          => $config['vk']['redirect_uri'],
    'response_type'         => 'code',
    'scope'                 => 'openid',
    'state'                 => $state,
    'nonce'                 => $nonce,
    'code_challenge'        => $challenge,
    'code_challenge_method' => 'S256',
];

header('Location: https://id.vk.com/authorize?' . http_build_query($params));
exit;

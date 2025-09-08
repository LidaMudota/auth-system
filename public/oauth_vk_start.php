<?php
// запускает VK ID Authorization Request (PKCE)
session_start();
$c = require __DIR__ . '/../config/config.php';

// PKCE
function b64u($s){ return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
$code_verifier  = b64u(random_bytes(32));
$_SESSION['vk_pkce_verifier'] = $code_verifier;
$code_challenge = b64u(hash('sha256', $code_verifier, true));

// (не обязательно, но полезно) CSRF state
$state = bin2hex(random_bytes(16));
$_SESSION['vk_oauth_state'] = $state;

$params = [
    'client_id'             => $c['vk']['client_id'],
    'redirect_uri'          => $c['vk']['redirect_uri'],
    'response_type'         => 'code',
    'scope'                 => implode(' ', $c['vk']['scopes']),
    'code_challenge'        => $code_challenge,
    'code_challenge_method' => 'S256',
    'state'                 => $state,
];

header('Location: ' . $c['vk']['auth_url'] . '?' . http_build_query($params));
exit;

<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

$cfg = require __DIR__ . '/../config/config.php';
$vk  = $cfg['vk'];

$base64url = static function (string $bin): string {
  return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
};

$code_verifier  = $base64url(random_bytes(32));
$_SESSION['vk_pkce_verifier'] = $code_verifier;
$code_challenge = $base64url(hash('sha256', $code_verifier, true));

$state = bin2hex(random_bytes(16));
$_SESSION['vk_oauth_state'] = $state;

$params = [
  'client_id'             => $vk['client_id'],
  'redirect_uri'          => $vk['redirect_uri'],
  'response_type'         => 'code',
  'scope'                 => $vk['scope'],
  'state'                 => $state,
  'code_challenge'        => $code_challenge,
  'code_challenge_method' => 'S256',
];

header('Location: ' . $vk['auth_url'] . '?' . http_build_query($params));
exit;

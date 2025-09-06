<?php
// /auth-system/public/oauth_vk_callback.php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

if (!isset($_GET['code'])) {
    http_response_code(400);
    exit('Missing code.');
}

$code = (string)$_GET['code'];

$params = [
    'client_id'     => VK_CLIENT_ID,
    'client_secret' => VK_CLIENT_SECRET,
    'redirect_uri'  => VK_REDIRECT_URI,
    'code'          => $code,
];

$tokenUrl = 'https://oauth.vk.com/access_token?' . http_build_query($params);

// Лучше curl, но для простоты по учебному:
$content = @file_get_contents($tokenUrl);
if ($content === false) {
    http_response_code(502);
    exit('VK access_token request failed.');
}

$resp = json_decode($content, true);
if (isset($resp['error'])) {
    http_response_code(400);
    exit('VK error: ' . $resp['error'] . ' — ' . ($resp['error_description'] ?? ''));
}

$accessToken = $resp['access_token'] ?? null;
$userId      = $resp['user_id'] ?? null;   // числовой VK id
$email       = $resp['email']   ?? null;   // приходит, если разрешен scope=email

if (!$accessToken || !$userId) {
    http_response_code(400);
    exit('No access_token or user_id from VK.');
}

// Логин формируем стабильно: vk_{id} (email используем, если пришёл)
$login = $email ?: ('vk_' . $userId);

// Ищем пользователя (по login или vk_id)
$pdo = db();
$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE vk_id = :vkid OR login = :login LIMIT 1');
    $stmt->execute([':vkid' => (int)$userId, ':login' => $login]);
    $u = $stmt->fetch();

    if ($u) {
        $userIdDb = (int)$u['id'];
    } else {
        // Для VK-пользователей пароль не нужен; роль "vk"
        $ins = $pdo->prepare('INSERT INTO users (login, password_hash, role, vk_id) VALUES (:l, :h, "vk", :vkid)');
        $ins->execute([
            ':l'   => $login,
            ':h'   => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT), // фиктивный
            ':vkid'=> (int)$userId,
        ]);
        $userIdDb = (int)$pdo->lastInsertId();
    }

    $pdo->commit();
    // Логиним и ведём на защищённую страницу
    login_user($userIdDb);
    header('Location: /auth-system/public/protected.php');
    exit;

} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    exit('DB error: ' . $e->getMessage());
}
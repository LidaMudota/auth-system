<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/logger.php';

start_session();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!check_csrf($token)) {
        $error = 'Ошибка авторизации.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt = db()->prepare("SELECT id, password_hash FROM users WHERE login = ? AND role = 'user'");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            login_user($user['id'], 'user');
            header('Location: protected.php');
            exit;
        } else {
            log_fail($login);
            $error = 'Ошибка авторизации.';
        }
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="assets/voltage.css">
</head>
<body>
    <div class="scene">
        <div class="cube">
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
        </div>
    </div>
    <?php if ($error): ?><p class="alert"><?=htmlspecialchars($error)?></p><?php endif; ?>
    <form method="post" class="frame">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf)?>">
        <label>Логин: <input type="text" name="login" required></label>
        <label>Пароль: <input type="password" name="password" required></label>
        <button class="brutalist-button" type="submit">
            <span class="button-text"><span></span><span>Войти</span></span>
        </button>
    </form>
    <div id="vkid-btn" style="margin-top:12px"></div>

<script src="https://unpkg.com/@vkid/sdk@<3.0.0/dist-sdk/umd/index.js"></script>
<script>
if ('VKIDSDK' in window) {
  const VKID = window.VKIDSDK;

  VKID.Config.init({
    app: 54061173,
    redirectUrl: 'http://localhost/auth-system/public/oauth_vk_callback.php',
    responseMode: VKID.ConfigResponseMode.Callback,
    source: VKID.ConfigSource.LOWCODE,
    scope: ''
  });

  const oneTap = new VKID.OneTap();
  oneTap.render({
      container: document.getElementById('vkid-btn'),
      fastAuthEnabled: false,
      showAlternativeLogin: false
    })
    .on(VKID.WidgetEvents.ERROR, console.error)
    .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, function (payload) {
      const code = payload.code;
      const deviceId = payload.device_id;

      VKID.Auth.exchangeCode(code, deviceId)
        .then(function (data) {
          const vkUserId = data.user_id || (data.user && data.user.id);
          return fetch('/auth-system/public/oauth_vk_callback.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ vk_user_id: vkUserId })
          });
        })
        .then(() => { location.href = '/auth-system/public/protected.php'; })
        .catch(console.error);
    });
}
</script>
</body>
</html>
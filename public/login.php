<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/logger.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass = $_POST['pass'] ?? '';
    if (!csrf_check($_POST['_csrf'] ?? '')) {
        log_failed_login($login, 'csrf_invalid');
        $error = 'Неверные данные';
    } elseif ($login === '' || $pass === '') {
        log_failed_login($login, 'invalid_input');
        $error = 'Неверные данные';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE login = :login AND role = "user" AND password_hash IS NOT NULL');
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();
        if (!$user) {
            log_failed_login($login, 'user_not_found');
            $error = 'Неверные данные';
        } elseif (!password_verify($pass, $user['password_hash'])) {
            log_failed_login($login, 'wrong_password');
            $error = 'Неверные данные';
        } else {
            login_user((int)$user['id'], 'user');
            if (!empty($_POST['remember'])) {
                issue_remember_token((int)$user['id']);
            }
            header('Location: protected.php');
            exit;
        }
    }
}
$token = csrf_get();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="assets/voltage.css">
</head>
<body>
<div class="container">
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <form method="post">
        <input type="text" name="login" placeholder="Логин" required>
        <input type="password" name="pass" placeholder="Пароль" required>
        <label><input type="checkbox" name="remember" value="1"> Запомнить меня</label>
        <input type="hidden" name="_csrf" value="<?= $token ?>">
        <button type="submit">Войти</button>
    </form>
    <div id="vkid-one-tap"></div>
    <script src="/auth-system/public/assets/vkid-sdk.js"></script>
    <script>
      const VKID = window.VKIDSDK;
      VKID.Config.init({
        app: 54095571,
        responseMode: VKID.ConfigResponseMode.PostMessage,
        source: VKID.ConfigSource.LOWCODE
      });

      const container = document.getElementById('vkid-one-tap');
      new VKID.OneTap()
      .render({ container, showAlternativeLogin: true })
        .on(VKID.WidgetEvents.ERROR, (e) => console.warn('VKID ERROR', e))
        .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, async (payload) => {
          console.log('LOGIN_SUCCESS payload:', payload);
          try {
            const res = await VKID.Auth.exchangeCode(payload.code, payload.device_id);
            console.log('exchangeCode result:', res);

            const r = await fetch('/auth-system/public/oauth_vk_callback.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(res)
            });
            console.log('callback POST status:', r.status);
            if (r.status === 200 || r.status === 204) location.href = 'protected.php';
          } catch (err) {
            console.error('exchangeCode/callback error:', err);
          }
        });
    </script>
</div>
</body>
</html>
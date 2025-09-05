<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass = $_POST['pass'] ?? '';
    if (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $login) || strlen($pass) < 8) {
        $error = 'Неверные данные';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = :login');
        $stmt->execute([':login' => $login]);
        if ($stmt->fetch()) {
            $error = 'Неверные данные';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (login, password_hash, role) VALUES (:l, :h, "user")');
            $stmt->execute([':l' => $login, ':h' => $hash]);
            $id = (int)$pdo->lastInsertId();
            login_user($id, 'user');
            header('Location: protected.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="assets/voltage.css">
</head>
<body>
<div class="container">
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <form method="post">
        <input type="text" name="login" placeholder="Логин" required>
        <input type="password" name="pass" placeholder="Пароль" required>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <div>
    <script src="/auth-system/public/assets/vkid-sdk.js"></script>
    <script>
      const VKID = window.VKIDSDK;
      VKID.Config.init({
        app: 54095571,
        responseMode: VKID.ConfigResponseMode.PostMessage,
        source: VKID.ConfigSource.LOWCODE
      });

      new VKID.OneTap()
        .render({ container: document.currentScript.parentElement, showAlternativeLogin: true })
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
</div>
<a href="https://oauth.vk.com/authorize?client_id=54095571&redirect_uri=http%3A%2F%2Flocalhost%2Fauth-system%2Fpublic%2Foauth_vk_callback.php&response_type=code&v=5.199&state=test" target="_blank">
  ТЕСТОВЫЙ ВХОД ЧЕРЕЗ VK (без SDK)
</a>
</body>
</html>
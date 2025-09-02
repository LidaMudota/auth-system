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
    <div>
      <script src="https://unpkg.com/@vkid/sdk@<3.0.0/dist-sdk/umd/index.js"></script>
      <script type="text/javascript">
        if ('VKIDSDK' in window) {
          const VKID = window.VKIDSDK;

          VKID.Config.init({
            app: 54095571,
            redirectUrl: 'http://localhost/auth-system/public/oauth_vk_callback.php',
            responseMode: VKID.ConfigResponseMode.Callback,
            source: VKID.ConfigSource.LOWCODE,
            scope: '', // Заполните нужными доступами по необходимости
          });

          const oneTap = new VKID.OneTap();

          oneTap.render({
            container: document.currentScript.parentElement,
            showAlternativeLogin: true
          })
          .on(VKID.WidgetEvents.ERROR, vkidOnError)
          .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, function (payload) {
            const code = payload.code;
            const deviceId = payload.device_id;

            VKID.Auth.exchangeCode(code, deviceId)
              .then(vkidOnSuccess)
              .catch(vkidOnError);
          });
        
          function vkidOnSuccess(data) {
            fetch('/auth-system/public/oauth_vk_callback.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(data)
            })
            .then(r => { if (r.status === 200 || r.status === 204) { window.location.href = 'protected.php'; } else { alert('Ошибка VK'); }})
            .catch(() => alert('Ошибка VK'));
          }
        
          function vkidOnError(error) {
            alert('Ошибка VK');
          }
        }
      </script>
    </div>
</div>
</body>
</html>
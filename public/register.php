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
      </script>
      <script type="text/javascript">
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
      </script>
    </div>
</div>
</body>
</html>
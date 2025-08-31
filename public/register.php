<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';

start_session();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($login === '' || $password === '') {
        $error = 'Заполните все поля.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = 'Логин уже занят.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare("INSERT INTO users (login, password_hash, role, vk_user_id) VALUES (?, ?, 'user', NULL)");
            $stmt->execute([$login, $hash]);
            header('Location: login.php');
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
        <label>Логин: <input type="text" name="login" required></label>
        <label>Пароль: <input type="password" name="password" required></label>
        <button class="brutalist-button" type="submit">
            <span class="button-text"><span></span><span>Регистрация</span></span>
        </button>
    </form>
    <div style="margin-top:12px">
        <script src="https://unpkg.com/@vkid/sdk@3.0.0/dist-sdk/umd/index.js"></script>
        <script>
        if ('VKIDSDK' in window) {
            const VKID = window.VKIDSDK;

            VKID.Config.init({
                app: 54095571,
                redirectUrl: 'http://localhost/auth-system/public/oauth_vk_callback.php',
                responseMode: VKID.ConfigResponseMode.Callback,
                source: VKID.ConfigSource.LOWCODE,
                scope: ''
            });

            const oneTap = new VKID.OneTap();

            oneTap.render({
                container: document.currentScript.parentElement,
                fastAuthEnabled: false,
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
                const vkUserId = data.user_id || (data.user && data.user.id);
                fetch('/auth-system/public/oauth_vk_callback.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ vk_user_id: vkUserId })
                })
                .then(() => { location.href = '/auth-system/public/protected.php'; })
                .catch(vkidOnError);
            }

            function vkidOnError(error) {
                console.error(error);
            }
        }
        </script>
    </div>
</body>
</html>
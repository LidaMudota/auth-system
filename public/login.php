<?php
// /auth-system/public/login.php
declare(strict_types=1);

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/logger.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Проверка CSRF
    csrf_validate_or_die($_POST['csrf_token'] ?? null);

    $login = trim((string)($_POST['login'] ?? ''));
    $pass  = (string)($_POST['pass'] ?? '');
    $remember = isset($_POST['remember']);

    if ($login === '' || $pass === '') {
        $error = 'Введите логин и пароль.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, login, password_hash FROM users WHERE login = :l LIMIT 1');
        $stmt->execute([':l' => $login]);
        $u = $stmt->fetch();

        if (!$u || !password_verify($pass, $u['password_hash'])) {
            $error = 'Неверный логин или пароль.';
            log_failed_login($login, 'bad_credentials');
        } else {
            login_user((int)$u['id']);

            if ($remember) {
                set_remember_me((int)$u['id']);
            } else {
                clear_remember_me((int)$u['id']);
            }

            header('Location: /auth-system/public/protected.php');
            exit;
        }
    }
}

$csrfInput = csrf_field();
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Вход</title>
</head>
<body>
  <h1>Вход</h1>

  <?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="post" action="/auth-system/public/login.php" autocomplete="off">
    <?= $csrfInput ?>
    <label>
      Логин:<br>
      <input type="text" name="login" required>
    </label><br><br>

    <label>
      Пароль:<br>
      <input type="password" name="pass" required>
    </label><br><br>

    <label>
      <input type="checkbox" name="remember"> Запомнить меня
    </label><br><br>

    <button type="submit">Войти</button>
  </form>

  <p><a href="/auth-system/public/index.php">На главную</a></p>

  <form method="get" action="/auth-system/public/oauth_vk_start.php" style="margin-top:12px">
    <button type="submit">Авторизоваться через VK</button>
  </form>
</body>
</html>

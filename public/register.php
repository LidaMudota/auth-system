<?php
// /auth-system/public/register.php
declare(strict_types=1);

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim((string)($_POST['login'] ?? ''));
    $pass  = (string)($_POST['pass'] ?? '');

    // Валидация
    if (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $login)) {
        $error = 'Некорректный логин (3–100 символов, латиница/цифры . _ -).';
    } elseif (strlen($pass) < 8) {
        $error = 'Пароль должен быть не короче 8 символов.';
    } else {
        $pdo = db();
        // Проверка уникальности логина
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = :login LIMIT 1');
        $stmt->execute([':login' => $login]);
        if ($stmt->fetch()) {
            $error = 'Логин уже занят.';
        } else {
            // Хэширование пароля (bcrypt/argon2 — по конфигурации PHP)
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $ins = $pdo->prepare('INSERT INTO users (login, password_hash, role) VALUES (:l, :h, "user")');
            $ins->execute([':l' => $login, ':h' => $hash]);

            $userId = (int)$pdo->lastInsertId();
            login_user($userId);
            header('Location: /auth-system/public/protected.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Регистрация</title>
</head>
<body>
  <h1>Регистрация</h1>

  <?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="post" action="/auth-system/public/register.php" autocomplete="off">
    <label>
      Логин:<br>
      <input type="text" name="login" required>
    </label><br><br>

    <label>
      Пароль:<br>
      <input type="password" name="pass" required>
    </label><br><br>

    <button type="submit">Зарегистрироваться</button>
  </form>

  <p><a href="/auth-system/public/index.php">На главную</a></p>
</body>
</html>

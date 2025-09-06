<?php
// /auth-system/public/role_demo.php
declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';

$user = require_auth(); // страница видна только авторизованным
$isAdmin = is_admin_role($user);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Демо ролей (ACL-lite)</title>
  <style>
    .button {
      padding: 10px 16px; border: 1px solid #888; border-radius: 6px;
      cursor: pointer; background: #f6f6f6;
    }
    .button.disabled {
      opacity: .5; cursor: not-allowed; filter: grayscale(40%);
    }
  </style>
</head>
<body>
  <h1>Демо ролей</h1>

  <p>Вы вошли как: <strong><?= htmlspecialchars($user['login']) ?></strong> (роль: <?= htmlspecialchars($user['role']) ?>)</p>

  <!-- Орган управления видят все. Для всех ролей, кроме admin — класс disabled -->
  <?php
    $btnClass = 'button' . ($isAdmin ? '' : ' disabled');
    $btnAttr  = $isAdmin ? '' : ' aria-disabled="true"';
  ?>
  <button id="welcomeBtn" class="<?= $btnClass ?>"<?= $btnAttr ?>>Добро пожаловать</button>

  <p>
    <a href="/auth-system/public/index.php">Главная</a> —
    <a href="/auth-system/public/logout.php">Выйти</a>
  </p>

  <script>
    (function(){
      var btn = document.getElementById('welcomeBtn');
      btn.addEventListener('click', function (e) {
        if (btn.classList.contains('disabled')) {
          // Не даём «клик» не-админам
          e.preventDefault();
          // Можно показать подсказку
          alert('Недостаточно прав: только администратор может нажать эту кнопку.');
          return;
        }
        // Админ — пускаем
        alert('Добро пожаловать');
      });
    })();
  </script>
</body>
</html>
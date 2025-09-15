<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/navigation_band.php';
attempt_cookie_login();
$logFile = __DIR__ . '/../storage/log.txt';
$msgFile = __DIR__ . '/../storage/messages.txt';
$status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['text'] ?? '');
    if ($text !== '') {
        if (file_exists($msgFile)) {
            file_put_contents($msgFile, $text . PHP_EOL, FILE_APPEND | LOCK_EX);
            $status = 'Сообщение сохранено';
        } else {
            $line = '[' . date('Y-m-d H:i:s') . "] messages.txt отсутствует" . PHP_EOL;
            file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
            $status = 'Файл сообщений отсутствует';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Сообщения</title>
<link rel="stylesheet" href="assets/voltage.css">
</head>
<body>
<?php navigation_band(); ?>
<div class="header">Сообщения</div>
<div class="box">
<?php if($status) echo '<p>'.$status.'</p>'; ?>
<form method="post">
<textarea name="text" rows="4" cols="40"></textarea>
<button type="submit">Сохранить</button>
</form>
</div>
</body>
</html>
<?php
// /auth-system/public/seed_admin.php
declare(strict_types=1);

require_once __DIR__ . '/../src/db.php';

$pdo = db();
$pdo->beginTransaction();

try {
    // admin / Admin12345!
    $stmt = $pdo->prepare('SELECT id FROM users WHERE login = :l LIMIT 1');
    $stmt->execute([':l' => 'admin']);
    if (!$stmt->fetch()) {
        $hash = password_hash('Admin12345!', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (login, password_hash, role) VALUES (:l,:h,"admin")')
            ->execute([':l' => 'admin', ':h' => $hash]);
    }

    // demo / Demo12345!
    $stmt->execute([':l' => 'demo']);
    if (!$stmt->fetch()) {
        $hash = password_hash('Demo12345!', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (login, password_hash, role) VALUES (:l,:h,"user")')
            ->execute([':l' => 'demo', ':h' => $hash]);
    }

    $pdo->commit();
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK: admin/demo созданы (или уже были)\n";
    echo "Логины: admin / demo\nПароли: Admin12345! / Demo12345!\n";
    echo "Перейдите на /auth-system/public/login.php\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Ошибка сида: " . $e->getMessage();
}
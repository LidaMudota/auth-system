<?php
require_once __DIR__ . '/auth.php';

function navigation_band(): void
{
    $role = user_role();
    $label = $role !== null ? $role : 'неавторизирован';
    $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<div class="nav-line">'
        . '<a class="nav-line__home" href="index.php">Главная</a>'
        . '<span class="nav-line__role">Роль: ' . $safeLabel . '</span>'
        . '</div>';
}

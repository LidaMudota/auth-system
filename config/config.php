<?php
// /auth-system/config/config.php
declare(strict_types=1);

// Базовые пути
const ROOT_PATH = __DIR__ . '/..';
const SRC_PATH  = ROOT_PATH . '/src';
const STORAGE_PATH = ROOT_PATH . '/storage';
const LOGS_PATH = STORAGE_PATH . '/logs';

// БД
const DB_DSN  = 'mysql:host=localhost;dbname=auth_system;charset=utf8mb4';
const DB_USER = 'root';
const DB_PASS = '';

// VK OAuth
const VK_CLIENT_ID     = '54095571';            // твой ID приложения
const VK_CLIENT_SECRET = 'WRLxvjS5XiYFjnorE1aj';// Защищённый ключ
const VK_REDIRECT_URI  = 'http://localhost/auth-system/public/oauth_vk_callback.php';

// Логи
const AUTH_LOG_FILE = LOGS_PATH . '/auth.log';

// Куки «запомнить меня»
const REMEMBER_COOKIE_NAME = 'remember_token';
const REMEMBER_COOKIE_LIFETIME = 60 * 60 * 24 * 14; // 14 дней
const REMEMBER_SAMESITE = 'Lax'; // Lax/Strict/None

// Соль для хэшей (НЕ для password_hash) — тут только пример.
// Для password_hash соль генерируется автоматически.
const APP_STATIC_SALT = 'CHANGE_ME_TO_RANDOM_LONG_STRING';

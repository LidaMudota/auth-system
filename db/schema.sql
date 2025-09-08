-- /auth-system/db/schema.sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255),
    role ENUM('user','vk') NOT NULL,
    remember_token VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

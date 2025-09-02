CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(100) UNIQUE NULL,
    password_hash VARCHAR(255) NULL,
    role ENUM('user','vk') NOT NULL,
    remember_token CHAR(88) NULL,
    remember_token_expires_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY login_unique (login),
    INDEX remember_token_idx (remember_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
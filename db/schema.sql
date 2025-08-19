CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(255) UNIQUE,
    password_hash VARCHAR(255),
    role ENUM('user','vk') NOT NULL,
    vk_user_id BIGINT UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
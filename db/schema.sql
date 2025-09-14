CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  login VARCHAR(100) UNIQUE,
  password_hash VARCHAR(255),
  role ENUM('local','vk') NOT NULL,
  vk_id BIGINT UNIQUE,
  remember_token VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
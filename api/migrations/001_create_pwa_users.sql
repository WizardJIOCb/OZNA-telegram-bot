-- PWA Users table for web authentication
-- Run against database: tg_bot on 82.146.42.213

CREATE TABLE IF NOT EXISTS pwa_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    linked_telegram_id BIGINT NULL UNIQUE,
    access_level TINYINT DEFAULT 0,
    fio VARCHAR(255),
    functional_direction VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_telegram_id (linked_telegram_id),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

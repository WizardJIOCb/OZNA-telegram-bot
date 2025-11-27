-- Database schema for BorisTgBot Telegram bot
-- Database name: tg_bot

-- Create the database
CREATE DATABASE IF NOT EXISTS tg_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE tg_bot;

-- Table for storing user data in JSON format
CREATE TABLE IF NOT EXISTS ozna_data (
    user_id BIGINT PRIMARY KEY,
    data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing user time tracking entries
CREATE TABLE IF NOT EXISTS ozna_tabel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    message TEXT NOT NULL,
    dt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_dt (dt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing access requests
CREATE TABLE IF NOT EXISTS access_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    user_telegram_name VARCHAR(255),
    user_fio VARCHAR(255),
    chat_id BIGINT NOT NULL,
    timestamp INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    processed_by BIGINT NULL,
    processed_at INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data structure in ozna_data table:
-- {
--   "fio": "User Full Name",
--   "functional_direction": "User Functional Direction",
--   "access_level": 0,  -- 0=GUEST, 1=USER, 2=SUPERVISOR, 3=ADMIN, 4=SUPER_ADMIN
--   "is_super_admin": false
-- }

-- Access level constants (for reference):
-- ACCESS_LEVEL_GUEST = 0
-- ACCESS_LEVEL_USER = 1
-- ACCESS_LEVEL_SUPERVISOR = 2
-- ACCESS_LEVEL_ADMIN = 3
-- ACCESS_LEVEL_SUPER_ADMIN = 4
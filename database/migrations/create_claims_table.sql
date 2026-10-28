-- Миграция: создание таблицы рекламаций
-- Дата: 2026-03-07

CREATE TABLE IF NOT EXISTS claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_code VARCHAR(50) NOT NULL COMMENT 'Код проекта',
    reg_number VARCHAR(100) NOT NULL COMMENT 'Регистрационный номер',
    subject VARCHAR(500) NOT NULL COMMENT 'Тема рекламации',
    act_date VARCHAR(50) NOT NULL COMMENT 'Дата акта',
    equipment VARCHAR(500) NOT NULL COMMENT 'Оборудование',
    serial_number VARCHAR(100) NOT NULL COMMENT 'Заводской номер',
    act_author VARCHAR(255) NOT NULL COMMENT 'Автор акта',
    description TEXT NOT NULL COMMENT 'Краткое описание',
    status ENUM('pending_moderation','active','pending_close','closed','rejected') 
        NOT NULL DEFAULT 'active' COMMENT 'Статус рекламации',
    created_by BIGINT NOT NULL COMMENT 'Telegram user_id создателя',
    moderated_by BIGINT NULL COMMENT 'Telegram user_id модератора',
    moderation_comment TEXT NULL COMMENT 'Комментарий модератора',
    moderated_at DATETIME NULL COMMENT 'Дата модерации',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_project_code (project_code),
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_reg_number (reg_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Рекламации по проектам';

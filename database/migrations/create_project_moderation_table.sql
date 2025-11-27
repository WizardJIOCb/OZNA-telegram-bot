-- Таблица для модерации проектов, добавленных пользователями
CREATE TABLE IF NOT EXISTS project_moderation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_code VARCHAR(50) NOT NULL,
    original_name VARCHAR(255) NOT NULL COMMENT 'Оригинальное название при добавлении',
    current_name VARCHAR(255) NOT NULL COMMENT 'Текущее название (может быть изменено администратором)',
    status ENUM('pending', 'active', 'renamed', 'rejected') DEFAULT 'pending' COMMENT 'Статус модерации',
    added_by BIGINT NOT NULL COMMENT 'ID пользователя, добавившего проект',
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата добавления',
    moderated_by BIGINT NULL COMMENT 'ID администратора, проверившего проект',
    moderated_at DATETIME NULL COMMENT 'Дата модерации',
    moderation_comment TEXT NULL COMMENT 'Комментарий администратора',
    is_active BOOLEAN DEFAULT 1 COMMENT 'Активен ли проект',
    
    -- Индексы для оптимизации запросов
    INDEX idx_status (status),
    INDEX idx_added_by (added_by),
    INDEX idx_project_code (project_code),
    INDEX idx_current_name (current_name),
    
    -- Уникальность активных проектов (case-insensitive)
    UNIQUE KEY uk_active_project (current_name, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Модерация проектов, добавленных пользователями';

-- Миграция существующих проектов из файла (выполнить вручную после создания таблицы)
-- INSERT INTO project_moderation (project_code, original_name, current_name, status, added_by, added_at, moderated_by, moderated_at, is_active)
-- VALUES 
--   ('ОИ834', 'ОИ834', 'ОИ834', 'active', 0, NOW(), 0, NOW(), 1),
--   ... (добавить все существующие проекты)
-- ON DUPLICATE KEY UPDATE status = status; -- Игнорировать дубликаты

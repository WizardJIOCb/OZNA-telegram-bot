-- Скрипт для создания системы заметок
-- Запуск: mysql -u root -p ozna_bot < create_notes_system.sql

USE ozna_bot;

-- Создание таблицы заметок
CREATE TABLE IF NOT EXISTS project_notes (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Уникальный идентификатор заметки',
    
    -- Информация о проекте
    project_id INT NOT NULL COMMENT 'ID проекта из таблицы project',
    
    -- Информация об авторе
    user_id BIGINT NOT NULL COMMENT 'Telegram ID автора заметки',
    user_fio VARCHAR(255) NOT NULL COMMENT 'ФИО автора заметки',
    
    -- Контекст создания
    department VARCHAR(50) NOT NULL COMMENT 'Отдел: ШМР/ПНР/Проект',
    context_menu VARCHAR(50) NOT NULL COMMENT 'Меню откуда создана: ШМР/ПНР/Проект',
    
    -- Содержимое заметки
    stage VARCHAR(100) NOT NULL COMMENT 'Этап работ',
    note_text TEXT NOT NULL COMMENT 'Текст заметки (до 4096 символов)',
    
    -- Статус заметки
    status ENUM('active', 'closed') DEFAULT 'active' COMMENT 'Статус заметки',
    
    -- Временные метки
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время создания',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата последнего изменения',
    
    -- Информация о закрытии
    closed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата и время закрытия',
    closed_by_user_id BIGINT NULL DEFAULT NULL COMMENT 'Telegram ID закрывшего',
    closed_by_fio VARCHAR(255) NULL DEFAULT NULL COMMENT 'ФИО закрывшего',
    
    -- Индексы для оптимизации
    INDEX idx_project_status (project_id, status) COMMENT 'Быстрый поиск активных заметок по проекту',
    INDEX idx_project_stage (project_id, stage) COMMENT 'Группировка по этапам',
    INDEX idx_user (user_id) COMMENT 'Поиск заметок автора',
    INDEX idx_created (created_at) COMMENT 'Сортировка по дате создания',
    INDEX idx_status (status) COMMENT 'Фильтрация по статусу'
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица заметок по проектам';

-- Проверка создания таблицы
SELECT 'Таблица project_notes успешно создана!' AS status;

-- Показать структуру таблицы
DESCRIBE project_notes;

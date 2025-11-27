-- Исправление кодировки таблицы project_notes
-- Проблема: иероглифы вместо русского текста
-- Причина: таблица имеет кодировку latin1 вместо utf8mb4
-- Решение: пересоздать таблицу с правильной кодировкой

-- ВНИМАНИЕ! Этот скрипт УДАЛИТ ВСЕ СУЩЕСТВУЮЩИЕ ЗАМЕТКИ!
-- Старые заметки невозможно восстановить из-за двойной кодировки

-- Шаг 1: Удалить старую таблицу
DROP TABLE IF EXISTS project_notes;

-- Шаг 2: Создать новую таблицу с правильной кодировкой UTF-8
CREATE TABLE project_notes (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Уникальный идентификатор заметки',
    
    -- Информация о проекте
    project_id VARCHAR(50) NOT NULL COMMENT 'Код проекта',
    
    -- Информация об авторе
    user_id BIGINT NOT NULL COMMENT 'Telegram ID автора заметки',
    user_fio VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ФИО автора заметки',
    
    -- Контекст создания
    department VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Отдел: ШМР/ПНР/Проект',
    context_menu VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Меню откуда создана: ШМР/ПНР/Проект',
    
    -- Содержимое заметки
    stage VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Этап работ',
    note_text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Текст заметки (до 4096 символов)',
    
    -- Статус заметки
    status ENUM('active', 'closed') DEFAULT 'active' COMMENT 'Статус заметки',
    
    -- Временные метки
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время создания',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата последнего изменения',
    
    -- Информация о закрытии
    closed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата и время закрытия',
    closed_by_user_id BIGINT NULL DEFAULT NULL COMMENT 'Telegram ID закрывшего',
    closed_by_fio VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'ФИО закрывшего',
    
    -- Индексы для оптимизации
    INDEX idx_project_status (project_id, status) COMMENT 'Быстрый поиск активных заметок по проекту',
    INDEX idx_project_stage (project_id, stage) COMMENT 'Группировка по этапам',
    INDEX idx_user (user_id) COMMENT 'Поиск заметок автора',
    INDEX idx_created (created_at) COMMENT 'Сортировка по дате создания',
    INDEX idx_status (status) COMMENT 'Фильтрация по статусу'
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица заметок по проектам';

-- Проверка
SHOW CREATE TABLE project_notes;
SHOW FULL COLUMNS FROM project_notes;

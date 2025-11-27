-- ====================================
-- Таблицы для системы карточек проектов
-- ====================================

USE tg_bot;

-- Справочник заказчиков
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE COMMENT 'Наименование заказчика',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
    
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник заказчиков';

-- Справочник бизнес-направлений (БН)
CREATE TABLE IF NOT EXISTS business_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Наименование БН',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
    
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник бизнес-направлений';

-- Основная таблица карточек проектов
CREATE TABLE IF NOT EXISTS project_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Шифр проекта',
    project_name VARCHAR(255) NOT NULL COMMENT 'Наименование проекта',
    customer_id INT NULL COMMENT 'ID заказчика',
    business_unit_id INT NULL COMMENT 'ID бизнес-направления',
    
    -- Плановые показатели ШМР
    smr_planned_hours DECIMAL(10,2) NULL COMMENT 'Плановая трудоемкость ШМР (часы)',
    
    -- Плановые показатели ПНР
    pnr_planned_hours DECIMAL(10,2) NULL COMMENT 'Плановая трудоемкость ПНР (часы)',
    
    -- Метаданные
    created_by BIGINT NOT NULL COMMENT 'Telegram ID создателя',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления',
    
    -- Внешние ключи
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (business_unit_id) REFERENCES business_units(id) ON DELETE SET NULL,
    
    -- Индексы
    INDEX idx_project_code (project_code),
    INDEX idx_customer (customer_id),
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Карточки проектов';

-- Вехи проектов (плановые показатели)
CREATE TABLE IF NOT EXISTS project_milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_code VARCHAR(50) NOT NULL COMMENT 'Шифр проекта',
    work_type ENUM('ШМР', 'ПНР') NOT NULL COMMENT 'Тип работ',
    milestone_name VARCHAR(255) NOT NULL COMMENT 'Наименование вехи',
    activity_type VARCHAR(100) NULL COMMENT 'Тип активности (связь с ozna_tabel)',
    planned_hours DECIMAL(10,2) NOT NULL COMMENT 'Плановые трудозатраты (часы)',
    sort_order INT DEFAULT 0 COMMENT 'Порядок сортировки',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
    
    -- Внешний ключ
    FOREIGN KEY (project_code) REFERENCES project_cards(project_code) ON DELETE CASCADE,
    
    -- Индексы
    INDEX idx_project (project_code),
    INDEX idx_work_type (work_type),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Вехи проектов с плановыми показателями';

-- Проверка создания таблиц
SELECT 'Таблицы для карточек проектов успешно созданы!' AS status;

<?php
/**
 * Migration script for Project Cards tables
 * Run this file once to create all necessary tables
 */

// Database configuration
$databaseHost = '82.146.42.213';
$databaseUsername = 'WizardJIOCb';
$databasePassword = '7qQ#28051961';
$databaseName = 'tg_bot';

try {
    $pdo = new PDO(
        "mysql:host={$databaseHost};dbname={$databaseName};charset=utf8mb4",
        $databaseUsername,
        $databasePassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Connected to database successfully!\n\n";
    
    // Create customers table
    echo "Creating customers table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE COMMENT 'Наименование заказчика',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Customers table created\n\n";
    
    // Create business_units table
    echo "Creating business_units table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS business_units (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Наименование БН',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Business units table created\n\n";
    
    // Create project_cards table
    echo "Creating project_cards table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS project_cards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Шифр проекта',
            project_name VARCHAR(255) NOT NULL COMMENT 'Наименование проекта',
            customer_id INT NULL COMMENT 'ID заказчика',
            business_unit_id INT NULL COMMENT 'ID бизнес-направления',
            smr_planned_hours DECIMAL(10,2) NULL COMMENT 'Плановая трудоемкость ШМР (часы)',
            pnr_planned_hours DECIMAL(10,2) NULL COMMENT 'Плановая трудоемкость ПНР (часы)',
            created_by BIGINT NOT NULL COMMENT 'Telegram ID создателя',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления',
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
            FOREIGN KEY (business_unit_id) REFERENCES business_units(id) ON DELETE SET NULL,
            INDEX idx_project_code (project_code),
            INDEX idx_customer (customer_id),
            INDEX idx_business_unit (business_unit_id),
            INDEX idx_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Project cards table created\n\n";
    
    // Create project_milestones table
    echo "Creating project_milestones table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS project_milestones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_code VARCHAR(50) NOT NULL COMMENT 'Шифр проекта',
            work_type ENUM('ШМР', 'ПНР') NOT NULL COMMENT 'Тип работ',
            milestone_name VARCHAR(255) NOT NULL COMMENT 'Наименование вехи',
            planned_hours DECIMAL(10,2) NOT NULL COMMENT 'Плановые трудозатраты (часы)',
            order_index INT DEFAULT 0 COMMENT 'Порядок сортировки',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
            FOREIGN KEY (project_code) REFERENCES project_cards(project_code) ON DELETE CASCADE,
            INDEX idx_project (project_code),
            INDEX idx_work_type (work_type),
            INDEX idx_order (order_index)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Project milestones table created\n\n";
    
    echo "========================================\n";
    echo "✓✓✓ ALL TABLES CREATED SUCCESSFULLY! ✓✓✓\n";
    echo "========================================\n\n";
    echo "You can now enable ProjectCardHandler in the bot.\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

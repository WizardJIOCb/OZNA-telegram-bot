<?php
/**
 * Migration: Create milestone progress tracking tables
 * Creates tables for storing milestone completion percentage estimates
 * 
 * Tables:
 * - milestone_progress_estimates: Current estimates (one per project+milestone)
 * - milestone_progress_history: History of all estimates
 */

// Database configuration (hardcoded for migration)
$databaseHost = '82.146.42.213';
$databaseUsername = 'WizardJIOCb';
$databasePassword = '7qQ#28051961';
$databaseName = 'tg_bot';

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host={$databaseHost};dbname={$databaseName};charset=utf8mb4",
        $databaseUsername,
        $databasePassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "Connected to database successfully!\n";
    
    // Create milestone_progress_estimates table (current estimates)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS milestone_progress_estimates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_code VARCHAR(50) NOT NULL COMMENT 'Шифр проекта',
            milestone_name VARCHAR(255) NOT NULL COMMENT 'Название вехи ПНР',
            estimated_percent TINYINT UNSIGNED NOT NULL COMMENT 'Оценка % выполнения (0-100)',
            previous_percent TINYINT UNSIGNED DEFAULT 0 COMMENT 'Предыдущая оценка для валидации',
            estimator_user_id BIGINT NOT NULL COMMENT 'Telegram user_id оценщика',
            estimator_fio VARCHAR(255) NOT NULL COMMENT 'ФИО оценщика',
            plan_hours_at_estimate DECIMAL(10,2) NULL COMMENT 'Плановые часы на момент оценки',
            fact_hours_at_estimate DECIMAL(10,2) NULL COMMENT 'Фактические часы на момент оценки',
            efficiency_at_estimate DECIMAL(5,2) NULL COMMENT 'Рассчитанная эффективность',
            estimated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата/время оценки',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY idx_project_milestone (project_code, milestone_name),
            INDEX idx_project_code (project_code),
            INDEX idx_estimator (estimator_user_id),
            INDEX idx_estimated_at (estimated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        COMMENT='Текущие оценки % выполнения вех ПНР'
    ");
    
    echo "✓ Table milestone_progress_estimates created successfully!\n";
    
    // Create milestone_progress_history table (history of all estimates)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS milestone_progress_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_code VARCHAR(50) NOT NULL COMMENT 'Шифр проекта',
            milestone_name VARCHAR(255) NOT NULL COMMENT 'Название вехи ПНР',
            estimated_percent TINYINT UNSIGNED NOT NULL COMMENT 'Оценка % выполнения',
            estimator_user_id BIGINT NOT NULL COMMENT 'Telegram user_id оценщика',
            estimator_fio VARCHAR(255) NOT NULL COMMENT 'ФИО оценщика',
            plan_hours DECIMAL(10,2) NULL COMMENT 'Плановые часы',
            fact_hours DECIMAL(10,2) NULL COMMENT 'Фактические часы',
            efficiency DECIMAL(5,2) NULL COMMENT 'Эффективность',
            estimated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата/время оценки',
            INDEX idx_project_milestone (project_code, milestone_name),
            INDEX idx_estimated_at (estimated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        COMMENT='История оценок % выполнения вех ПНР'
    ");
    
    echo "✓ Table milestone_progress_history created successfully!\n";
    
    echo "\nMigration completed successfully!\n";
    echo "Tables created:\n";
    echo "  - milestone_progress_estimates (current estimates)\n";
    echo "  - milestone_progress_history (history log)\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

<?php
/**
 * Migration: Add work_type column to milestone progress tables
 * Allows distinguishing PNR vs SMR milestone estimates
 * 
 * Changes:
 * - milestone_progress_estimates: add work_type column, update UNIQUE constraint
 * - milestone_progress_history: add work_type column
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
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "Connected to database successfully!\n";
    
    $pdo->beginTransaction();
    
    // 1. Add work_type column to milestone_progress_estimates
    $pdo->exec("
        ALTER TABLE milestone_progress_estimates 
        ADD COLUMN work_type VARCHAR(10) NOT NULL DEFAULT 'ПНР' 
        COMMENT 'Тип работ: ПНР или ШМР'
        AFTER milestone_name
    ");
    echo "✓ Added work_type column to milestone_progress_estimates\n";
    
    // 2. Drop old UNIQUE constraint and create new one with work_type
    $pdo->exec("
        ALTER TABLE milestone_progress_estimates 
        DROP INDEX idx_project_milestone
    ");
    echo "✓ Dropped old idx_project_milestone constraint\n";
    
    $pdo->exec("
        ALTER TABLE milestone_progress_estimates 
        ADD UNIQUE KEY idx_project_worktype_milestone (project_code, work_type, milestone_name)
    ");
    echo "✓ Created new idx_project_worktype_milestone constraint\n";
    
    // 3. Add work_type column to milestone_progress_history
    $pdo->exec("
        ALTER TABLE milestone_progress_history 
        ADD COLUMN work_type VARCHAR(10) NOT NULL DEFAULT 'ПНР' 
        COMMENT 'Тип работ: ПНР или ШМР'
        AFTER milestone_name
    ");
    echo "✓ Added work_type column to milestone_progress_history\n";
    
    $pdo->commit();
    
    echo "\nMigration completed successfully!\n";
    echo "All existing records preserved with work_type='ПНР'\n";
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

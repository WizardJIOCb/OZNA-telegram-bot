<?php
/**
 * Migration: Add PNR additional work columns to project_cards table
 * Adds columns for mobilization and project activity planning
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
    
    // Add pnr_mobilization column
    $pdo->exec("
        ALTER TABLE project_cards 
        ADD COLUMN pnr_mobilization INT NULL COMMENT 'Мобилизационные мероприятия (дни)' AFTER pnr_planned_hours
    ");
    
    echo "✓ Column pnr_mobilization added successfully!\n";
    
    // Add pnr_project_activity column
    $pdo->exec("
        ALTER TABLE project_cards 
        ADD COLUMN pnr_project_activity INT NULL COMMENT 'Проектная деятельность (дни)' AFTER pnr_mobilization
    ");
    
    echo "✓ Column pnr_project_activity added successfully!\n";
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

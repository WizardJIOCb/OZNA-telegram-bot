<?php
/**
 * Migration: Allow NULL for planned_hours in project_milestones table
 * This is needed to support "not applicable" milestones
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
    
    // Modify planned_hours column to allow NULL
    $pdo->exec("
        ALTER TABLE project_milestones 
        MODIFY COLUMN planned_hours INT NULL COMMENT 'Плановые трудозатраты'
    ");
    
    echo "✓ Column planned_hours modified to allow NULL successfully!\n";
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

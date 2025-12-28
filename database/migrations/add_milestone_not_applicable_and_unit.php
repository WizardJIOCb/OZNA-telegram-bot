<?php
/**
 * Migration: Add is_not_applicable and unit columns to project_milestones table
 * This allows marking milestones as "not applicable" and supports hours/days planning
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
    
    // Check if is_not_applicable column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM project_milestones LIKE 'is_not_applicable'");
    if ($stmt->rowCount() > 0) {
        echo "Column is_not_applicable already exists. Skipping...\n";
    } else {
        // Add is_not_applicable column
        $pdo->exec("
            ALTER TABLE project_milestones 
            ADD COLUMN is_not_applicable BOOLEAN DEFAULT 0 COMMENT 'Работы не предусмотрены проектом' AFTER planned_hours
        ");
        
        echo "✓ Column is_not_applicable added successfully!\n";
    }
    
    // Check if unit column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM project_milestones LIKE 'unit'");
    if ($stmt->rowCount() > 0) {
        echo "Column unit already exists. Skipping...\n";
    } else {
        // Add unit column
        $pdo->exec("
            ALTER TABLE project_milestones 
            ADD COLUMN unit ENUM('days', 'hours') DEFAULT 'days' COMMENT 'Единица измерения трудозатрат' AFTER is_not_applicable
        ");
        
        echo "✓ Column unit added successfully!\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

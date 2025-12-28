<?php
/**
 * Migration: Add name_1c column to project_cards table
 * This column stores the 1C system name for the project
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
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM project_cards LIKE 'name_1c'");
    if ($stmt->rowCount() > 0) {
        echo "Column name_1c already exists. Skipping...\n";
    } else {
        // Add name_1c column
        $pdo->exec("
            ALTER TABLE project_cards 
            ADD COLUMN name_1c VARCHAR(500) NULL COMMENT 'Наименование проекта в 1С для импорта' AFTER business_unit_id,
            ADD INDEX idx_name_1c (name_1c)
        ");
        
        echo "✓ Column name_1c added successfully!\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

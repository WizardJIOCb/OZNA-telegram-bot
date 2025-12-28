<?php
/**
 * Migration to add project_manager_id column to project_cards table
 * Run this file once to add the column
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
    
    echo "========================================\n";
    echo "Adding project_manager_id to project_cards\n";
    echo "========================================\n\n";
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM project_cards LIKE 'project_manager_id'");
    if ($stmt->rowCount() > 0) {
        echo "Column project_manager_id already exists. Skipping...\n";
    } else {
        // Add project_manager_id column
        echo "Adding project_manager_id column...\n";
        $pdo->exec("
            ALTER TABLE project_cards 
            ADD COLUMN project_manager_id INT NULL COMMENT 'ID руководителя проекта' AFTER business_unit_id,
            ADD INDEX idx_project_manager (project_manager_id)
        ");
        echo "✓ Column project_manager_id added successfully!\n";
    }
    
    echo "\n========================================\n";
    echo "✓✓✓ MIGRATION COMPLETED! ✓✓✓\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

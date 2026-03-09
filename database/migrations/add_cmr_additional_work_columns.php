<?php
/**
 * Migration: Add CMR additional work columns to project_cards table
 * Adds columns for СМР (Мобильная бригада) mobilization and project activity planning
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
    
    echo "Connected to database successfully!\n\n";
    
    // Check if columns already exist
    $result = $pdo->query("SHOW COLUMNS FROM project_cards LIKE 'cmr_mobilization'");
    if ($result->rowCount() > 0) {
        echo "Warning: Column cmr_mobilization already exists, skipping...\n";
    } else {
        // Add cmr_mobilization column
        $pdo->exec("
            ALTER TABLE project_cards 
            ADD COLUMN cmr_mobilization INT NULL COMMENT 'СМР: Мобилизационные мероприятия (дни)'
        ");
        echo "Column cmr_mobilization added successfully!\n";
    }
    
    // Check if column already exists
    $result = $pdo->query("SHOW COLUMNS FROM project_cards LIKE 'cmr_project_activity'");
    if ($result->rowCount() > 0) {
        echo "Warning: Column cmr_project_activity already exists, skipping...\n";
    } else {
        // Add cmr_project_activity column
        $pdo->exec("
            ALTER TABLE project_cards 
            ADD COLUMN cmr_project_activity INT NULL COMMENT 'СМР: Доп. работы без плана (дни)'
        ");
        echo "Column cmr_project_activity added successfully!\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

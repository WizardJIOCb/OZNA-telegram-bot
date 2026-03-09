<?php
/**
 * Migration: Add cmr_forecast_enabled column to project_cards table
 * Allows disabling CMR forecast per project (for legacy projects with incorrect SMR data)
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
    
    // Check if column already exists
    $result = $pdo->query("SHOW COLUMNS FROM project_cards LIKE 'cmr_forecast_enabled'");
    if ($result->rowCount() > 0) {
        echo "Warning: Column cmr_forecast_enabled already exists, skipping...\n";
    } else {
        // Add cmr_forecast_enabled column (default 1 = enabled)
        $pdo->exec("
            ALTER TABLE project_cards 
            ADD COLUMN cmr_forecast_enabled TINYINT(1) DEFAULT 1 COMMENT 'Включён ли прогноз СМР на основе ШМР'
        ");
        echo "Column cmr_forecast_enabled added successfully!\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

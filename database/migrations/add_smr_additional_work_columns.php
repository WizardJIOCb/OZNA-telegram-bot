<?php
/**
 * Migration: Add SMR additional work columns to project_cards table
 * Adds columns for ШМР mobilization and project activity planning
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
    $result = $pdo->query("SHOW COLUMNS FROM project_cards LIKE 'smr_mobilization'");
    if ($result->rowCount() > 0) {
        echo "⚠️  Column smr_mobilization already exists, skipping...\n";
    } else {
        // Add smr_mobilization column
        $pdo->exec("
            ALTER TABLE project_cards 
            ADD COLUMN smr_mobilization INT NULL COMMENT 'ШМР: Мобилизационные мероприятия (дни)' AFTER smr_planned_hours
        ");
        echo "✓ Column smr_mobilization added successfully!\n";
    }
    
    // Check if column already exists
    $result = $pdo->query("SHOW COLUMNS FROM project_cards LIKE 'smr_project_activity'");
    if ($result->rowCount() > 0) {
        echo "⚠️  Column smr_project_activity already exists, skipping...\n";
    } else {
        // Add smr_project_activity column
        $pdo->exec("
            ALTER TABLE project_cards 
            ADD COLUMN smr_project_activity INT NULL COMMENT 'ШМР: Проектная деятельность (дни)' AFTER smr_mobilization
        ");
        echo "✓ Column smr_project_activity added successfully!\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

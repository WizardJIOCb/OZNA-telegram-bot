<?php
/**
 * Migration: Add smr_master_duties column to project_cards table
 * This replaces smr_project_activity column for ШМР
 */

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
    
    echo "Checking if smr_master_duties column exists...\n";
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM project_cards LIKE 'smr_master_duties'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "✓ Column smr_master_duties already exists!\n";
    } else {
        // Add smr_master_duties column after smr_mobilization
        $pdo->exec("
            ALTER TABLE project_cards 
            ADD COLUMN smr_master_duties INT NULL COMMENT 'ШМР: Исполнение обязанностей мастера (дни)' AFTER smr_mobilization
        ");
        echo "✓ Column smr_master_duties added successfully!\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

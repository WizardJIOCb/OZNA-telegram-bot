<?php
/**
 * Run migration to fix project_moderation table user ID columns
 * 
 * This script fixes the added_by and moderated_by columns to use BIGINT
 * instead of INT to support large Telegram user IDs
 */

require_once __DIR__ . '/set.php';
require_once __DIR__ . '/Custome/DB.php';

echo "Starting migration: Fix project_moderation user ID columns\n";
echo "==========================================================\n\n";

try {
    // Execute ALTER TABLE statements directly
    echo "Step 1: Modifying added_by column to BIGINT...\n";
    $sql1 = "ALTER TABLE project_moderation MODIFY COLUMN added_by BIGINT NOT NULL COMMENT 'ID пользователя, добавившего проект'";
    $result1 = pDB::insert($sql1);
    echo "✓ added_by column updated successfully\n\n";
    
    echo "Step 2: Modifying moderated_by column to BIGINT...\n";
    $sql2 = "ALTER TABLE project_moderation MODIFY COLUMN moderated_by BIGINT NULL COMMENT 'ID администратора, проверившего проект'";
    $result2 = pDB::insert($sql2);
    echo "✓ moderated_by column updated successfully\n\n";
    
    echo "==========================================================\n";
    echo "Migration completed successfully!\n\n";
    
    // Verify the changes
    echo "Verifying changes...\n";
    $tableInfo = pDB::select("DESCRIBE project_moderation");
    
    echo "\nTable structure (user ID columns):\n";
    echo str_pad("Field", 25) . str_pad("Type", 20) . str_pad("Null", 10) . "Key\n";
    echo str_repeat("-", 70) . "\n";
    
    foreach ($tableInfo as $row) {
        if (in_array($row['Field'], ['added_by', 'moderated_by'])) {
            echo str_pad($row['Field'], 25) . 
                 str_pad($row['Type'], 20) . 
                 str_pad($row['Null'], 10) . 
                 $row['Key'] . "\n";
        }
    }
    echo "\n";
    
    echo "✓ Migration verified successfully!\n";
    echo "You can now test adding projects again.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

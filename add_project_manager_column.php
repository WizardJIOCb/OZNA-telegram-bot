<?php
// Add project_manager column to project_cards table

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

try {
    // Check if column already exists
    $result = $conn->query("SHOW COLUMNS FROM project_cards LIKE 'project_manager'");
    
    if ($result->num_rows > 0) {
        echo "Column 'project_manager' already exists\n";
    } else {
        // Add column
        $sql = "ALTER TABLE project_cards ADD COLUMN project_manager VARCHAR(255) NULL COMMENT 'Руководитель проекта' AFTER project_name";
        
        if ($conn->query($sql)) {
            echo "✅ Column 'project_manager' added successfully!\n";
        } else {
            echo "❌ Error adding column: " . $conn->error . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

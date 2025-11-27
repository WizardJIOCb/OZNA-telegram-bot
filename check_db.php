<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    echo "Checking database connection...\n";
    $db = \Longman\TelegramBot\DB::getPdo();
    echo "Database connection successful!\n\n";
    
    echo "Checking conversation table...\n";
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM conversation');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total conversation records: " . $result['count'] . "\n\n";
    
    echo "Checking conversation records for user 4225504...\n";
    $stmt = $db->prepare('SELECT * FROM conversation WHERE user_id = 4225504 AND chat_id = 4225504 LIMIT 5');
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "No conversation records found for user 4225504\n";
    } else {
        echo "Found " . count($results) . " conversation records:\n";
        foreach ($results as $row) {
            echo "  ID: " . $row['id'] . "\n";
            echo "  Command: " . $row['command'] . "\n";
            echo "  Notes: " . $row['notes'] . "\n";
            echo "  Status: " . $row['status'] . "\n";
            echo "  Created: " . $row['created_at'] . "\n";
            echo "  Updated: " . $row['updated_at'] . "\n\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
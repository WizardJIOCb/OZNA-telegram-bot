<?php

require_once 'CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

try {
    $db = new DatabaseService();
    $result = $db->createAccessRequestsTable();
    
    if ($result) {
        echo "Access requests table created successfully\n";
    } else {
        echo "Failed to create access requests table\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
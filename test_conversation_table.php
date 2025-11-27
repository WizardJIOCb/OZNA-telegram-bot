<?php
/**
 * Test to check if the conversation table exists and is accessible
 */

echo "=== CONVERSATION TABLE TEST ===\n\n";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    // Get database connection
    echo "1. Getting database connection...\n";
    $db = \Longman\TelegramBot\DB::getPdo();
    
    if ($db) {
        echo "   ✅ Database connection successful\n\n";
    } else {
        echo "   ❌ Database connection failed\n\n";
        exit(1);
    }
    
    // Check if conversation table exists
    echo "2. Checking if conversation table exists...\n";
    
    // First, get the table prefix
    $tablePrefix = \Longman\TelegramBot\DB::$table_prefix ?? '';
    echo "   Table prefix: '{$tablePrefix}'\n";
    
    $conversationTable = $tablePrefix . 'conversation';
    echo "   Conversation table name: '{$conversationTable}'\n";
    
    // Check if table exists
    $stmt = $db->prepare("SHOW TABLES LIKE :table_name");
    $stmt->bindParam(':table_name', $conversationTable);
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "   ✅ Conversation table exists\n\n";
    } else {
        echo "   ❌ Conversation table does not exist\n\n";
        exit(1);
    }
    
    // Check table structure
    echo "3. Checking conversation table structure...\n";
    $stmt = $db->prepare("DESCRIBE `{$conversationTable}`");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Table columns:\n";
    foreach ($columns as $column) {
        echo "     - {$column['Field']} ({$column['Type']})\n";
    }
    echo "\n";
    
    // Try to insert a test record directly
    echo "4. Testing direct insert into conversation table...\n";
    
    $userId = 4225504;
    $chatId = 4225504;
    $command = 'test_direct_insert_' . time();
    $notes = json_encode(['test_data' => 'Direct insert test', 'timestamp' => time()], JSON_UNESCAPED_UNICODE);
    $date = date('Y-m-d H:i:s');
    
    $stmt = $db->prepare("INSERT INTO `{$conversationTable}` 
        (`status`, `user_id`, `chat_id`, `command`, `notes`, `created_at`, `updated_at`) 
        VALUES 
        (:status, :user_id, :chat_id, :command, :notes, :created_at, :updated_at)");
    
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':chat_id', $chatId);
    $stmt->bindParam(':command', $command);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':created_at', $date);
    $stmt->bindParam(':updated_at', $date);
    
    $status = 'active';
    
    $insertResult = $stmt->execute();
    
    if ($insertResult) {
        $insertId = $db->lastInsertId();
        echo "   ✅ Direct insert successful, insert ID: {$insertId}\n\n";
        
        // Try to update the record
        echo "5. Testing direct update of conversation record...\n";
        
        $newNotes = json_encode(['test_data' => 'Direct update test', 'timestamp' => time(), 'updated' => true], JSON_UNESCAPED_UNICODE);
        $updateDate = date('Y-m-d H:i:s');
        
        $stmt = $db->prepare("UPDATE `{$conversationTable}` 
            SET `notes` = :notes, `updated_at` = :updated_at 
            WHERE `id` = :id");
        
        $stmt->bindParam(':notes', $newNotes);
        $stmt->bindParam(':updated_at', $updateDate);
        $stmt->bindParam(':id', $insertId);
        
        $updateResult = $stmt->execute();
        
        if ($updateResult) {
            echo "   ✅ Direct update successful\n\n";
        } else {
            echo "   ❌ Direct update failed\n\n";
        }
        
        // Try to read the record back
        echo "6. Testing direct read of conversation record...\n";
        
        $stmt = $db->prepare("SELECT * FROM `{$conversationTable}` WHERE `id` = :id");
        $stmt->bindParam(':id', $insertId);
        $stmt->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($record) {
            echo "   ✅ Direct read successful\n";
            echo "   Record data:\n";
            foreach ($record as $key => $value) {
                echo "     - {$key}: {$value}\n";
            }
            echo "\n";
        } else {
            echo "   ❌ Direct read failed\n\n";
        }
        
        // Clean up - delete the test record
        echo "7. Cleaning up test record...\n";
        $stmt = $db->prepare("DELETE FROM `{$conversationTable}` WHERE `id` = :id");
        $stmt->bindParam(':id', $insertId);
        $stmt->execute();
        echo "   ✅ Test record cleaned up\n\n";
        
    } else {
        echo "   ❌ Direct insert failed\n\n";
    }
    
    echo "=== TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}
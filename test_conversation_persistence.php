<?php
/**
 * Simplified test to check if conversation persistence works at all
 */

echo "=== CHECKING CONVERSATION DATABASE PERSISTENCE ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$userId = 4225504;
$chatId = 4225504;

echo "1. Testing basic conversation functionality...\n";

try {
    // Test with a simple conversation command that should work
    $testConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'test');
    
    echo "   Created test conversation\n";
    echo "   Initial notes: " . json_encode($testConversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
    
    // Set some test data
    $testConversation->notes = ['test' => 'data', 'timestamp' => time()];
    
    echo "   Set test notes: " . json_encode($testConversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
    
    // Try to update
    $updateResult = $testConversation->update();
    echo "   Update result: " . ($updateResult ? "Success" : "Failed") . "\n";
    
    // Create new instance to test persistence
    $checkConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'test');
    echo "   New instance notes: " . json_encode($checkConversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
    
    if ($checkConversation->notes === null) {
        echo "   ❌ Conversation data not persisted\n";
    } else {
        echo "   ✓ Conversation data persisted correctly\n";
    }
    
    // Cleanup
    $testConversation->stop();
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Checking database configuration...\n";

// Check if database is configured for conversations
try {
    // Check if the database connection exists
    $telegram = new \Longman\TelegramBot\Telegram('dummy_token', 'dummy_username');
    
    // Check if database is enabled - simplified check
    echo "   Checking if DB class is available: " . (class_exists('\\Longman\\TelegramBot\\DB') ? "Yes" : "No") . "\n";
    
} catch (Exception $e) {
    echo "   Database check error: " . $e->getMessage() . "\n";
}

echo "\n3. Checking conversation table existence...\n";

try {
    // Try to check if conversation table exists
    $pdo = \Longman\TelegramBot\DB::getPdo();
    
    if ($pdo === null) {
        echo "   ❌ Database PDO connection is null\n";
        echo "   This means database is not properly configured\n";
    } else {
        echo "   ✓ Database PDO connection exists\n";
        
        // Try to query conversation table
        $stmt = $pdo->query("SHOW TABLES LIKE 'conversation'");
        $tableExists = $stmt->rowCount() > 0;
        
        echo "   Conversation table exists: " . ($tableExists ? "✓ Yes" : "❌ No") . "\n";
        
        if ($tableExists) {
            // Check table structure
            $stmt = $pdo->query("DESCRIBE conversation");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "   Table columns: " . implode(', ', $columns) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ Database table check error: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSIS ===\n";
echo "If conversation data is not persisting, the issue is:\n";
echo "• Database not configured for Telegram bot\n";
echo "• Conversation table doesn't exist\n";
echo "• Database permissions issue\n";
echo "• Bot not properly initialized with database\n\n";
echo "This would explain why manual input gets no feedback!\n";
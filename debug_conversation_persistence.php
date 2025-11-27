<?php
/**
 * Debug conversation persistence and detection
 */

echo "=== DEBUGGING CONVERSATION PERSISTENCE ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$userId = 4225504;
$chatId = 4225504;
$projectCode = 'ОИ789';

echo "1. Creating conversation and checking immediately...\n";

try {
    // Create conversation
    $conversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_group_input');
    
    echo "   Before setting notes:\n";
    echo "   Notes: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
    
    $conversation->notes = [
        'state' => 'waiting_group_id',
        'project_code' => $projectCode,
        'step' => 'group_id_input'
    ];
    
    echo "   After setting notes (before update):\n";
    echo "   Notes: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
    
    $conversation->update();
    
    echo "   After update:\n";
    echo "   Notes: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Error creating conversation: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Reading conversation in new instance (simulating GenericmessageCommand)...\n";

try {
    // Create new instance as GenericmessageCommand would
    $checkConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_group_input');
    
    echo "   New instance notes: " . json_encode($checkConversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
    
    $hasState = isset($checkConversation->notes['state']);
    $stateValue = $checkConversation->notes['state'] ?? 'NOT SET';
    $isWaiting = $hasState && $checkConversation->notes['state'] === 'waiting_group_id';
    
    echo "   Has state: " . ($hasState ? "✓ Yes" : "❌ No") . "\n";
    echo "   State value: {$stateValue}\n";
    echo "   Is waiting for group ID: " . ($isWaiting ? "✓ Yes" : "❌ No") . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Error reading conversation: " . $e->getMessage() . "\n";
}

echo "\n3. Testing with debug conversation class...\n";

// Let's try to understand what's happening internally
class DebugConversation extends \Longman\TelegramBot\Conversation {
    public function __construct($user_id, $chat_id, $command = null) {
        echo "   Creating conversation: user_id={$user_id}, chat_id={$chat_id}, command={$command}\n";
        parent::__construct($user_id, $chat_id, $command);
        echo "   Initial notes: " . json_encode($this->notes, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    public function update(): bool {
        echo "   Updating conversation with notes: " . json_encode($this->notes, JSON_UNESCAPED_UNICODE) . "\n";
        return parent::update();
    }
}

try {
    echo "   Creating debug conversation...\n";
    $debugConversation = new DebugConversation($userId, $chatId, 'manual_group_input');
    
    $debugConversation->notes = [
        'state' => 'waiting_group_id',
        'project_code' => $projectCode,
        'step' => 'group_id_input'
    ];
    
    $debugConversation->update();
    
    echo "   Reading back with new debug instance...\n";
    $debugCheck = new DebugConversation($userId, $chatId, 'manual_group_input');
    
} catch (Exception $e) {
    echo "   ❌ Debug conversation error: " . $e->getMessage() . "\n";
}

echo "\n4. Checking database directly...\n";

try {
    // Check if conversation is stored in database
    $db = \Longman\TelegramBot\DB::getPdo();
    
    $query = "SELECT * FROM conversation WHERE user_id = :user_id AND chat_id = :chat_id AND command = :command";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':chat_id', $chatId, PDO::PARAM_INT);
    $command = 'manual_group_input';
    $stmt->bindParam(':command', $command, PDO::PARAM_STR);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "   ✓ Found conversation in database:\n";
        echo "   ID: {$result['id']}\n";
        echo "   Status: {$result['status']}\n";
        echo "   Notes: {$result['notes']}\n";
        echo "   Created: {$result['created_at']}\n";
        echo "   Updated: {$result['updated_at']}\n";
    } else {
        echo "   ❌ No conversation found in database\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Database check error: " . $e->getMessage() . "\n";
}

echo "\n5. Cleanup...\n";
try {
    $conversation->stop();
    if (isset($debugConversation)) {
        $debugConversation->stop();
    }
    echo "   ✓ Conversations cleaned up\n";
} catch (Exception $e) {
    echo "   ⚠️ Cleanup warning: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
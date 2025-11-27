<?php
/**
 * Test script to verify the "Прочее" button behavior in project activities
 */

echo "=== TESTING PROJECT ACTIVITY PROCHÉE BUTTON ===\n\n";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

// Test user and project data
$userId = 4225504; // Test user ID
$chatId = 4225504; // Test chat ID
$projectCode = 'ОИ774'; // Test project code from user's example
$customText = 'Ппппооыкрув'; // Test custom text from user's example

echo "1. Testing project activity Prochee flow...\n";
echo "   User ID: {$userId}\n";
echo "   Chat ID: {$chatId}\n";
echo "   Project Code: {$projectCode}\n";
echo "   Custom Text: {$customText}\n\n";

// Simulate the conversation flow
echo "2. Simulating conversation flow...\n";

try {
    // Create conversation for project activity Prochee
    // Use the same command pattern as in the actual code
    $conversation = new \Longman\TelegramBot\Conversation($userId, $chatId, '/callback');
    
    // Set up initial conversation state for project activity Prochee
    $conversation->notes = [
        'project_code' => $projectCode,
        'tabel_data' => '30.09.2025',
        'prochee_activity_type' => 'Прочее_ПроектнаяДеятельность',
        'prochee_source_category' => 'cat_ТабПроектнаяДеят_Проч',
        'awaiting_custom_prochee_input' => true
    ];
    
    echo "   Initial conversation notes set\n";
    echo "   Notes: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Update conversation
    $result = $conversation->update();
    echo "   Conversation update result: " . ($result ? "SUCCESS" : "FAILED") . "\n\n";
    
    // Simulate custom input handling
    echo "3. Simulating custom input handling...\n";
    
    // Process the custom input
    $conversation->notes['prochee_custom_text'] = $customText;
    $conversation->notes['awaiting_custom_prochee_input'] = false;
    
    echo "   Custom text stored: {$customText}\n";
    echo "   Notes after custom input: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Update conversation again
    $result = $conversation->update();
    echo "   Conversation update result after custom input: " . ($result ? "SUCCESS" : "FAILED") . "\n\n";
    
    // Verify storage by creating new conversation instance
    echo "4. Verifying storage...\n";
    $verifyConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, '/callback');
    echo "   Verified notes: " . json_encode($verifyConversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
    
    $storedCustomText = $verifyConversation->notes['prochee_custom_text'] ?? 'NOT_FOUND';
    echo "   Stored custom text: {$storedCustomText}\n";
    
    if ($storedCustomText === $customText) {
        echo "   ✅ Custom text correctly stored\n\n";
    } else {
        echo "   ❌ Custom text not correctly stored!\n";
        echo "   Expected: {$customText}\n";
        echo "   Got: {$storedCustomText}\n\n";
    }
    
    // Simulate confirmation handling
    echo "5. Simulating confirmation handling...\n";
    
    // Get user data
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $userData = $databaseService->getUserData($userId);
    $fio = $userData['fio'] ?? 'Без ФИО';
    
    echo "   User FIO: {$fio}\n";
    
    // Create TabelEntry instance
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);
    
    // Test createTabelMessage directly
    echo "6. Testing createTabelMessage directly...\n";
    $date = '30.09.2025';
    $activityType = 'Прочее_ПроектнаяДеятельность';
    
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $customText);
    echo "   Generated message: {$message}\n\n";
    
    // Check if custom text is in the message
    if (strpos($message, $customText) !== false) {
        echo "   ✅ Custom text found in message\n";
    } else {
        echo "   ❌ Custom text NOT found in message!\n";
        echo "   Looking for: {$customText}\n";
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}
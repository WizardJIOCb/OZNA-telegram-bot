<?php
// Deep comparison test between working "Прочее" equipment button and custom idle comment

echo "=== DEEP COMPARISON TEST ===\n";
echo "Comparing working 'Прочее' equipment vs custom idle comment flows\n\n";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Entities\Chat;
use CustomCommands\Services\ConversationService;
use CustomCommands\Services\MessageService;
use CustomCommands\Handlers\TabelHandler;
use CustomCommands\GenericmessageCommand;

// Initialize services
$conversationService = new ConversationService();
$messageService = new MessageService();
$tabelHandler = new TabelHandler($conversationService, $messageService);

$chatId = 123456789; // Test chat ID
$userId = 987654321; // Test user ID

echo "1. TESTING WORKING 'ПРОЧЕЕ' EQUIPMENT BUTTON\n";
echo "============================================\n";

// Step 1: Create mock CallbackQuery for "Прочее" equipment
$userData = ['id' => $userId, 'is_bot' => false, 'first_name' => 'Test', 'username' => 'testuser'];
$user = new User($userData);

$chatData = ['id' => $chatId, 'type' => 'private'];
$chat = new Chat($chatData);

$messageData = [
    'message_id' => 1,
    'from' => $userData,
    'date' => time(),
    'chat' => $chatData,
    'text' => 'Test message'
];
$message = new Message($messageData, 'testbot');

// Equipment "Прочее" callback data
$equipmentProcheeData = "cat_ТабЭлОб_Проч";

$callbackQueryData = [
    'id' => 'test_callback_1',
    'from' => $userData,
    'data' => $equipmentProcheeData,
    'message' => $messageData
];

$equipmentProcheeQuery = new CallbackQuery($callbackQueryData, 'testbot');

echo "Step 1a: Testing equipment 'Прочее' button callback: $equipmentProcheeData\n";

// Get or create conversation
$conversation = $conversationService->getConversation($chatId, $userId);
echo "Conversation state before: " . json_encode($conversation->notes) . "\n";

// Test the equipment "Прочее" button
$result1 = $tabelHandler->handle($equipmentProcheeQuery);
echo "Equipment 'Прочее' result: " . ($result1 ? "SUCCESS" : "FAILED") . "\n";

// Check conversation state after
$conversation = $conversationService->getConversation($chatId, $userId);
echo "Conversation state after equipment 'Прочее': " . json_encode($conversation->notes) . "\n\n";

// Step 2: Simulate user text input for equipment "Прочее"
echo "Step 1b: Simulating user text input for equipment 'Прочее'\n";
$customText = "Тестовое оборудование прочее";

// Create GenericmessageCommand to handle text
$genericCommand = new GenericmessageCommand($conversationService, $messageService);

// Create mock message with custom text
$textMessageData = [
    'message_id' => 2,
    'from' => $userData,
    'date' => time(),
    'chat' => $chatData,
    'text' => $customText
];
$textMessage = new Message($textMessageData, 'testbot');

echo "Processing custom text: '$customText'\n";
$textResult1 = $genericCommand->execute($textMessage, []);
echo "Text processing result: " . ($textResult1 ? "SUCCESS" : "FAILED") . "\n";

// Check final conversation state
$conversation = $conversationService->getConversation($chatId, $userId);
echo "Final conversation state after text: " . json_encode($conversation->notes) . "\n\n";

echo "2. TESTING CUSTOM IDLE COMMENT FUNCTIONALITY\n";
echo "===========================================\n";

// Reset conversation for idle test
$conversation->notes = [];
$conversation->update();

// Step 1: Navigate to idle custom comment
echo "Step 2a: Navigating to idle custom comment\n";

// First, select a main idle category (e.g., "Оборудование")
$idleMainData = "idle_menu_Оборудование";
$callbackQueryData['data'] = $idleMainData;
$callbackQueryData['id'] = 'test_callback_2';
$idleMainQuery = new CallbackQuery($callbackQueryData, 'testbot');

echo "Testing idle main category: $idleMainData\n";
$result2a = $tabelHandler->handle($idleMainQuery);
echo "Idle main category result: " . ($result2a ? "SUCCESS" : "FAILED") . "\n";

$conversation = $conversationService->getConversation($chatId, $userId);
echo "Conversation after idle main: " . json_encode($conversation->notes) . "\n\n";

// Step 2: Select "Свой комментарий" (custom comment)
$idleCustomData = "idle_subcategory_Оборудование_kom";
$callbackQueryData['data'] = $idleCustomData;
$callbackQueryData['id'] = 'test_callback_3';
$idleCustomQuery = new CallbackQuery($callbackQueryData, 'testbot');

echo "Step 2b: Testing idle custom comment: $idleCustomData\n";
$result2b = $tabelHandler->handle($idleCustomQuery);
echo "Idle custom comment result: " . ($result2b ? "SUCCESS" : "FAILED") . "\n";

$conversation = $conversationService->getConversation($chatId, $userId);
echo "Conversation after idle custom: " . json_encode($conversation->notes) . "\n\n";

// Step 3: Simulate user text input for idle custom comment
echo "Step 2c: Simulating user text input for idle custom comment\n";
$customIdleText = "Тестовая причина простоя";

$textMessageData['text'] = $customIdleText;
$textMessageData['message_id'] = 3;
$idleTextMessage = new Message($textMessageData, 'testbot');

echo "Processing custom idle text: '$customIdleText'\n";
$textResult2 = $genericCommand->execute($idleTextMessage, []);
echo "Idle text processing result: " . ($textResult2 ? "SUCCESS" : "FAILED") . "\n";

$conversation = $conversationService->getConversation($chatId, $userId);
echo "Final conversation state after idle text: " . json_encode($conversation->notes) . "\n\n";

echo "3. DETAILED COMPARISON ANALYSIS\n";
echo "==============================\n";

// Reset and test both flows with detailed logging
echo "Resetting for detailed comparison...\n\n";

// Clear conversation
$conversation->notes = [];
$conversation->update();

echo "=== FLOW A: Equipment 'Прочее' (WORKING) ===\n";

// Equipment Прочее flow
$equipmentProcheeQuery = new CallbackQuery($callbackQueryData, 'testbot');
$callbackQueryData['data'] = $equipmentProcheeData;
$callbackQueryData['id'] = 'test_A';

echo "A1. Clicking equipment 'Прочее' button\n";
$tabelHandler->handle($equipmentProcheeQuery);
$convA1 = $conversationService->getConversation($chatId, $userId);
echo "A1 State: " . json_encode($convA1->notes) . "\n";

echo "A2. Entering custom text\n";
$textMessageData['text'] = "Test equipment text";
$textMessageData['message_id'] = 100;
$textMsgA = new Message($textMessageData, 'testbot');
$genericCommand->execute($textMsgA, []);
$convA2 = $conversationService->getConversation($chatId, $userId);
echo "A2 State: " . json_encode($convA2->notes) . "\n\n";

// Reset for idle test
$conversation->notes = [];
$conversation->update();

echo "=== FLOW B: Idle Custom Comment (TESTING) ===\n";

echo "B1. Clicking idle main category\n";
$callbackQueryData['data'] = "idle_menu_Оборудование";
$callbackQueryData['id'] = 'test_B1';
$idleMainQueryB = new CallbackQuery($callbackQueryData, 'testbot');
$tabelHandler->handle($idleMainQueryB);
$convB1 = $conversationService->getConversation($chatId, $userId);
echo "B1 State: " . json_encode($convB1->notes) . "\n";

echo "B2. Clicking idle 'Свой комментарий'\n";
$callbackQueryData['data'] = "idle_subcategory_Оборудование_kom";
$callbackQueryData['id'] = 'test_B2';
$idleCustomQueryB = new CallbackQuery($callbackQueryData, 'testbot');
$tabelHandler->handle($idleCustomQueryB);
$convB2 = $conversationService->getConversation($chatId, $userId);
echo "B2 State: " . json_encode($convB2->notes) . "\n";

echo "B3. Entering custom idle text\n";
$textMessageData['text'] = "Test idle text";
$textMessageData['message_id'] = 200;
$textMsgB = new Message($textMessageData, 'testbot');
$genericCommand->execute($textMsgB, []);
$convB3 = $conversationService->getConversation($chatId, $userId);
echo "B3 State: " . json_encode($convB3->notes) . "\n\n";

echo "4. KEY DIFFERENCES ANALYSIS\n";
echo "==========================\n";

echo "Comparing final states:\n";
echo "Equipment 'Прочее' final: " . json_encode($convA2->notes) . "\n";
echo "Idle Custom final: " . json_encode($convB3->notes) . "\n\n";

// Check for specific differences
$equipmentHasAwaitingInput = isset($convA2->notes['awaiting_custom_input']);
$idleHasAwaitingInput = isset($convB3->notes['awaiting_custom_input']);

$equipmentActivityType = $convA2->notes['custom_input_activity_type'] ?? 'NONE';
$idleActivityType = $convB3->notes['custom_input_activity_type'] ?? 'NONE';

echo "Equipment has awaiting_custom_input: " . ($equipmentHasAwaitingInput ? "YES" : "NO") . "\n";
echo "Idle has awaiting_custom_input: " . ($idleHasAwaitingInput ? "YES" : "NO") . "\n";
echo "Equipment activity_type: $equipmentActivityType\n";
echo "Idle activity_type: $idleActivityType\n\n";

echo "5. SPECIFIC TESTS\n";
echo "================\n";

// Test specific conditions
echo "Testing GenericmessageCommand detection:\n";

// Reset conversation to idle custom state
$conversation->notes = [
    'awaiting_custom_input' => true,
    'custom_input_activity_type' => 'Простой_Комментарий',
    'idle_custom_category' => 'Оборудование'
];
$conversation->update();

echo "Set conversation to: " . json_encode($conversation->notes) . "\n";

// Test if GenericmessageCommand detects this state
$testMessage = new Message([
    'message_id' => 999,
    'from' => $userData,
    'date' => time(),
    'chat' => $chatData,
    'text' => 'Test detection'
], 'testbot');

echo "Testing detection with text: 'Test detection'\n";
$detectionResult = $genericCommand->execute($testMessage, []);
echo "Detection result: " . ($detectionResult ? "SUCCESS" : "FAILED") . "\n";

$finalConv = $conversationService->getConversation($chatId, $userId);
echo "Final state after detection test: " . json_encode($finalConv->notes) . "\n";

echo "\n=== TEST COMPLETE ===\n";
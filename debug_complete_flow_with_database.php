<?php

// Debug script to test the complete flow including database operations

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== DEBUGGING COMPLETE FLOW WITH DATABASE ===\n\n";

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test data
$userId = 123456789; // Test user ID
$fio = 'Тестовый Пользователь';
$date = '26.09.2025';
$projectCode = 'ОИ838';
$activityType = 'Прочее_ПроектнаяДеятельность';
$customDescription = 'Тестовая запись для проверки отображения';
$chatId = '123456789';

echo "Test Parameters:\n";
echo "- User ID: {$userId}\n";
echo "- FIO: {$fio}\n";
echo "- Date: {$date}\n";
echo "- Project Code: {$projectCode}\n";
echo "- Activity Type: {$activityType}\n";
echo "- Custom Description: {$customDescription}\n";
echo "- Chat ID: {$chatId}\n\n";

echo "1. Testing createTabelMessage method...\n";
$message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $customDescription);
echo "Generated message:\n{$message}\n\n";

if (strpos($message, $customDescription) !== false) {
    echo "✅ SUCCESS: Custom description is included in the message\n\n";
} else {
    echo "❌ FAILURE: Custom description is missing from the message\n\n";
}

echo "2. Testing addEntry method (this simulates the complete flow)...\n";
echo "This will call:\n";
echo "- createTabelMessage (already tested above)\n";
echo "- databaseService->addUserTabel\n";
echo "- messageService->sendToHistoryChatByFunctionalDirection\n\n";

// Mock the database service to avoid actual database operations
class MockDatabaseService extends DatabaseService {
    public function addUserTabel(int $userId, string $message): bool {
        echo "MockDatabaseService: addUserTabel called with:\n";
        echo "- User ID: {$userId}\n";
        echo "- Message: {$message}\n\n";
        
        // Check if custom description is in the message
        global $customDescription;
        if (strpos($message, $customDescription) !== false) {
            echo "✅ SUCCESS: Custom description found in message to be saved to database\n";
        } else {
            echo "❌ FAILURE: Custom description missing from message to be saved to database\n";
        }
        
        // Return true to simulate successful database operation
        return true;
    }
    
    public function getUserData(int $userId): array {
        // Return mock user data
        return [
            'fio' => 'Тестовый Пользователь',
            'functional_direction' => 'Мобильная бригада'
        ];
    }
}

// Mock the message service to avoid actual message sending
class MockMessageService extends MessageService {
    public function sendToHistoryChatByFunctionalDirection(string $message, string $functionalDirection): \Longman\TelegramBot\Entities\ServerResponse {
        echo "MockMessageService: sendToHistoryChatByFunctionalDirection called with:\n";
        echo "- Message: {$message}\n";
        echo "- Functional Direction: {$functionalDirection}\n\n";
        
        // Check if custom description is in the message
        global $customDescription;
        if (strpos($message, $customDescription) !== false) {
            echo "✅ SUCCESS: Custom description found in message to be sent to history chat\n";
        } else {
            echo "❌ FAILURE: Custom description missing from message to be sent to history chat\n";
        }
        
        // Return a mock ServerResponse
        // Create a simple mock object
        $mockResponse = new class {
            public function isOk() { return true; }
        };
        return $mockResponse;
    }
}

// Create services with mocks
$mockDatabaseService = new MockDatabaseService();
$mockMessageService = new MockMessageService();
$mockTabelEntry = new TabelEntry($mockDatabaseService, $mockMessageService);

echo "3. Calling addEntry with mock services...\n";
$success = $mockTabelEntry->addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $customDescription);

echo "\n4. Result:\n";
if ($success) {
    echo "✅ SUCCESS: addEntry returned true\n";
} else {
    echo "❌ FAILURE: addEntry returned false\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
echo "If all steps show SUCCESS, then the issue is likely elsewhere in the system.\n";
echo "If any step shows FAILURE, that indicates where the problem is occurring.\n";
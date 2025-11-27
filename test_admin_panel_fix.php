<?php
/**
 * Test the admin panel fix for "Заявки на доступ" button
 */

echo "=== Testing Admin Panel Fix ===\n\n";

// Simulate what the admin panel does
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Create a mock DatabaseService that always throws exceptions to force fallback
class MockDatabaseService extends \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService {
    public function getPendingAccessRequests(): array {
        throw new Exception("Database not available - forcing fallback to file");
    }
    
    public function getUserAccessLevel(int $userId): int {
        // Return admin level for testing
        return 3;
    }
}

// Create a simple mock MessageService
class SimpleMockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
        // Just return a successful response
        return new \Longman\TelegramBot\Entities\ServerResponse([
            'ok' => true,
            'result' => true
        ]);
    }
}

// Use reflection to create AdminService with our mock database
$reflection = new ReflectionClass(AdminService::class);
$adminService = $reflection->newInstanceWithoutConstructor();

// Set the message service
$messageService = new SimpleMockMessageService();
$reflectionProperty = $reflection->getProperty('messageService');
$reflectionProperty->setAccessible(true);
$reflectionProperty->setValue($adminService, $messageService);

// Set the database service to our mock
$reflectionProperty = $reflection->getProperty('databaseService');
$reflectionProperty->setAccessible(true);
$reflectionProperty->setValue($adminService, new MockDatabaseService());

// Set the access requests file path to the correct one
$reflectionProperty = $reflection->getProperty('accessRequestsFile');
$reflectionProperty->setAccessible(true);
$reflectionProperty->setValue($adminService, __DIR__ . '/data/access_requests.json');

// Test getting all access requests (should fallback to file)
echo "Getting all access requests (simulating admin panel)...\n";
$accessRequests = $adminService->getAllAccessRequests();
echo "Total requests found: " . count($accessRequests) . "\n";

if (!empty($accessRequests)) {
    echo "SUCCESS: The admin panel should now be able to display access requests!\n";
    echo "The 'Заявки на доступ' button should work correctly.\n\n";
    
    echo "First few requests:\n";
    $count = 0;
    foreach ($accessRequests as $request) {
        if ($count >= 3) break;
        echo "  - {$request['user_name']} (ID: {$request['user_id']}) - " . date('Y-m-d H:i:s', $request['timestamp']) . "\n";
        $count++;
    }
} else {
    echo "No access requests found.\n";
}

echo "\nTest completed.\n";
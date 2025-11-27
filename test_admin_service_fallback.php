<?php
/**
 * Test AdminService fallback to file-based access requests
 */

echo "=== Testing AdminService Fallback ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

// Create a mock DatabaseService that always throws exceptions to force fallback
class MockDatabaseService extends DatabaseService {
    public function getPendingAccessRequests(): array {
        throw new Exception("Database not available - forcing fallback to file");
    }
    
    public function addAccessRequest(array $requestData): bool {
        throw new Exception("Database not available - forcing fallback to file");
    }
    
    public function getAccessRequestByUserId(int $userId): ?array {
        throw new Exception("Database not available - forcing fallback to file");
    }
    
    public function approveAccessRequest(int $id, int $processedBy): bool {
        throw new Exception("Database not available - forcing fallback to file");
    }
    
    public function rejectAccessRequest(int $id, int $processedBy): bool {
        throw new Exception("Database not available - forcing fallback to file");
    }
    
    public function clearPendingAccessRequests(): bool {
        throw new Exception("Database not available - forcing fallback to file");
    }
    
    public function getUserAccessLevel(int $userId): int {
        // Return guest level by default
        return 0;
    }
    
    public function updateUserAccessLevel(int $userId, int $newLevel): bool {
        // Always succeed for testing
        return true;
    }
}

// Create a mock MessageService for testing
class MockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
        echo "Would send message to chat ID $chatId:\n";
        echo "Text: " . substr($text, 0, 100) . "...\n";
        if ($keyboard) {
            echo "Keyboard has " . count($keyboard) . " rows\n";
        }
        echo "\n";
        // Return a mock ServerResponse
        return new \Longman\TelegramBot\Entities\ServerResponse([
            'ok' => true,
            'result' => true
        ]);
    }
}

// Create services with mock database
$messageService = new MockMessageService();

// Use reflection to create AdminService with our mock database
$reflection = new ReflectionClass(AdminService::class);
$adminService = $reflection->newInstanceWithoutConstructor();

// Set the message service
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
echo "Testing getAllAccessRequests with fallback...\n";
try {
    $requests = $adminService->getAllAccessRequests();
    echo "Total requests found: " . count($requests) . "\n";
    
    if (!empty($requests)) {
        echo "Displaying first few requests:\n";
        $count = 0;
        foreach ($requests as $request) {
            if ($count >= 3) break; // Show only first 3
            echo "  User ID: " . $request['user_id'] . "\n";
            echo "  User Name: " . $request['user_name'] . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n\n";
            $count++;
        }
        
        if (count($requests) > 3) {
            echo "  ... and " . (count($requests) - 3) . " more requests\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
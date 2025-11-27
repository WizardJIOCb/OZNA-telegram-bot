<?php
/**
 * Direct file test for access requests
 */

echo "=== Direct File Test for Access Requests ===\n\n";

// Test reading the access requests file directly
$accessRequestsFile = __DIR__ . '/data/access_requests.json';

echo "Checking access requests file: {$accessRequestsFile}\n";

if (file_exists($accessRequestsFile)) {
    echo "File exists: YES\n";
    
    $jsonData = file_get_contents($accessRequestsFile);
    echo "File size: " . strlen($jsonData) . " bytes\n";
    
    if ($jsonData !== false) {
        $requests = json_decode($jsonData, true);
        if (is_array($requests)) {
            echo "JSON decoded successfully\n";
            echo "Total requests found: " . count($requests) . "\n";
            
            if (!empty($requests)) {
                echo "\nDisplaying first few requests:\n";
                $count = 0;
                foreach ($requests as $request) {
                    if ($count >= 5) break; // Show only first 5
                    echo "  User ID: " . $request['user_id'] . "\n";
                    echo "  User Name: " . $request['user_name'] . "\n";
                    echo "  Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n\n";
                    $count++;
                }
                
                if (count($requests) > 5) {
                    echo "  ... and " . (count($requests) - 5) . " more requests\n";
                }
            }
        } else {
            echo "Failed to decode JSON\n";
            echo "JSON error: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "Failed to read file content\n";
    }
} else {
    echo "File exists: NO\n";
}

// Test the AdminService fallback mechanism
echo "\n=== Testing AdminService Fallback Mechanism ===\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Create a mock DatabaseService that always throws exceptions
class MockDatabaseService extends \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService {
    public function __construct() {
        // Don't call parent constructor to avoid mysqli error
    }
    
    public function getPendingAccessRequests(): array {
        throw new Exception("Database not available - forcing fallback to file");
    }
    
    public function getUserAccessLevel(int $userId): int {
        // Return admin level for testing (level 4 = super admin)
        if ($userId == 5261465966) {
            return 4;
        }
        return 0; // Guest level
    }
    
    public function updateUserAccessLevel(int $userId, int $newLevel): bool {
        return true;
    }
}

// Create a simple mock MessageService
class SimpleMockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
        echo "Mock sendMessage called:\n";
        echo "  Chat ID: $chatId\n";
        echo "  Text: " . substr($text, 0, 100) . "...\n";
        if ($keyboard) {
            echo "  Keyboard rows: " . count($keyboard) . "\n";
        }
        echo "\n";
        
        // Return a mock ServerResponse
        return new \Longman\TelegramBot\Entities\ServerResponse([
            'ok' => true,
            'result' => true
        ]);
    }
}

// Test with a test user ID that should be an admin
$testAdminUserId = 5261465966; // This should be an admin user ID

echo "\nTesting access level for user ID: {$testAdminUserId}\n";

// Use reflection to create AdminService with our mock database
$messageService = new SimpleMockMessageService();
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

$userAccessLevel = $adminService->getUserAccessLevel($testAdminUserId);
echo "User access level: {$userAccessLevel}\n";
$levelName = $adminService->getAccessLevelName($userAccessLevel);
echo "Level name: {$levelName}\n\n";

// Check if user has admin access
$hasAdminAccess = $userAccessLevel >= 3;
echo "Has admin access (level 3 or higher): " . ($hasAdminAccess ? 'YES' : 'NO') . "\n\n";

// Test getting all access requests
echo "Getting all access requests (should use file fallback)...\n";
try {
    $requests = $adminService->getAllAccessRequests();
    echo "Total requests found: " . count($requests) . "\n";
    
    if (!empty($requests)) {
        echo "Displaying first few requests:\n";
        $count = 0;
        foreach ($requests as $request) {
            if ($count >= 3) break;
            echo "  - {$request['user_name']} (ID: {$request['user_id']}) - " . date('Y-m-d H:i:s', $request['timestamp']) . "\n";
            $count++;
        }
        if (count($requests) > 3) {
            echo "  ... and " . (count($requests) - 3) . " more requests\n";
        }
    } else {
        echo "No access requests found.\n";
    }
} catch (Exception $e) {
    echo "Error getting access requests: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
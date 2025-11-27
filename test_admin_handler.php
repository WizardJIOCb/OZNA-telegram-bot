<?php
/**
 * Test script for AdminHandler functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Admin Handler ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

// Create a simple mock class to simulate missing mysqli extension
class SimpleMockDatabaseService {
    public function getPendingAccessRequests(): array {
        // Read from file instead
        $accessRequestsFile = __DIR__ . '/data/access_requests.json';
        if (file_exists($accessRequestsFile)) {
            $jsonData = file_get_contents($accessRequestsFile);
            if ($jsonData !== false) {
                $requests = json_decode($jsonData, true);
                if (is_array($requests)) {
                    return $requests;
                }
            }
        }
        return [];
    }
    
    public function getAccessRequestByUserId(int $userId): ?array {
        // Read from file instead
        $accessRequestsFile = __DIR__ . '/data/access_requests.json';
        if (file_exists($accessRequestsFile)) {
            $jsonData = file_get_contents($accessRequestsFile);
            if ($jsonData !== false) {
                $requests = json_decode($jsonData, true);
                if (is_array($requests)) {
                    foreach ($requests as $request) {
                        if (isset($request['user_id']) && (int)$request['user_id'] === $userId) {
                            return $request;
                        }
                    }
                }
            }
        }
        return null;
    }
    
    public function getUserAccessLevel(int $userId): int {
        // For testing, assume user is admin
        return 3; // ACCESS_LEVEL_ADMIN
    }
}

// Create a mock AdminService that uses our simple mock database
class MockAdminService extends AdminService {
    private $mockDatabaseService;
    
    public function __construct(MessageService $messageService) {
        parent::__construct($messageService);
        $this->mockDatabaseService = new SimpleMockDatabaseService();
    }
    
    public function getAllAccessRequests(): array {
        return $this->mockDatabaseService->getPendingAccessRequests();
    }
    
    public function getAccessRequestByUserId(int $userId): ?array {
        return $this->mockDatabaseService->getAccessRequestByUserId($userId);
    }
    
    public function getUserAccessLevel(int $userId): int {
        return $this->mockDatabaseService->getUserAccessLevel($userId);
    }
    
    public function getAccessLevelName(int $accessLevel): string {
        return parent::getAccessLevelName($accessLevel);
    }
}

// Create services
$messageService = new MessageService();
$adminService = new MockAdminService($messageService);
$adminHandler = new AdminHandler($adminService, $messageService);

// Test if AdminHandler can handle the callback for "Заявки на доступ"
$callbackData = 'category_Админка_Заявки';
echo "Testing if AdminHandler can handle callback: '{$callbackData}'\n";
$canHandle = $adminHandler->canHandle($callbackData);
echo "Result: " . ($canHandle ? 'YES' : 'NO') . "\n\n";

// Test getting all access requests through AdminService
echo "Testing AdminService->getAllAccessRequests()...\n";
$requests = $adminService->getAllAccessRequests();
echo "Total requests found: " . count($requests) . "\n\n";

echo "\n=== Test Complete ===\n";
<?php
/**
 * Test script for the fixed "Заявки на доступ" functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Fixed Access Requests ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create a mock database service class to simulate the missing mysqli extension
class MockDatabaseService {
    public function getPendingAccessRequests(): array {
        throw new Exception("MySQLi extension not available");
    }
    
    public function getAccessRequestByUserId(int $userId): ?array {
        throw new Exception("MySQLi extension not available");
    }
    
    public function approveAccessRequest(int $id, int $processedBy): bool {
        throw new Exception("MySQLi extension not available");
    }
    
    public function rejectAccessRequest(int $id, int $processedBy): bool {
        throw new Exception("MySQLi extension not available");
    }
    
    public function clearPendingAccessRequests(): bool {
        throw new Exception("MySQLi extension not available");
    }
    
    public function updateUserAccessLevel(int $userId, int $newLevel): bool {
        // This is a mock implementation
        return true;
    }
}

// Create services with mock database
$messageService = new MessageService();

// Temporarily replace the DatabaseService with our mock
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

// Set the access requests file path
$reflectionProperty = $reflection->getProperty('accessRequestsFile');
$reflectionProperty->setAccessible(true);
$reflectionProperty->setValue($adminService, __DIR__ . '/data/access_requests.json');

echo "Testing getAllAccessRequests...\n";
try {
    $requests = $adminService->getAllAccessRequests();
    echo "Total requests found: " . count($requests) . "\n";
    
    if (!empty($requests)) {
        echo "Displaying first request:\n";
        $firstRequest = $requests[0];
        print_r($firstRequest);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
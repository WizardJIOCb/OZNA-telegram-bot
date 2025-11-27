<?php
/**
 * Test script to verify file-based access requests functionality
 */

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

try {
    echo "Testing file-based access requests functionality...\n";
    
    // Create a mock message service
    $messageService = new MessageService();
    
    // Create admin service
    $adminService = new AdminService($messageService);
    
    // Test adding an access request
    echo "Adding test access request...\n";
    $result = $adminService->sendAccessRequest(123456, 'Test User', '123456');
    echo "Add request result: " . ($result ? "success" : "failed") . "\n";
    
    // Test getting access requests
    echo "Getting access requests...\n";
    $requests = $adminService->getAllAccessRequests();
    echo "Found " . count($requests) . " access requests\n";
    
    if (!empty($requests)) {
        // Test approving an access request
        echo "Approving access request...\n";
        $result = $adminService->approveAccessRequest(123456, 'Test User', '123456', 999999);
        echo "Approve request result: " . ($result ? "success" : "failed") . "\n";
        
        // Test getting access requests again
        echo "Getting access requests after approval...\n";
        $requests = $adminService->getAllAccessRequests();
        echo "Found " . count($requests) . " access requests\n";
    }
    
    echo "File-based access requests functionality test completed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
<?php
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

// Create a mock message service
class MockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): ServerResponse {
        echo "=== Mock Message Service ===\n";
        echo "Sending message to chat $chatId:\n";
        echo "$text\n";
        if ($keyboard) {
            echo "With keyboard:\n";
            foreach ($keyboard as $row) {
                foreach ($row as $button) {
                    echo "  [{$button['text']}] -> {$button['callback_data']}\n";
                }
            }
        }
        echo "========================\n\n";
        // Return a mock response
        return new ServerResponse(['ok' => true, 'result' => null]);
    }
}

// Test the complete project deletion flow
$messageService = new MockMessageService();
$adminService = new AdminService($messageService);

echo "Testing complete project deletion flow...\n\n";

// 1. Get current projects
$projects = $adminService->getAllProjects();
echo "Current projects:\n";
foreach ($projects as $project) {
    echo "- $project\n";
}
echo "\n";

// 2. Add a test project if it doesn't exist
$testProject = 'TEST123';
if (!$adminService->projectExists($testProject)) {
    echo "Adding test project '$testProject'...\n";
    $result = $adminService->addProject($testProject);
    echo "Add project result: " . ($result ? "SUCCESS" : "FAILED") . "\n\n";
    
    // Get updated projects
    $projects = $adminService->getAllProjects();
    echo "Updated projects:\n";
    foreach ($projects as $project) {
        echo "- $project\n";
    }
    echo "\n";
}

// 3. Test the delete project keyboard creation
echo "Testing delete project keyboard creation...\n";
$deleteKeyboard = $adminService->createDeleteProjectKeyboard($projects);
echo "Delete keyboard created with " . count($deleteKeyboard) . " rows\n";
foreach ($deleteKeyboard as $rowIndex => $row) {
    echo "Row " . ($rowIndex + 1) . ":\n";
    foreach ($row as $button) {
        echo "  [{$button['text']}] -> {$button['callback_data']}\n";
    }
}
echo "\n";

// 4. Test project deletion
echo "Testing project deletion for '$testProject'...\n";
$deleteResult = $adminService->deleteProject($testProject);
echo "Delete project result: " . ($deleteResult ? "SUCCESS" : "FAILED") . "\n\n";

// 5. Get final projects
$finalProjects = $adminService->getAllProjects();
echo "Final projects:\n";
foreach ($finalProjects as $project) {
    echo "- $project\n";
}
echo "\n";

echo "Test completed.\n";
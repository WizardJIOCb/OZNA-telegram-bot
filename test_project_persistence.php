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
        echo "Would send message to chat $chatId:\n";
        echo $text . "\n";
        if ($keyboard) {
            echo "With keyboard: " . json_encode($keyboard, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "---\n";
        // Return a mock response
        return new ServerResponse(['ok' => true, 'result' => null]);
    }
}

echo "Testing project persistence functionality...\n\n";

// Create AdminService with mock message service
$messageService = new MockMessageService();
$adminService = new AdminService($messageService);

// Get current projects
$projects = $adminService->getAllProjects();
echo "Current projects:\n";
foreach ($projects as $project) {
    echo "- $project\n";
}
echo "\n";

// Try to add a new project
$testProject = 'TEST' . date('His'); // Unique project code with timestamp
echo "Adding new project '$testProject'...\n";
$result = $adminService->addProject($testProject);
echo "Add project result: " . ($result ? "SUCCESS" : "FAILED") . "\n\n";

// Get updated projects
$projects = $adminService->getAllProjects();
echo "Updated projects:\n";
foreach ($projects as $project) {
    echo "- $project\n";
}
echo "\n";

// Check if our test project is in the list
$projectExists = in_array($testProject, $projects);
echo "Test project '$testProject' exists in list: " . ($projectExists ? "YES" : "NO") . "\n\n";

// Create a new AdminService instance to simulate application restart
echo "Simulating application restart...\n";
$adminService2 = new AdminService($messageService);

// Get projects from the new instance
$projects2 = $adminService2->getAllProjects();
echo "Projects after 'restart':\n";
foreach ($projects2 as $project) {
    echo "- $project\n";
}
echo "\n";

// Check if our test project is still in the list
$projectExistsAfterRestart = in_array($testProject, $projects2);
echo "Test project '$testProject' exists after restart: " . ($projectExistsAfterRestart ? "YES" : "NO") . "\n\n";

if ($projectExists && $projectExistsAfterRestart) {
    echo "✅ SUCCESS: Project persistence is working correctly!\n";
    echo "Projects are being saved to and loaded from the file.\n";
} else {
    echo "❌ FAILURE: Project persistence is not working correctly.\n";
    if (!$projectExists) {
        echo "- Project was not added to the list\n";
    }
    if (!$projectExistsAfterRestart) {
        echo "- Project was not persisted after restart\n";
    }
}

echo "\nTest completed.\n";
?>
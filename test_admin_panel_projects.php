<?php

// Test to verify admin panel can see the new projects
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Testing admin panel project list after restart...\n\n";

// Initialize services (simulating a fresh start)
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Simulate admin panel project list display
$projects = $adminService->getAllProjects();

echo "✅ AdminService loaded " . count($projects) . " projects\n\n";

// Check for some of the newly added projects
$testProjects = ['ОИ661', 'ОИ711', 'ОИ811', 'ОИ420-04', 'ИП002'];
$foundProjects = [];
$missingProjects = [];

foreach ($testProjects as $testProject) {
    if (in_array($testProject, $projects)) {
        $foundProjects[] = $testProject;
    } else {
        $missingProjects[] = $testProject;
    }
}

echo "Test results for sample new projects:\n";
echo "✅ Found: " . implode(', ', $foundProjects) . "\n";
if (!empty($missingProjects)) {
    echo "❌ Missing: " . implode(', ', $missingProjects) . "\n";
} else {
    echo "🎉 All test projects found!\n";
}

echo "\nFull project list (first 10):\n";
for ($i = 0; $i < min(10, count($projects)); $i++) {
    echo ($i + 1) . ". " . $projects[$i] . "\n";
}

if (count($projects) > 10) {
    echo "... and " . (count($projects) - 10) . " more projects\n";
}

echo "\n=== Admin Panel Simulation ===\n";
echo "This is what the admin would see when clicking 'Просмотреть все проекты':\n\n";

$text = "📋 <b>Список проектов:</b>\n\n";
if (empty($projects)) {
    $text .= "Проекты не найдены.";
} else {
    foreach ($projects as $project) {
        $text .= "• {$project}\n";
    }
}

echo $text;
echo "\nTest completed!\n";
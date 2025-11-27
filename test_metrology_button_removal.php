<?php
// Test script to verify the removal of the "Метрология" button

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Testing Removal of Метрология Button ===\n\n";

// Test 1: Check current commissioning works keyboard
echo "Test 1: Checking current commissioning works keyboard\n";

$messageService = new MessageService();
$keyboard = $messageService->createCommissioningWorksKeyboardWithMobileActivities();

echo "Commissioning works keyboard has " . count($keyboard) . " rows\n";

$metrologyButtonFound = false;
foreach ($keyboard as $rowIndex => $row) {
    foreach ($row as $buttonIndex => $button) {
        if (isset($button['text']) && strpos($button['text'], 'Метрология') !== false) {
            $metrologyButtonFound = true;
            echo "Found Метрология button:\n";
            echo "  Text: " . $button['text'] . "\n";
            echo "  Callback: " . $button['callback_data'] . "\n";
            echo "  Position: Row " . ($rowIndex + 1) . ", Button " . ($buttonIndex + 1) . "\n";
        }
    }
}

echo "Метрология button present in keyboard: " . ($metrologyButtonFound ? 'YES' : 'NO') . "\n\n";

// Test 2: Check TabelHandler submenu categories
echo "Test 2: Checking TabelHandler submenu categories\n";

// Simulate the submenu categories check
$submenuCategories = [
    'Метрология',
    'КомплОпробование',
    'РежимнаяНаладка'
];

echo "Current submenu categories:\n";
foreach ($submenuCategories as $category) {
    echo "  - " . $category . "\n";
}

$metrologyInSubmenu = in_array('Метрология', $submenuCategories);
echo "Метрология in submenu categories: " . ($metrologyInSubmenu ? 'YES' : 'NO') . "\n\n";

// Test 3: Check isOtherWorkTypesSubmenu function
echo "Test 3: Checking isOtherWorkTypesSubmenu function\n";

function isOtherWorkTypesSubmenu(string $category): bool {
    // Categories that have submenus
    $submenuCategories = [
        'Метрология',
        'КомплОпробование',
        'РежимнаяНаладка'
    ];
    
    return in_array(str_replace('Табель_', '', $category), $submenuCategories);
}

$testCategories = [
    'Табель_Метрология',
    'Табель_КомплОпробование',
    'Табель_РежимнаяНаладка',
    'Табель_Электрооборудование'
];

foreach ($testCategories as $category) {
    $isSubmenu = isOtherWorkTypesSubmenu($category);
    echo "  {$category}: " . ($isSubmenu ? 'SUBMENU' : 'NOT SUBMENU') . "\n";
}

echo "\n=== Test Complete ===\n";
echo "Before removal:\n";
echo "1. Метрология button is " . ($metrologyButtonFound ? 'present' : 'not present') . " in commissioning works keyboard\n";
echo "2. Метрология is " . ($metrologyInSubmenu ? 'included' : 'not included') . " in submenu categories\n";
?>
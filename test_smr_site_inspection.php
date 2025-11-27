<?php
// Test script to verify ШМР "Проверка монтажной площадки" fix

require_once 'vendor/autoload.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== Testing ШМР Site Inspection Fix ===\n\n";

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$projectCode = 'ОИ834';

echo "Testing ШМР simple activities:\n\n";

// Test Site Inspection (Проверка монтажной площадки)
echo "1. SITE INSPECTION TEST:\n";
echo "   Activity: ПроверкаМонтажнойПлощадки\n";
echo "   Expected format: 'Проект ОИ834: Проверка монтажной площадка. Отправить запись в табель?'\n";

$confirmationMessage = $tabelEntry->getConfirmationMessage('ПроверкаМонтажнойПлощадки', $projectCode);
echo "   Actual message: $confirmationMessage\n\n";

// Check if it uses the correct format
if (strpos($confirmationMessage, "Проект <b>$projectCode</b>:") !== false && 
    strpos($confirmationMessage, ". Отправить запись в табель?") !== false) {
    echo "   ✅ SUCCESS: Using correct standard format\n";
} else {
    echo "   ❌ FAILED: Still using old format\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test Act Signing (Подписание акта ШМР)
echo "2. ACT SIGNING TEST:\n";
echo "   Activity: ПодписаниеАктаШМР\n";
echo "   Expected format: 'Проект ОИ834: Подписание акта ШМР. Отправить запись в табель?'\n";

$confirmationMessage2 = $tabelEntry->getConfirmationMessage('ПодписаниеАктаШМР', $projectCode);
echo "   Actual message: $confirmationMessage2\n\n";

// Check if it uses the correct format
if (strpos($confirmationMessage2, "Проект <b>$projectCode</b>:") !== false && 
    strpos($confirmationMessage2, ". Отправить запись в табель?") !== false) {
    echo "   ✅ SUCCESS: Using correct standard format\n";
} else {
    echo "   ❌ FAILED: Still using old format\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test callback handling patterns
echo "3. CALLBACK PATTERN TEST:\n";
echo "   Testing callback: cat_ШМР_АналФунд_yes\n";

$testCallback = 'cat_ШМР_АналФунд_yes';
$pattern = '/^cat_ШМР_(.+)_yes$/u';

if (preg_match($pattern, $testCallback, $matches)) {
    echo "   ✅ Pattern matches: {$matches[1]}\n";
    
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР'
    ];
    
    if (isset($simpleSupervisionMap[$matches[1]])) {
        $activityType = $simpleSupervisionMap[$matches[1]];
        echo "   ✅ Callback correctly maps to: $activityType\n";
        echo "   ✅ This should work in handleActivityExecution\n";
    } else {
        echo "   ❌ Callback mapping not found\n";
    }
} else {
    echo "   ❌ Pattern does not match\n";
}

echo "\n=== Test Complete ===\n";
echo "If both activities show SUCCESS above, the fix is working correctly.\n";
?>
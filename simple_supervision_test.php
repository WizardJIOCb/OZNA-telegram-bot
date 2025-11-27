<?php
/**
 * Simple test to check activity mapping in TabelEntry
 */

echo "🧪 SIMPLE SUPERVISION ACTIVITY MAPPING TEST\n";
echo "==========================================\n\n";

// Read the TabelEntry.php file to check mappings
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

echo "1. CHECKING ACTIVITY TYPE MAPPINGS IN TABELENTRY.PHP\n";
echo "====================================================\n";

$testActivityTypes = [
    'ШМ_РабПоГрафику',
    'ШМ_ПодготовкаКПоездке', 
    'ШМ_Дорога',
    'ШМ_Инструктаж',
    'ШМ_СдачаАвансовогоОтчета'
];

foreach ($testActivityTypes as $activityType) {
    echo "Checking: '{$activityType}'\n";
    
    if (strpos($tabelEntryContent, "'{$activityType}'") !== false) {
        echo "  ✅ Found in activityTypeMappings\n";
        
        // Extract the mapping
        $pattern = "/'{$activityType}' => '([^']+)'/";
        if (preg_match($pattern, $tabelEntryContent, $matches)) {
            echo "  Mapped to: '{$matches[1]}'\n";
        }
    } else {
        echo "  ❌ NOT found in activityTypeMappings\n";
    }
    echo "\n";
}

echo "2. CHECKING SUPERVISION INSTALLATION WORKS ARRAY\n";
echo "===============================================\n";

if (strpos($tabelEntryContent, '$supervisionInstallationWorks') !== false) {
    echo "✅ supervisionInstallationWorks array exists\n";
    
    foreach ($testActivityTypes as $activityType) {
        if (strpos($tabelEntryContent, "'{$activityType}'") !== false && 
            strpos($tabelEntryContent, '$supervisionInstallationWorks') !== false) {
            echo "  ✅ '{$activityType}' should be in array\n";
        }
    }
} else {
    echo "❌ supervisionInstallationWorks array NOT found\n";
}

echo "\n3. CHECKING CREATETABELMESSAGE METHOD STRUCTURE\n";
echo "==============================================\n";

// Check if the method has the supervision installation works section
if (strpos($tabelEntryContent, 'Supervision Installation Works') !== false) {
    echo "✅ Supervision Installation Works section exists\n";
} else {
    echo "❌ Supervision Installation Works section NOT found\n";
}

// Check for the automation format usage
if (strpos($tabelEntryContent, 'sprintf(') !== false && 
    strpos($tabelEntryContent, '✅%s, Проект') !== false) {
    echo "✅ Automation format template exists\n";
} else {
    echo "❌ Automation format template NOT found\n";
}

echo "\n4. EXAMINING ISSUE WITH CALLBACK PROCESSING\n";
echo "==========================================\n";

echo "The issue might be in the callback processing chain:\n";
echo "1. Button click: cat_ШМР_СдачаОтч\n";
echo "2. Confirmation: cat_ШМР_СдачаОтч_yes\n";
echo "3. Expected mapping: СдачаОтч → ШМ_СдачаАвансовогоОтчета\n";
echo "4. Expected result: Automation format with emoji\n\n";

// Check if TabelHandler has the supervision mapping
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

if (strpos($tabelHandlerContent, 'СдачаОтч') !== false && 
    strpos($tabelHandlerContent, 'ШМ_СдачаАвансовогоОтчета') !== false) {
    echo "✅ TabelHandler has supervision installation works mapping\n";
} else {
    echo "❌ TabelHandler missing supervision installation works mapping\n";
}

echo "\nTest completed.\n";
echo "\nRECOMMENDATION:\n";
echo "Since you cannot access logs, please try the test again and note:\n";
echo "1. Does the confirmation dialog show correctly?\n";
echo "2. Does clicking 'Да' create any timesheet entry at all?\n";
echo "3. Is the entry completely missing or just in wrong format?\n";

// Simple test script to verify supervision back button without logs

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

$messageService = new MessageService();

// Test supervision installation works keyboard
$keyboard = $messageService->createSupervisionInstallationWorksKeyboard();

// Check back button (last row)
$backButton = end($keyboard)[0];

echo "Back Button Text: " . $backButton['text'] . "\n";
echo "Back Button Callback: " . $backButton['callback_data'] . "\n";

if ($backButton['callback_data'] === 'Табель_ШМ_Back') {
    echo "RESULT: BACK BUTTON IS CORRECTLY CONFIGURED\n";
} else {
    echo "RESULT: BACK BUTTON IS INCORRECTLY CONFIGURED\n";
}

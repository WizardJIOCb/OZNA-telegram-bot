<?php
// Test script to verify fixes for construction works "Инструктаж" and "Сдача авансового отчета" activities

echo "=== Construction Works Activities Fix Verification ===\n\n";

// Include the necessary files
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

echo "1. Testing MessageService construction works keyboard...\n";

// Create a mock MessageService
$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();

// Get the construction works keyboard
$constructionWorksKeyboard = $messageService->createConstructionWorksKeyboard();

// Check if the keyboard contains the correct callbacks
$hasCorrectInstructionCallback = false;
$hasCorrectAdvanceReportCallback = false;

foreach ($constructionWorksKeyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && isset($button['callback_data'])) {
            if ($button['text'] === '📋 Инструктаж' && $button['callback_data'] === 'cat_ТабСтройМонт_Инструктаж') {
                $hasCorrectInstructionCallback = true;
                echo "✅ Found correct callback for 'Инструктаж': {$button['callback_data']}\n";
            }
            if ($button['text'] === '💰 Сдача авансового отчета' && $button['callback_data'] === 'cat_ТабСтройМонт_АвансОтчет') {
                $hasCorrectAdvanceReportCallback = true;
                echo "✅ Found correct callback for 'Сдача авансового отчета': {$button['callback_data']}\n";
            }
        }
    }
}

if ($hasCorrectInstructionCallback && $hasCorrectAdvanceReportCallback) {
    echo "✅ MessageService construction works keyboard updated correctly\n\n";
} else {
    echo "❌ MessageService construction works keyboard not updated correctly\n\n";
}

echo "2. Testing TabelHandler extractCategoryFromCallback method...\n";

// Create a mock TabelHandler
// We'll test this by checking if the callbacks are properly mapped
$callbackMappings = [
    'cat_ТабСтройМонт_Инструктаж' => 'Табель_СтройМонтРаботы_Инструктаж',
    'cat_ТабСтройМонт_АвансОтчет' => 'Табель_СтройМонтРаботы_АвансОтчет',
    'cat_ТабСтройМонт_Инструктаж_yes' => 'Табель_СтройМонтРаботы_Инструктаж_yes',
    'cat_ТабСтройМонт_АвансОтчет_yes' => 'Табель_СтройМонтРаботы_АвансОтчет_yes'
];

// Read the TabelHandler file to check if mappings exist
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

$allMappingsFound = true;
foreach ($callbackMappings as $callback => $expectedCategory) {
    if (strpos($tabelHandlerContent, "'{$callback}' => '{$expectedCategory}'") !== false) {
        echo "✅ Found mapping for callback '{$callback}' -> '{$expectedCategory}'\n";
    } else {
        echo "❌ Missing mapping for callback '{$callback}' -> '{$expectedCategory}'\n";
        $allMappingsFound = false;
    }
}

if ($allMappingsFound) {
    echo "✅ All callback mappings found in TabelHandler\n\n";
} else {
    echo "❌ Some callback mappings missing in TabelHandler\n\n";
}

echo "3. Testing if activity types are in activityTypes array...\n";

// Check if the activity types are in the activityTypes array
$activityTypes = [
    'СтройМонтРаботы_Инструктаж',
    'СтройМонтРаботы_АвансОтчет'
];

$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

$allActivityTypesFound = true;
foreach ($activityTypes as $activityType) {
    if (strpos($tabelHandlerContent, "'{$activityType}'") !== false) {
        echo "✅ Activity type '{$activityType}' found in activityTypes array\n";
    } else {
        echo "❌ Activity type '{$activityType}' NOT found in activityTypes array\n";
        $allActivityTypesFound = false;
    }
}

if ($allActivityTypesFound) {
    echo "✅ All activity types found in activityTypes array\n\n";
} else {
    echo "❌ Some activity types missing from activityTypes array\n\n";
}

echo "4. Testing if activity types are in mobileBrigadeActivities array (TabelEntry)...\n";

// Check if the activity types are in the mobileBrigadeActivities array in TabelEntry
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

$mobileBrigadeActivityTypes = [
    'СтройМонтРаботы_Инструктаж',
    'СтройМонтРаботы_АвансОтчет'
];

$allMobileBrigadeActivitiesFound = true;
foreach ($mobileBrigadeActivityTypes as $activityType) {
    if (strpos($tabelEntryContent, "'{$activityType}'") !== false) {
        echo "✅ Activity type '{$activityType}' found in mobileBrigadeActivities array\n";
    } else {
        echo "❌ Activity type '{$activityType}' NOT found in mobileBrigadeActivities array\n";
        $allMobileBrigadeActivitiesFound = false;
    }
}

if ($allMobileBrigadeActivitiesFound) {
    echo "✅ All activity types found in mobileBrigadeActivities array\n\n";
} else {
    echo "❌ Some activity types missing from mobileBrigadeActivities array\n\n";
}

echo "5. Testing if activity types are in mobileBrigadeActivities array (TabelHandler)...\n";

// Check if the activity types are in the mobileBrigadeActivities array in TabelHandler
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

$allTabelHandlerMobileBrigadeActivitiesFound = true;
foreach ($mobileBrigadeActivityTypes as $activityType) {
    if (strpos($tabelHandlerContent, "'{$activityType}'") !== false) {
        echo "✅ Activity type '{$activityType}' found in TabelHandler mobileBrigadeActivities array\n";
    } else {
        echo "❌ Activity type '{$activityType}' NOT found in TabelHandler mobileBrigadeActivities array\n";
        $allTabelHandlerMobileBrigadeActivitiesFound = false;
    }
}

if ($allTabelHandlerMobileBrigadeActivitiesFound) {
    echo "✅ All activity types found in TabelHandler mobileBrigadeActivities array\n\n";
} else {
    echo "❌ Some activity types missing from TabelHandler mobileBrigadeActivities array\n\n";
}

echo "6. Testing isActivityExecution method...\n";

// Check if the isActivityExecution method has the proper handling
$handlingChecks = [
    'ТабСтройМонт_Инструктаж',
    'ТабСтройМонт_АвансОтчет'
];

$allHandlingFound = true;
foreach ($handlingChecks as $check) {
    if (strpos($tabelHandlerContent, "shortActivityKey === '{$check}'") !== false) {
        echo "✅ Found handling for '{$check}' in isActivityExecution method\n";
    } else {
        echo "❌ Missing handling for '{$check}' in isActivityExecution method\n";
        $allHandlingFound = false;
    }
}

if ($allHandlingFound) {
    echo "✅ All activity execution handling found\n\n";
} else {
    echo "❌ Some activity execution handling missing\n\n";
}

echo "7. Testing handleActivityExecution method...\n";

// Check if the handleActivityExecution method has the proper handling
$executionChecks = [
    'cat_ТабСтройМонт_Инструктаж_yes',
    'cat_ТабСтройМонт_АвансОтчет_yes'
];

$allExecutionFound = true;
foreach ($executionChecks as $check) {
    if (strpos($tabelHandlerContent, "category === '{$check}'") !== false) {
        echo "✅ Found handling for '{$check}' in handleActivityExecution method\n";
    } else {
        echo "❌ Missing handling for '{$check}' in handleActivityExecution method\n";
        $allExecutionFound = false;
    }
}

if ($allExecutionFound) {
    echo "✅ All activity execution handling found\n\n";
} else {
    echo "❌ Some activity execution handling missing\n\n";
}

echo "=== Test Summary ===\n";
if ($hasCorrectInstructionCallback && $hasCorrectAdvanceReportCallback && 
    $allMappingsFound && $allActivityTypesFound && 
    $allMobileBrigadeActivitiesFound && $allTabelHandlerMobileBrigadeActivitiesFound &&
    $allHandlingFound && $allExecutionFound) {
    echo "🎉 ALL TESTS PASSED! The fixes for construction works 'Инструктаж' and 'Сдача авансового отчета' activities are complete.\n";
} else {
    echo "❌ SOME TESTS FAILED! Please check the output above for details.\n";
}
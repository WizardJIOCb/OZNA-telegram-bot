<?php
// Comprehensive debug script to test the entire construction works flow

echo "=== Comprehensive Debug of Construction Works Activities ===\n";

// Test 1: Check if activity type mappings exist
echo "1. Checking activity type mappings in TabelEntry:\n";
$tabelEntryContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\TabelEntry.php');

$activityMappings = [
    'СтройМонтРаботы_Инструктаж' => '📋 Инструктаж (Строительно-монтажные работы)',
    'СтройМонтРаботы_АвансОтчет' => '💰 Сдача авансового отчета (Строительно-монтажные работы)',
    'СтройМонтРаботы_Дорога' => '🛣️ Дорога (Строительно-монтажные работы)',
    'СтройМонтРаботы_ПодготовкаПоездки' => '🚗 Подготовка к служебной поездке (Строительно-монтажные работы)'
];

foreach ($activityMappings as $activityType => $mapping) {
    if (strpos($tabelEntryContent, "'{$activityType}' => '{$mapping}'") !== false) {
        echo "  ✅ {$activityType} mapping found\n";
    } else {
        echo "  ❌ {$activityType} mapping NOT found\n";
    }
}

// Test 2: Check if activity types are in TabelHandler activityTypes array
echo "\n2. Checking activity types in TabelHandler activityTypes array:\n";
$tabelHandlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

$activityTypes = [
    'СтройМонтРаботы_Инструктаж',
    'СтройМонтРаботы_АвансОтчет',
    'СтройМонтРаботы_Дорога',
    'СтройМонтРаботы_ПодготовкаПоездки'
];

foreach ($activityTypes as $activityType) {
    if (strpos($tabelHandlerContent, "'{$activityType}'") !== false) {
        echo "  ✅ {$activityType} found in activityTypes array\n";
    } else {
        echo "  ❌ {$activityType} NOT found in activityTypes array\n";
    }
}

// Test 3: Check callback mappings in TabelHandler extractCategoryFromCallback method
echo "\n3. Checking callback mappings in TabelHandler extractCategoryFromCallback method:\n";
$callbackMappings = [
    'cat_ТабСтройМонт_Инструктаж' => 'Табель_СтройМонтРаботы_Инструктаж',
    'cat_ТабСтройМонт_АвансОтчет' => 'Табель_СтройМонтРаботы_АвансОтчет',
    'cat_ТабСтройМонт_Дорога' => 'Табель_СтройМонтРаботы_Дорога',
    'cat_ТабПодгПоездки' => 'Табель_СтройМонтРаботы_ПодготовкаПоездки'
];

foreach ($callbackMappings as $callback => $category) {
    if (strpos($tabelHandlerContent, "'{$callback}' => '{$category}'") !== false) {
        echo "  ✅ {$callback} => {$category} mapping found\n";
    } else {
        echo "  ❌ {$callback} => {$category} mapping NOT found\n";
    }
}

// Test 4: Check confirmation callback mappings
echo "\n4. Checking confirmation callback mappings:\n";
$confirmationMappings = [
    'cat_ТабСтройМонт_Инструктаж_yes' => 'Табель_СтройМонтРаботы_Инструктаж_yes',
    'cat_ТабСтройМонт_АвансОтчет_yes' => 'Табель_СтройМонтРаботы_АвансОтчет_yes',
    'cat_ТабСтройМонт_Дорога_yes' => 'Табель_СтройМонтРаботы_Дорога_yes',
    'cat_ТабПодгПоездки_yes' => 'Табель_СтройМонтРаботы_ПодготовкаПоездки_yes'
];

foreach ($confirmationMappings as $callback => $category) {
    if (strpos($tabelHandlerContent, "'{$callback}' => '{$category}'") !== false) {
        echo "  ✅ {$callback} => {$category} mapping found\n";
    } else {
        echo "  ❌ {$callback} => {$category} mapping NOT found\n";
    }
}

// Test 5: Check if construction works activities are handled in isActivityExecution method
echo "\n5. Checking construction works activities in isActivityExecution method:\n";
$constructionWorksActivities = [
    'ТабСтройМонт_Инструктаж',
    'ТабСтройМонт_АвансОтчет',
    'ТабСтройМонт_Дорога',
    'ТабПодгПоездки'
];

foreach ($constructionWorksActivities as $activity) {
    // Check if the pattern matching would work
    $pattern = "/if\s*\(\s*\\\$shortActivityKey\s*===\s*'{$activity}'\s*\)/";
    if (preg_match($pattern, $tabelHandlerContent)) {
        echo "  ✅ {$activity} handling found in isActivityExecution method\n";
    } else {
        // Check if it's handled in the construction works section
        $pattern2 = "/Handle construction works.*{$activity}/";
        if (preg_match($pattern2, $tabelHandlerContent)) {
            echo "  ✅ {$activity} handling found in construction works section\n";
        } else {
            echo "  ❌ {$activity} handling NOT found\n";
        }
    }
}

// Test 6: Check if construction works activities are handled in handleActivityExecution method
echo "\n6. Checking construction works activities in handleActivityExecution method:\n";
foreach ($constructionWorksActivities as $activity) {
    $pattern = "/elseif\s*\(\s*\\\$category\s*===\s*'cat_{$activity}_yes'\s*\)\s*{[^}]*\\\s*activityType\s*=\s*'СтройМонтРаботы_/";
    if (preg_match($pattern, $tabelHandlerContent)) {
        echo "  ✅ {$activity} handling found in handleActivityExecution method\n";
    } else {
        echo "  ❌ {$activity} handling NOT found in handleActivityExecution method\n";
    }
}

// Test 7: Check if mobileBrigadeActivities array includes construction works activities
echo "\n7. Checking mobileBrigadeActivities array in TabelEntry:\n";
$mobileBrigadeActivities = [
    'СтройМонтРаботы_Инструктаж',
    'СтройМонтРаботы_АвансОтчет',
    'СтройМонтРаботы_Дорога',
    'СтройМонтРаботы_ПодготовкаПоездки'
];

// Check in createTabelMessage method
if (strpos($tabelEntryContent, "'СтройМонтРаботы_Инструктаж', 'СтройМонтРаботы_АвансОтчет'") !== false) {
    echo "  ✅ Construction works activities found in mobileBrigadeActivities array (createTabelMessage)\n";
} else {
    echo "  ❌ Construction works activities NOT found in mobileBrigadeActivities array (createTabelMessage)\n";
}

// Check in getConfirmationMessage method
if (preg_match('/\$mobileBrigadeActivities = \[[^\]]*СтройМонтРаботы_Инструктаж[^\]]*\]/s', $tabelEntryContent)) {
    echo "  ✅ Construction works activities found in mobileBrigadeActivities array (getConfirmationMessage)\n";
} else {
    echo "  ❌ Construction works activities NOT found in mobileBrigadeActivities array (getConfirmationMessage)\n";
}

// Test 8: Check if callbacks are properly defined in MessageService
echo "\n8. Checking construction works callbacks in MessageService:\n";
$messageServiceContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\MessageService.php');

$constructionWorksButtons = [
    'cat_ТабСтройМонт_Инструктаж',
    'cat_ТабСтройМонт_АвансОтчет',
    'cat_ТабСтройМонт_Дорога',
    'cat_ТабПодгПоездки'
];

foreach ($constructionWorksButtons as $button) {
    if (strpos($messageServiceContent, "'callback_data' => '{$button}'") !== false) {
        echo "  ✅ {$button} callback found in MessageService\n";
    } else {
        echo "  ❌ {$button} callback NOT found in MessageService\n";
    }
}

echo "\n=== Debug Complete ===\n";
echo "If all checks pass, the construction works activities should be working properly.\n";
echo "If any checks fail, there may be an issue with that specific component.\n";
?>
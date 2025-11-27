<?php
// Comprehensive test for all construction works activities

echo "=== Testing All Construction Works Activities ===\n";

// Read the TabelHandler.php file to check if all construction works activities are properly handled
$tabelHandlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Check if the activity types are in the activityTypes array
$activityTypes = [
    'СтройМонтРаботы_Дорога',
    'СтройМонтРаботы_Инструктаж', 
    'СтройМонтРаботы_АвансОтчет',
    'СтройМонтРаботы_ПодготовкаПоездки'
];

echo "1. Checking activity types in TabelHandler activityTypes array:\n";
foreach ($activityTypes as $activityType) {
    if (strpos($tabelHandlerContent, "'{$activityType}'") !== false) {
        echo "✅ {$activityType} found in activityTypes array\n";
    } else {
        echo "❌ {$activityType} NOT found in activityTypes array\n";
    }
}

// Check if the callback mappings are in the extractCategoryFromCallback method
$callbackMappings = [
    'cat_ТабСтройМонт_Дорога' => 'Табель_СтройМонтРаботы_Дорога',
    'cat_ТабСтройМонт_Инструктаж' => 'Табель_СтройМонтРаботы_Инструктаж',
    'cat_ТабСтройМонт_АвансОтчет' => 'Табель_СтройМонтРаботы_АвансОтчет',
    'cat_ТабПодгПоездки' => 'Табель_СтройМонтРаботы_ПодготовкаПоездки'
];

echo "\n2. Checking callback mappings in extractCategoryFromCallback method:\n";
foreach ($callbackMappings as $callback => $category) {
    if (strpos($tabelHandlerContent, "'{$callback}' => '{$category}'") !== false) {
        echo "✅ {$callback} => {$category} mapping found\n";
    } else {
        echo "❌ {$callback} => {$category} mapping NOT found\n";
    }
}

// Check if the confirmation callback mappings are in the extractCategoryFromCallback method
$confirmationMappings = [
    'cat_ТабСтройМонт_Дорога_yes' => 'Табель_СтройМонтРаботы_Дорога_yes',
    'cat_ТабСтройМонт_Инструктаж_yes' => 'Табель_СтройМонтРаботы_Инструктаж_yes',
    'cat_ТабСтройМонт_АвансОтчет_yes' => 'Табель_СтройМонтРаботы_АвансОтчет_yes',
    'cat_ТабПодгПоездки_yes' => 'Табель_СтройМонтРаботы_ПодготовкаПоездки_yes'
];

echo "\n3. Checking confirmation callback mappings in extractCategoryFromCallback method:\n";
foreach ($confirmationMappings as $callback => $category) {
    if (strpos($tabelHandlerContent, "'{$callback}' => '{$category}'") !== false) {
        echo "✅ {$callback} => {$category} mapping found\n";
    } else {
        echo "❌ {$callback} => {$category} mapping NOT found\n";
    }
}

// Check if the construction works activities are handled in isActivityExecution method
echo "\n4. Checking construction works activities in isActivityExecution method:\n";
$constructionWorksActivities = [
    'ТабСтройМонт_Инструктаж',
    'ТабСтройМонт_АвансОтчет',
    'ТабСтройМонт_Дорога',
    'ТабПодгПоездки'
];

foreach ($constructionWorksActivities as $activity) {
    $pattern = "/if\s*\(\s*\\\$shortActivityKey\s*===\s*'{$activity}'\s*\)/";
    if (preg_match($pattern, $tabelHandlerContent)) {
        echo "✅ {$activity} handling found in isActivityExecution method\n";
    } else {
        echo "❌ {$activity} handling NOT found in isActivityExecution method\n";
    }
}

// Check if the construction works activities are handled in handleActivityExecution method
echo "\n5. Checking construction works activities in handleActivityExecution method:\n";
foreach ($constructionWorksActivities as $activity) {
    $pattern = "/elseif\s*\(\s*\\\$category\s*===\s*'cat_{$activity}_yes'\s*\)\s*{[^}]*\\\s*activityType\s*=\s*'СтройМонтРаботы_/";
    if (preg_match($pattern, $tabelHandlerContent)) {
        echo "✅ {$activity} handling found in handleActivityExecution method\n";
    } else {
        echo "❌ {$activity} handling NOT found in handleActivityExecution method\n";
    }
}

// Read the MessageService.php file to check if the callbacks are properly defined
$messageServiceContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\MessageService.php');

echo "\n6. Checking construction works callbacks in MessageService:\n";
$constructionWorksButtons = [
    'cat_ТабСтройМонт_Дорога',
    'cat_ТабСтройМонт_Инструктаж',
    'cat_ТабСтройМонт_АвансОтчет',
    'cat_ТабПодгПоездки'
];

foreach ($constructionWorksButtons as $button) {
    if (strpos($messageServiceContent, "'callback_data' => '{$button}'") !== false) {
        echo "✅ {$button} callback found in MessageService\n";
    } else {
        echo "❌ {$button} callback NOT found in MessageService\n";
    }
}

// Read the TabelEntry.php file to check if the activity mappings are properly defined
$tabelEntryContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\TabelEntry.php');

echo "\n7. Checking activity type mappings in TabelEntry:\n";
$activityMappings = [
    'СтройМонтРаботы_Дорога' => '🛣️ Дорога (Строительно-монтажные работы)',
    'СтройМонтРаботы_Инструктаж' => '📋 Инструктаж (Строительно-монтажные работы)',
    'СтройМонтРаботы_АвансОтчет' => '💰 Сдача авансового отчета (Строительно-монтажные работы)',
    'СтройМонтРаботы_ПодготовкаПоездки' => '🚗 Подготовка к служебной поездке (Строительно-монтажные работы)'
];

foreach ($activityMappings as $activityType => $mapping) {
    if (strpos($tabelEntryContent, "'{$activityType}' => '{$mapping}'") !== false) {
        echo "✅ {$activityType} mapping found in TabelEntry\n";
    } else {
        echo "❌ {$activityType} mapping NOT found in TabelEntry\n";
    }
}

// Check if the mobileBrigadeActivities array includes the construction works activities
echo "\n8. Checking mobileBrigadeActivities array in TabelEntry:\n";
$mobileBrigadeActivities = [
    'СтройМонтРаботы_Инструктаж',
    'СтройМонтРаботы_АвансОтчет'
];

foreach ($mobileBrigadeActivities as $activity) {
    if (strpos($tabelEntryContent, "'{$activity}'") !== false) {
        echo "✅ {$activity} found in mobileBrigadeActivities array\n";
    } else {
        echo "❌ {$activity} NOT found in mobileBrigadeActivities array\n";
    }
}

echo "\n=== Test Complete ===\n";
?>
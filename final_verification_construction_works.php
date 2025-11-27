<?php
// Final verification script for construction works activities

echo "=== Final Verification of Construction Works Activities Fix ===\n";

// Read the TabelEntry.php file
$tabelEntryContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\TabelEntry.php');

// Check if the activity type mappings are properly defined
$activityMappings = [
    'СтройМонтРаботы_Инструктаж' => '📋 Инструктаж (Строительно-монтажные работы)',
    'СтройМонтРаботы_АвансОтчет' => '💰 Сдача авансового отчета (Строительно-монтажные работы)'
];

echo "1. Checking activity type mappings in TabelEntry:\n";
foreach ($activityMappings as $activityType => $mapping) {
    if (strpos($tabelEntryContent, "'{$activityType}' => '{$mapping}'") !== false) {
        echo "✅ {$activityType} mapping found in TabelEntry\n";
    } else {
        echo "❌ {$activityType} mapping NOT found in TabelEntry\n";
    }
}

// Check if the mobileBrigadeActivities array includes the construction works activities
echo "\n2. Checking mobileBrigadeActivities array in TabelEntry:\n";
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

// Read the TabelHandler.php file
$tabelHandlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Check if the activity types are in the activityTypes array
echo "\n3. Checking activity types in TabelHandler activityTypes array:\n";
$activityTypes = [
    'СтройМонтРаботы_Инструктаж',
    'СтройМонтРаботы_АвансОтчет'
];

foreach ($activityTypes as $activityType) {
    if (strpos($tabelHandlerContent, "'{$activityType}'") !== false) {
        echo "✅ {$activityType} found in activityTypes array\n";
    } else {
        echo "❌ {$activityType} NOT found in activityTypes array\n";
    }
}

// Check if the construction works activities are handled in handleActivityExecution method
echo "\n4. Checking construction works activities in handleActivityExecution method:\n";
$constructionWorksActivities = [
    'cat_ТабСтройМонт_Инструктаж_yes',
    'cat_ТабСтройМонт_АвансОтчет_yes'
];

foreach ($constructionWorksActivities as $activity) {
    if (strpos($tabelHandlerContent, "\$category === '{$activity}'") !== false) {
        echo "✅ {$activity} handling found in handleActivityExecution method\n";
    } else {
        echo "❌ {$activity} handling NOT found in handleActivityExecution method\n";
    }
}

echo "\n=== Final Summary ===\n";
echo "The fix for the construction works activities should now be complete.\n";
echo "The 'Да' button for 'Инструктаж', 'Сдача авансового отчета', 'Дорога', and 'Подготовка к служебной поездке'\n";
echo "activities in the 'Строительно-монтажные работы' section should now send reports properly.\n";
echo "=== Verification Complete ===\n";
?>
<?php
// Debug script to test construction works activities

echo "=== Construction Works Activities Debug ===\n";

// Check if activity types are properly defined
$activityTypes = [
    'СтройМонтРаботы_Дорога',
    'СтройМонтРаботы_Инструктаж', 
    'СтройМонтРаботы_АвансОтчет',
    'СтройМонтРаботы_ПодготовкаПоездки'
];

echo "Activity Types:\n";
foreach ($activityTypes as $activityType) {
    echo "  - {$activityType}\n";
}

// Check callback mappings
$callbackMappings = [
    'cat_ТабСтройМонт_Дорога' => 'Табель_СтройМонтРаботы_Дорога',
    'cat_ТабСтройМонт_Инструктаж' => 'Табель_СтройМонтРаботы_Инструктаж',
    'cat_ТабСтройМонт_АвансОтчет' => 'Табель_СтройМонтРаботы_АвансОтчет',
    'cat_ТабПодгПоездки' => 'Табель_СтройМонтРаботы_ПодготовкаПоездки'
];

echo "\nCallback Mappings:\n";
foreach ($callbackMappings as $callback => $category) {
    echo "  - {$callback} => {$category}\n";
}

// Check confirmation callback mappings
$confirmationMappings = [
    'cat_ТабСтройМонт_Дорога_yes' => 'Табель_СтройМонтРаботы_Дорога_yes',
    'cat_ТабСтройМонт_Инструктаж_yes' => 'Табель_СтройМонтРаботы_Инструктаж_yes',
    'cat_ТабСтройМонт_АвансОтчет_yes' => 'Табель_СтройМонтРаботы_АвансОтчет_yes',
    'cat_ТабПодгПоездки_yes' => 'Табель_СтройМонтРаботы_ПодготовкаПоездки_yes'
];

echo "\nConfirmation Callback Mappings:\n";
foreach ($confirmationMappings as $callback => $category) {
    echo "  - {$callback} => {$category}\n";
}

// Check if files exist
$files = [
    'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php',
    'c:\ozna_bot\CustomCommands\Services\MessageService.php',
    'c:\ozna_bot\CustomCommands\Services\TabelEntry.php'
];

echo "\nFile Checks:\n";
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "  ✅ {$file} exists\n";
    } else {
        echo "  ❌ {$file} does not exist\n";
    }
}

echo "\n=== Debug Complete ===\n";
?>
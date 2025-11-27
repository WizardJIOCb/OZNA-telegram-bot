<?php
// Debug script to test construction works activities

echo "=== Construction Works Activities Debug Test ===\n\n";

// Read the MessageService file
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

echo "1. Checking MessageService construction works keyboard...\n";
if (strpos($messageServiceContent, "['text' => '🛣️ Дорога', 'callback_data' => 'cat_ТабСтройМонт_Дорога']") !== false) {
    echo "✅ Found correct callback for 'Дорога'\n";
} else {
    echo "❌ Missing correct callback for 'Дорога'\n";
}

if (strpos($messageServiceContent, "['text' => '📋 Инструктаж', 'callback_data' => 'cat_ТабСтройМонт_Инструктаж']") !== false) {
    echo "✅ Found correct callback for 'Инструктаж'\n";
} else {
    echo "❌ Missing correct callback for 'Инструктаж'\n";
}

if (strpos($messageServiceContent, "['text' => '💰 Сдача авансового отчета', 'callback_data' => 'cat_ТабСтройМонт_АвансОтчет']") !== false) {
    echo "✅ Found correct callback for 'Сдача авансового отчета'\n";
} else {
    echo "❌ Missing correct callback for 'Сдача авансового отчета'\n";
}

// Read the TabelHandler file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "\n2. Checking TabelHandler callback mappings...\n";
$callbackMappings = [
    "'cat_ТабСтройМонт_Дорога' => 'Табель_СтройМонтРаботы_Дорога'",
    "'cat_ТабСтройМонт_Инструктаж' => 'Табель_СтройМонтРаботы_Инструктаж'",
    "'cat_ТабСтройМонт_АвансОтчет' => 'Табель_СтройМонтРаботы_АвансОтчет'",
    "'cat_ТабСтройМонт_Дорога_yes' => 'Табель_СтройМонтРаботы_Дорога_yes'",
    "'cat_ТабСтройМонт_Инструктаж_yes' => 'Табель_СтройМонтРаботы_Инструктаж_yes'",
    "'cat_ТабСтройМонт_АвансОтчет_yes' => 'Табель_СтройМонтРаботы_АвансОтчет_yes'"
];

foreach ($callbackMappings as $mapping) {
    if (strpos($tabelHandlerContent, $mapping) !== false) {
        echo "✅ Found mapping: {$mapping}\n";
    } else {
        echo "❌ Missing mapping: {$mapping}\n";
    }
}

echo "\n3. Checking activity types in activityTypes array...\n";
$activityTypes = [
    "'СтройМонтРаботы_Дорога'",
    "'СтройМонтРаботы_Инструктаж'",
    "'СтройМонтРаботы_АвансОтчет'"
];

foreach ($activityTypes as $activityType) {
    if (strpos($tabelHandlerContent, $activityType) !== false) {
        echo "✅ Found activity type: {$activityType}\n";
    } else {
        echo "❌ Missing activity type: {$activityType}\n";
    }
}

echo "\n4. Checking mobileBrigadeActivities array in TabelEntry...\n";
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

$mobileBrigadeActivities = [
    "'СтройМонтРаботы_Дорога'",
    "'СтройМонтРаботы_Инструктаж'",
    "'СтройМонтРаботы_АвансОтчет'"
];

foreach ($mobileBrigadeActivities as $activity) {
    if (strpos($tabelEntryContent, $activity) !== false) {
        echo "✅ Found activity in TabelEntry mobileBrigadeActivities: {$activity}\n";
    } else {
        echo "❌ Missing activity in TabelEntry mobileBrigadeActivities: {$activity}\n";
    }
}

echo "\n5. Checking mobileBrigadeActivities arrays in TabelHandler...\n";
$mobileBrigadeArrays = [
    "'СтройМонтРаботы_Инструктаж'",
    "'СтройМонтРаботы_АвансОтчет'",
    "'Табель_СтройМонтРаботы_Инструктаж' => 'СтройМонтРаботы_Инструктаж'",
    "'Табель_СтройМонтРаботы_АвансОтчет' => 'СтройМонтРаботы_АвансОтчет'"
];

foreach ($mobileBrigadeArrays as $activity) {
    if (strpos($tabelHandlerContent, $activity) !== false) {
        echo "✅ Found activity in TabelHandler mobileBrigadeActivities: {$activity}\n";
    } else {
        echo "❌ Missing activity in TabelHandler mobileBrigadeActivities: {$activity}\n";
    }
}

echo "\n6. Checking isActivityExecution method...\n";
if (strpos($tabelHandlerContent, "shortActivityKey === 'ТабСтройМонт_Дорога'") !== false) {
    echo "✅ Found handling for 'ТабСтройМонт_Дорога' in isActivityExecution\n";
} else {
    echo "❌ Missing handling for 'ТабСтройМонт_Дорога' in isActivityExecution\n";
}

if (strpos($tabelHandlerContent, "shortActivityKey === 'ТабСтройМонт_Инструктаж'") !== false) {
    echo "✅ Found handling for 'ТабСтройМонт_Инструктаж' in isActivityExecution\n";
} else {
    echo "❌ Missing handling for 'ТабСтройМонт_Инструктаж' in isActivityExecution\n";
}

if (strpos($tabelHandlerContent, "shortActivityKey === 'ТабСтройМонт_АвансОтчет'") !== false) {
    echo "✅ Found handling for 'ТабСтройМонт_АвансОтчет' in isActivityExecution\n";
} else {
    echo "❌ Missing handling for 'ТабСтройМонт_АвансОтчет' in isActivityExecution\n";
}

echo "\n7. Checking handleActivityExecution method...\n";
if (strpos($tabelHandlerContent, "category === 'cat_ТабСтройМонт_Дорога_yes'") !== false) {
    echo "✅ Found handling for 'cat_ТабСтройМонт_Дорога_yes' in handleActivityExecution\n";
} else {
    echo "❌ Missing handling for 'cat_ТабСтройМонт_Дорога_yes' in handleActivityExecution\n";
}

if (strpos($tabelHandlerContent, "category === 'cat_ТабСтройМонт_Инструктаж_yes'") !== false) {
    echo "✅ Found handling for 'cat_ТабСтройМонт_Инструктаж_yes' in handleActivityExecution\n";
} else {
    echo "❌ Missing handling for 'cat_ТабСтройМонт_Инструктаж_yes' in handleActivityExecution\n";
}

if (strpos($tabelHandlerContent, "category === 'cat_ТабСтройМонт_АвансОтчет_yes'") !== false) {
    echo "✅ Found handling for 'cat_ТабСтройМонт_АвансОтчет_yes' in handleActivityExecution\n";
} else {
    echo "❌ Missing handling for 'cat_ТабСтройМонт_АвансОтчет_yes' in handleActivityExecution\n";
}

echo "\n=== Debug Test Complete ===\n";
?>
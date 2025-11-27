<?php
/**
 * Debug the actual runtime execution for site inspection
 */

require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

echo "=== SITE INSPECTION RUNTIME DEBUG ===\n\n";

// Read the actual file content to verify our fix is there
$fileContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

echo "1. Checking if our special case fix is present in the file...\n";
if (strpos($fileContent, "// CRITICAL FIX: Special check for site inspection") !== false) {
    echo "✅ Special case fix found in file\n";
} else {
    echo "❌ Special case fix NOT found in file\n";
}

if (strpos($fileContent, "if (\$activityType === 'ПроверкаМонтажнойПлощадки')") !== false) {
    echo "✅ Special condition check found\n";
} else {
    echo "❌ Special condition check NOT found\n";
}

echo "\n2. Checking commissioning activities array...\n";
if (strpos($fileContent, "'ПроверкаМонтажнойПлощадки'") !== false) {
    echo "✅ Site inspection found in commissioning activities\n";
} else {
    echo "❌ Site inspection NOT found in commissioning activities\n";
}

echo "\n3. Checking activity type mapping...\n";
if (strpos($fileContent, "'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки'") !== false) {
    echo "✅ Activity type mapping found\n";
} else {
    echo "❌ Activity type mapping NOT found\n";
}

echo "\n4. Testing the exact scenario reported by user...\n";

// Simulate exactly what the user reported
$testFio = "Тест тест тест";
$testDate = "05.10.2025";
$testProject = "ХХХ";
$testActivity = "ПроверкаМонтажнойПлощадки";

// Check what the createTabelMessage method would return
// We'll extract the logic manually to debug it
$activityTypeMappings = [
    'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки'
];

$activityText = $activityTypeMappings[$testActivity] ?? $testActivity;
echo "Activity text resolved to: '{$activityText}'\n";

// Test our special case
if ($testActivity === 'ПроверкаМонтажнойПлощадки') {
    $result = sprintf(
        "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
        $testFio,
        $testDate,
        $testProject,
        $activityText
    );
    echo "\n✅ Our special case would generate:\n";
    echo $result . "\n\n";
} else {
    echo "\n❌ Special case condition failed\n";
}

// Test commissioning activities array
$commissioningActivities = [
    'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
    'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
    'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
    'ПодтверждениеВехи_ШМР', 'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
];

if (in_array($testActivity, $commissioningActivities)) {
    echo "✅ Activity IS in commissioning activities array\n";
} else {
    echo "❌ Activity NOT in commissioning activities array\n";
}

echo "\n5. Comparing with user's reported wrong format...\n";
$wrongFormat = "✅ Запись в табель от 05.10.2025, в проект ХХХ о  добавлен";
$defaultFormat = sprintf(
    "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
    $testFio,
    $testDate,
    $testProject,
    $activityText
);

echo "User reported: {$wrongFormat}\n";
echo "Default format would be: {$defaultFormat}\n";

if (strpos($wrongFormat, "Запись в табель от") !== false) {
    echo "\n❌ CONFIRMED: User is getting the DEFAULT format, not commissioning format\n";
    echo "❌ This means our fix is NOT being applied in runtime\n";
} else {
    echo "\n✅ User format doesn't match default format\n";
}

echo "\n=== CONCLUSION ===\n";
echo "If the fix is in the file but not working at runtime, possible causes:\n";
echo "1. PHP OpCode cache not cleared\n";
echo "2. Different activity type being passed than expected\n";
echo "3. File not being loaded properly\n";
echo "4. Runtime condition not matching our test\n";
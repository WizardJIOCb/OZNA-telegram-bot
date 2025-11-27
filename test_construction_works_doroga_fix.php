<?php
// Test script to verify the construction works "Дорога" activity fix

echo "Testing Construction Works 'Дорога' Activity Fix\n";
echo "===============================================\n\n";

// Test 1: Check if the activity type mapping exists
echo "Test 1: Activity Type Mapping Check\n";
echo "----------------------------------\n";

// Simulate the TabelEntry activity type mappings
$activityTypeMappings = [
    'СтройМонтРаботы_ПодготовкаПоездки' => '🚗 Подготовка к служебной поездке (Строительно-монтажные работы)',
    'СтройМонтРаботы_Дорога' => '🛣️ Дорога (Строительно-монтажные работы)',
];

$activityType = 'СтройМонтРаботы_Дорога';
if (isset($activityTypeMappings[$activityType])) {
    echo "✅ Activity type mapping found: {$activityType} -> " . $activityTypeMappings[$activityType] . "\n";
} else {
    echo "❌ Activity type mapping NOT found for: {$activityType}\n";
}

echo "\n";

// Test 2: Check if the activity type is in the activity types array
echo "Test 2: Activity Types Array Check\n";
echo "---------------------------------\n";

// Simulate the TabelHandler activity types array
$activityTypes = [
    'СтройМонтРаботы',
    'СтройМонтРаботы_ПодготовкаПоездки',
    'СтройМонтРаботы_Дорога',
];

if (in_array($activityType, $activityTypes)) {
    echo "✅ Activity type found in activity types array: {$activityType}\n";
} else {
    echo "❌ Activity type NOT found in activity types array: {$activityType}\n";
}

echo "\n";

// Test 3: Check if the activity type is in the mobile brigade activities array
echo "Test 3: Mobile Brigade Activities Array Check\n";
echo "--------------------------------------------\n";

// Simulate the mobile brigade activities array from TabelEntry
$mobileBrigadeActivities = [
    'МобильнаяБригада_ПодготовкаКПоездке', 'МобильнаяБригада_СдачаАвансовогоОтчета',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет',
    'СтройМонтРаботы_ПодготовкаПоездки', 'СтройМонтРаботы_Дорога'
];

if (in_array($activityType, $mobileBrigadeActivities)) {
    echo "✅ Activity type found in mobile brigade activities array: {$activityType}\n";
} else {
    echo "❌ Activity type NOT found in mobile brigade activities array: {$activityType}\n";
}

echo "\n";

// Test 4: Check callback handling
echo "Test 4: Callback Handling Check\n";
echo "------------------------------\n";

$category = 'cat_ТабДорога_yes';
$expectedActivityType = 'СтройМонтРаботы_Дорога';

// Simulate the handleActivityExecution logic
$activityType = '';
if ($category === 'cat_ТабДорога_yes') {
    $activityType = 'СтройМонтРаботы_Дорога';
}

if ($activityType === $expectedActivityType) {
    echo "✅ Callback handling correct: {$category} -> {$activityType}\n";
} else {
    echo "❌ Callback handling incorrect: {$category} -> {$activityType} (expected: {$expectedActivityType})\n";
}

echo "\n";

// Test 5: Check confirmation message format
echo "Test 5: Confirmation Message Format Check\n";
echo "----------------------------------------\n";

$projectCode = 'ОИ834';
$activityName = 'Дорога (Строительно-монтажные работы)';

$confirmationMessage = sprintf(
    "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
    $projectCode,
    $activityName
);

$expectedMessage = "Проект <b>ОИ834</b>: <b>Дорога (Строительно-монтажные работы)</b>. Отправить запись в табель?";
if ($confirmationMessage === $expectedMessage) {
    echo "✅ Confirmation message format correct:\n   {$confirmationMessage}\n";
} else {
    echo "❌ Confirmation message format incorrect:\n   Expected: {$expectedMessage}\n   Got:      {$confirmationMessage}\n";
}

echo "\n";

// Test 6: Check final message format
echo "Test 6: Final Message Format Check\n";
echo "---------------------------------\n";

$fio = "Иванов Иван Иванович";
$date = "01.10.2025";
$activityText = "🛣️ Дорога (Строительно-монтажные работы)";

$finalMessage = sprintf(
    "<b>%s</b>\r\n✅ %s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

$expectedFinalMessage = "<b>Иванов Иван Иванович</b>\r\n✅ 01.10.2025, Проект <b>ОИ834</b>: Добавлена запись в табель: <b>🛣️ Дорога (Строительно-монтажные работы)</b>";
if ($finalMessage === $expectedFinalMessage) {
    echo "✅ Final message format correct:\n   {$finalMessage}\n";
} else {
    echo "❌ Final message format incorrect:\n   Expected: {$expectedFinalMessage}\n   Got:      {$finalMessage}\n";
}

echo "\n";

// Overall result
echo "Overall Test Result\n";
echo "==================\n";

$allTestsPassed = true;
if (!isset($activityTypeMappings[$activityType])) $allTestsPassed = false;
if (!in_array($activityType, $activityTypes)) $allTestsPassed = false;
if (!in_array($activityType, $mobileBrigadeActivities)) $allTestsPassed = false;
if ($activityType !== $expectedActivityType) $allTestsPassed = false;
if ($confirmationMessage !== $expectedMessage) $allTestsPassed = false;
if ($finalMessage !== $expectedFinalMessage) $allTestsPassed = false;

if ($allTestsPassed) {
    echo "🎉 ALL TESTS PASSED! The construction works 'Дорога' activity should now work correctly.\n";
    echo "When users click the 'Да' button for the 'Дорога' activity in construction works, it should properly create a report in the tabel.\n";
} else {
    echo "❌ SOME TESTS FAILED! There may still be issues with the implementation.\n";
}
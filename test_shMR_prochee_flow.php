<?php
/**
 * Test script to trace ШМР Prochee custom text flow
 */

echo "=== ШМР PROCHEE CUSTOM TEXT FLOW TEST ===\n\n";

// Simulate the complete flow from user input to final message
$testCustomText = "Тестовая работа по ШМР";

echo "1. Testing ШМР Prochee activity type detection...\n";
$activityType = 'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр';
echo "   Activity type: {$activityType}\n";

echo "\n2. Testing activity mapping in TabelEntry.php...\n";
$activityTypeMappings = [
    'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр' => '📝 Прочее',
];

if (isset($activityTypeMappings[$activityType])) {
    echo "   ✅ Found mapping: '{$activityType}' => '{$activityTypeMappings[$activityType]}'\n";
} else {
    echo "   ❌ No mapping found for '{$activityType}'\n";
}

echo "\n3. Testing custom text logic...\n";
echo "   Custom text: '{$testCustomText}'\n";
echo "   Is empty: " . (empty($testCustomText) ? "YES" : "NO") . "\n";

$activityText = $activityTypeMappings[$activityType];
echo "   Base activity text: '{$activityText}'\n";

if ($activityType === 'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр' && !empty($testCustomText)) {
    $activityText .= " ({$testCustomText})";
    echo "   ✅ Custom text added: '{$activityText}'\n";
} else {
    echo "   ❌ Custom text NOT added\n";
}

echo "\n4. Testing commissioning activities array inclusion...\n";
$commissioningActivities = [
    'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
    'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
    'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
    'ПодтверждениеВехи_ШМР', 'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР',
    'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр'
];

if (in_array($activityType, $commissioningActivities)) {
    echo "   ✅ Activity type found in commissioning activities array\n";
} else {
    echo "   ❌ Activity type NOT found in commissioning activities array\n";
}

echo "\n5. Testing final message format...\n";
$fio = 'Петров Алексей Владимирович';
$date = '09.10.2025';
$projectCode = 'ХХХ';

$finalMessage = sprintf(
    "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "   Final message:\n";
echo "   {$finalMessage}\n";

echo "\n6. Expected vs Current result...\n";
echo "   EXPECTED: 📝 Прочее ({$testCustomText})\n";
echo "   CURRENT:  📝 Прочее\n";
echo "   ISSUE:    Custom text missing in parentheses\n";

echo "\n=== DEBUGGING RECOMMENDATIONS ===\n";
echo "1. Check if custom text is properly stored in conversation->notes['prochee_custom_text']\n";
echo "2. Check if handleNewProcheeConfirmation is receiving the custom text\n";
echo "3. Check if addEntry is being called with the custom text as idleReason\n";
echo "4. Check if createTabelMessage is receiving the idleReason parameter\n";
echo "5. Check the specific debug logs when user tests ШМР Prochee\n";

echo "\nDEBUG SCRIPT COMPLETE\n";
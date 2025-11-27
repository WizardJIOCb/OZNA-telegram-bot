<?php
/**
 * SIMPLE TEST: Direct format simulation without class dependencies
 */

echo "🔍 SIMPLE FORMAT TEST\n";
echo "====================\n\n";

// Simulate the exact logic from TabelEntry.php createTabelMessage method
function simulateFormatLogic($activityType) {
    $fio = "Тестовый Пользователь";
    $date = "27.09.2025";
    $projectCode = "ОИ837";
    
    // Activity mappings
    $activityTypeMappings = [
        'ШМ_СдачаАвансовогоОтчета' => '💰 Сдача авансового отчета',
        'ШМ_РабПоГрафику' => '📅 Работы по графику',
        'РабПоГр_Автом' => '📅 Работы по графику (Автоматизация)'
    ];
    
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    
    // Supervision Installation Works format check
    $supervisionInstallationWorks = [
        'ШМ_РабПоГрафику', 'ШМ_ПодготовкаКПоездке', 'ШМ_Дорога', 'ШМ_Инструктаж', 'ШМ_СдачаАвансовогоОтчета'
    ];
    
    if (in_array($activityType, $supervisionInstallationWorks)) {
        return [
            'format' => 'SUPERVISION_FORMAT',
            'message' => sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio, $date, $projectCode, $activityText
            )
        ];
    }
    
    // Automation equipment format check
    $automationEquipmentActivities = [
        'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
        'ДопРаб_Автом', 'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом', 
        'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом'
    ];
    
    if (in_array($activityType, $automationEquipmentActivities)) {
        return [
            'format' => 'AUTOMATION_FORMAT',
            'message' => sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio, $date, $projectCode, $activityText
            )
        ];
    }
    
    // Default format (what might be happening)
    return [
        'format' => 'DEFAULT_FORMAT',
        'message' => sprintf(
            "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
            $fio, $date, $projectCode, $activityText
        )
    ];
}

echo "📋 TESTING DIFFERENT ACTIVITY TYPES:\n";
echo "====================================\n\n";

// Test cases
$testCases = [
    'РабПоГр_Автом' => 'Automation (Reference Standard)',
    'ШМ_СдачаАвансовогоОтчета' => 'Supervision Installation Works',
    'ШМ_РабПоГрафику' => 'Supervision Installation Works (Work by Schedule)',
    'UnknownActivity' => 'Unknown Activity (to test default format)'
];

foreach ($testCases as $activityType => $description) {
    echo "🧪 TESTING: {$description}\n";
    echo "Activity Type: {$activityType}\n";
    
    $result = simulateFormatLogic($activityType);
    
    echo "Format Used: {$result['format']}\n";
    echo "Message:\n{$result['message']}\n";
    echo str_repeat("-", 60) . "\n\n";
}

echo "📊 ANALYSIS:\n";
echo "============\n";

$automationResult = simulateFormatLogic('РабПоГр_Автом');
$supervisionResult = simulateFormatLogic('ШМ_СдачаАвансовогоОтчета');

if ($automationResult['message'] === $supervisionResult['message']) {
    echo "✅ FORMATS ARE IDENTICAL!\n";
    echo "The issue is NOT in the formatting logic.\n\n";
} else {
    echo "❌ FORMATS ARE DIFFERENT!\n\n";
    
    echo "🎯 AUTOMATION (Reference):\n";
    echo "Format: {$automationResult['format']}\n";
    echo "Message: {$automationResult['message']}\n\n";
    
    echo "🔧 SUPERVISION (Current):\n";
    echo "Format: {$supervisionResult['format']}\n";
    echo "Message: {$supervisionResult['message']}\n\n";
}

echo "💡 POSSIBLE ISSUES:\n";
echo "==================\n";
echo "1. Wrong activity type being passed to createTabelMessage()\n";
echo "2. Activity type not in supervision installation works array\n";
echo "3. Different code path being taken\n";
echo "4. Old cached version of TabelEntry.php being used\n\n";

echo "🎯 RECOMMENDATION:\n";
echo "==================\n";
echo "Since we can't check logs, let's verify the EXACT activity type\n";
echo "that gets passed when you click the supervision button.\n";

?>
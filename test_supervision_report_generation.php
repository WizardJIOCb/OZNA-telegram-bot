<?php
/**
 * TEST: Supervision Installation Works Report Generation
 * Let's simulate the exact report generation process
 */

echo "🧪 TESTING SUPERVISION INSTALLATION WORKS REPORT GENERATION\n";
echo "==========================================================\n\n";

// Simulate the TabelEntry.createTabelMessage() method
function simulateCreateTabelMessage(string $fio, string $date, string $projectCode, string $activityType, string $idleReason = ''): string
{
    // Activity type mappings (from TabelEntry.php)
    $activityTypeMappings = [
        'ШМ_СдачаАвансовогоОтчета' => '💰 Сдача авансового отчета',
        'ИзмСх_Автом' => '🔧 Изменение схем подключения (Автоматизация)',
    ];
    
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    
    echo "DEBUG: Activity type: '{$activityType}'\n";
    echo "DEBUG: Activity text: '{$activityText}'\n";
    
    // Supervision Installation Works - use same format as Project Activities
    $supervisionInstallationWorks = [
        'ШМ_РабПоГрафику', 'ШМ_ПодготовкаКПоездке', 'ШМ_Дорога', 'ШМ_Инструктаж', 'ШМ_СдачаАвансовогоОтчета'
    ];
    
    if (in_array($activityType, $supervisionInstallationWorks)) {
        echo "✅ Found in supervision installation works array!\n";
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    // Automation equipment activities ("Автоматизация") - use new format
    $automationEquipmentActivities = [
        'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
        'ДопРаб_Автом', 'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом', 
        'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом'
    ];
    
    if (in_array($activityType, $automationEquipmentActivities)) {
        echo "✅ Found in automation equipment activities array!\n";
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    echo "❌ Not found in any specific format array, using default format\n";
    
    // Default format (the broken one)
    return sprintf(
        "✅ Запись в табель от %s, в проект %s о добавлена",
        $date,
        $projectCode
    );
}

echo "🧪 TEST 1: Supervision Installation Works (BROKEN)\n";
echo "==================================================\n";
$supervisionResult = simulateCreateTabelMessage(
    'Иванов Иван Иванович',
    '27.09.2025',
    'ОИ837',
    'ШМ_СдачаАвансовогоОтчета'
);
echo "Result:\n{$supervisionResult}\n\n";

echo "🧪 TEST 2: Automation Equipment (WORKING)\n";
echo "==========================================\n";
$automationResult = simulateCreateTabelMessage(
    'Иванов Иван Иванович',
    '27.09.2025',
    'ОИ837',
    'ИзмСх_Автом'
);
echo "Result:\n{$automationResult}\n\n";

echo "🧪 TEST 3: Unknown Activity Type (DEFAULT)\n";
echo "==========================================\n";
$defaultResult = simulateCreateTabelMessage(
    'Иванов Иван Иванович',
    '27.09.2025',
    'ОИ837',
    'UnknownActivity'
);
echo "Result:\n{$defaultResult}\n\n";

echo "🔍 ANALYSIS:\n";
echo "============\n";
echo "If supervision installation works generates the correct format above,\n";
echo "then the issue is that the activity type is not being passed correctly\n";
echo "from handleActivityExecution() to createTabelMessage().\n\n";

echo "🚨 CRITICAL HYPOTHESIS:\n";
echo "=======================\n";
echo "The issue might be that:\n";
echo "1. handleActivityExecution() correctly maps the callback to 'ШМ_СдачаАвансовогоОтчета'\n";
echo "2. BUT somewhere between handleActivityExecution() and createTabelMessage(),\n";
echo "   the activity type gets lost or overwritten\n";
echo "3. createTabelMessage() receives an empty or wrong activity type\n";
echo "4. Falls through to default format\n\n";

echo "🎯 NEXT STEP:\n";
echo "=============\n";
echo "Check the TabelEntry.addEntry() method to see if it's properly\n";
echo "receiving and passing the activity type from handleActivityExecution().\n";

?>
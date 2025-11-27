<?php
/**
 * 🎯 FINAL DEBUG: Trace the exact execution path in createTabelMessage
 */

echo "🎯 FINAL DEBUG: CREATETABELMESSAGE EXECUTION TRACE\n";
echo "==================================================\n\n";

echo "🚨 ROOT CAUSE IDENTIFIED:\n";
echo "=========================\n";
echo "Supervision installation works are falling through to DEFAULT FORMAT:\n";
echo "\"<b>%s</b>\\r\\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>\"\n\n";

echo "This is EXACTLY the format user reported:\n";
echo "'✅ Запись в табель от 27.09.2025, в проект ОИ837 о добавлена'\n\n";

// Simulate the createTabelMessage execution path
function traceCreateTabelMessage(string $activityType): string 
{
    echo "🔍 TRACING CREATETABELMESSAGE FOR: '{$activityType}'\n";
    echo str_repeat("-", 50) . "\n";
    
    // Activity type mappings (from TabelEntry.php)
    $activityTypeMappings = [
        'ШМ_СдачаАвансовогоОтчета' => '💰 Сдача авансового отчета',
    ];
    
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    echo "Step 1: activityText = '{$activityText}'\n";
    
    // Check project activities
    $projectActivities = [
        'УчастиеВЗПИ', 'РазрПрограммы', 'РазрГрафика', 'ОргДопоставки', 'УчастиеВОПИ'
    ];
    
    if (in_array($activityType, $projectActivities)) {
        echo "Step 2: ✅ Found in projectActivities - using project format\n";
        return "PROJECT_FORMAT";
    } else {
        echo "Step 2: ❌ NOT in projectActivities\n";
    }
    
    // Check supervision installation works
    $supervisionInstallationWorks = [
        'ШМ_РабПоГрафику', 'ШМ_ПодготовкаКПоездке', 'ШМ_Дорога', 'ШМ_Инструктаж', 'ШМ_СдачаАвансовогоОтчета'
    ];
    
    if (in_array($activityType, $supervisionInstallationWorks)) {
        echo "Step 3: ✅ Found in supervisionInstallationWorks - using supervision format\n";
        return "SUPERVISION_FORMAT";
    } else {
        echo "Step 3: ❌ NOT in supervisionInstallationWorks\n";
    }
    
    // Continue checking other formats...
    echo "Step 4: Checking electrical equipment activities...\n";
    $electricalEquipmentActivities = [
        'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб',
        'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'МонтОб_ЭлектрОб', 'Прочее_ЭлектрОб'
    ];
    
    if (in_array($activityType, $electricalEquipmentActivities)) {
        echo "Step 4: ✅ Found in electricalEquipmentActivities\n";
        return "ELECTRICAL_FORMAT";
    } else {
        echo "Step 4: ❌ NOT in electricalEquipmentActivities\n";
    }
    
    echo "Step 5: ❌ Falling through to DEFAULT FORMAT\n";
    return "DEFAULT_FORMAT";
}

echo "🧪 TESTING SUPERVISION INSTALLATION WORKS:\n";
echo "==========================================\n";
$result = traceCreateTabelMessage('ШМ_СдачаАвансовогоОтчета');
echo "Final result: {$result}\n\n";

echo "🎯 EXPECTED RESULT: SUPERVISION_FORMAT\n";
echo "🚨 ACTUAL USER REPORT: DEFAULT_FORMAT\n\n";

echo "🔍 DEBUGGING HYPOTHESIS:\n";
echo "========================\n";
echo "If supervision installation works should use SUPERVISION_FORMAT but user gets DEFAULT_FORMAT,\n";
echo "then either:\n";
echo "1. The activity type is not 'ШМ_СдачаАвансовогоОтчета' when createTabelMessage is called\n";
echo "2. There's a bug in the array check or format condition\n";
echo "3. The activity type is being modified before reaching the format check\n\n";

echo "🎯 SOLUTION:\n";
echo "============\n";
echo "Add debug logging to createTabelMessage() to see exactly what activity type\n";
echo "is being passed and which format condition is being matched.\n\n";

echo "📝 IMMEDIATE ACTION NEEDED:\n";
echo "===========================\n";
echo "Add this debug log to the start of createTabelMessage():\n";
echo "error_log(\"TabelEntry: createTabelMessage called with activityType: '{\$activityType}'\");\n\n";

echo "And add debug logs before each format condition to see which path is taken.\n";

?>
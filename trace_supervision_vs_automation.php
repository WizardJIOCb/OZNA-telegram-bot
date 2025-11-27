<?php
/**
 * TRACE SUPERVISION VS AUTOMATION: Find the exact difference
 * This compares working automation equipment with broken supervision installation works
 */

echo "🔍 TRACING SUPERVISION VS AUTOMATION FLOW\n";
echo "==========================================\n\n";

echo "🎯 COMPARISON: WORKING vs BROKEN\n";
echo "================================\n\n";

echo "📋 AUTOMATION EQUIPMENT (WORKING):\n";
echo "-----------------------------------\n";
echo "1. Button click: 'Изменение схем подключения'\n";
echo "2. Callback sent: 'cat_ТабАвтом_ИзмСх'\n";
echo "3. Confirmation dialog: 'Изменение схем подключения (Автоматизация)'\n";
echo "4. User clicks 'Да': 'cat_ТабАвтом_ИзмСх_yes'\n";
echo "5. Report generated: '✅27.09.2025, Проект ОИ837: Добавлена запись в табель: 🔧 Изменение схем подключения (Автоматизация)'\n\n";

echo "📋 SUPERVISION INSTALLATION (BROKEN):\n";
echo "-------------------------------------\n";
echo "1. Button click: 'Сдача авансового отчета'\n";
echo "2. Callback sent: 'cat_ШМР_СдачаОтч'\n";
echo "3. Confirmation dialog: 'Сдача авансового отчета'\n";
echo "4. User clicks 'Да': 'cat_ШМР_СдачаОтч_yes'\n";
echo "5. Report generated: '✅ Запись в табель от 27.09.2025, в проект ОИ837 о добавлена' (BROKEN - no emoji, no activity)\n\n";

echo "🔧 TECHNICAL ANALYSIS:\n";
echo "======================\n\n";

echo "AUTOMATION EQUIPMENT PROCESSING:\n";
echo "1. handleActivityExecution() matches: /^cat_ТабАвтом_(.+)_yes$/\n";
echo "2. Maps 'ИзмСх' → 'ИзмСх_Автом'\n";
echo "3. TabelEntry.createTabelMessage() finds: 'ИзмСх_Автом' → '🔧 Изменение схем подключения (Автоматизация)'\n";
echo "4. Uses standard equipment format\n\n";

echo "SUPERVISION INSTALLATION PROCESSING:\n";
echo "1. handleActivityExecution() matches: /^cat_ШМР_(.+)_yes$/\n";
echo "2. Maps 'СдачаОтч' → 'ШМ_СдачаАвансовогоОтчета'\n";
echo "3. TabelEntry.createTabelMessage() finds: 'ШМ_СдачаАвансовогоОтчета' → '💰 Сдача авансового отчета'\n";
echo "4. Should use supervision installation format BUT...\n\n";

echo "🚨 ROOT CAUSE HYPOTHESIS:\n";
echo "=========================\n\n";

echo "The issue is likely in TabelEntry.createTabelMessage():\n";
echo "• Automation equipment activities use standard equipment message format\n";
echo "• Supervision installation works should use the same format\n";
echo "• But they're falling through to old format\n\n";

echo "CRITICAL QUESTION:\n";
echo "What format is supervision installation works actually getting?\n";
echo "Let's check if 'ШМ_СдачаАвансовогоОтчета' is in the supervision installation works array...\n\n";

// Read TabelEntry.php to check the format logic
$tabelEntryContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\TabelEntry.php');

echo "🔍 CHECKING TABELENTRY FORMAT LOGIC:\n";
echo "====================================\n\n";

// Find supervision installation works check
if (strpos($tabelEntryContent, '$supervisionInstallationWorks = [') !== false) {
    echo "✅ supervisionInstallationWorks array found\n";
    
    // Extract the array
    $start = strpos($tabelEntryContent, '$supervisionInstallationWorks = [');
    $end = strpos($tabelEntryContent, '];', $start);
    $arrayContent = substr($tabelEntryContent, $start, $end - $start + 2);
    
    echo "Array content:\n";
    echo $arrayContent . "\n\n";
    
    // Check if ШМ_СдачаАвансовогоОтчета is in the array
    if (strpos($arrayContent, 'ШМ_СдачаАвансовогоОтчета') !== false) {
        echo "✅ 'ШМ_СдачаАвансовогоОтчета' is in supervision installation works array\n";
        echo "✅ Should use supervision format\n\n";
    } else {
        echo "❌ 'ШМ_СдачаАвансовогоОтчета' is NOT in supervision installation works array\n";
        echo "❌ Will fall through to default format\n\n";
    }
} else {
    echo "❌ supervisionInstallationWorks array NOT found\n\n";
}

// Check the format condition
if (strpos($tabelEntryContent, 'if (in_array($activityType, $supervisionInstallationWorks))') !== false) {
    echo "✅ Format condition found: in_array(\$activityType, \$supervisionInstallationWorks)\n";
    
    // Find the format used
    $formatStart = strpos($tabelEntryContent, 'if (in_array($activityType, $supervisionInstallationWorks))');
    $formatBlock = substr($tabelEntryContent, $formatStart, 500);
    echo "Format block preview:\n";
    echo $formatBlock . "\n\n";
    
} else {
    echo "❌ Format condition NOT found\n\n";
}

echo "🎯 NEXT STEP:\n";
echo "=============\n";
echo "Check what format supervision installation works is actually using\n";
echo "and compare with automation equipment format.\n";

?>
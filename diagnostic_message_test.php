<?php
/**
 * DIAGNOSTIC: Show format comparison directly in bot
 */

echo "🔍 DIAGNOSTIC: Creating test messages for bot\n";
echo "=============================================\n\n";

// Read the actual TabelEntry.php to get real format logic
$content = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Create a test script that will send diagnostic messages to bot
$diagnosticScript = '<?php
// DIAGNOSTIC TEST - Shows format comparison directly in bot
require_once __DIR__ . "/CustomCommands/Services/TabelEntry.php";
require_once __DIR__ . "/CustomCommands/Services/DatabaseService.php";  
require_once __DIR__ . "/CustomCommands/Services/MessageService.php";

use CustomCommands\Services\TabelEntry;
use CustomCommands\Services\DatabaseService;
use CustomCommands\Services\MessageService;

// Test data
$testData = [
    "fio" => "Тестовый Пользователь",
    "date" => "27.09.2025", 
    "projectCode" => "ОИ837",
    "idleReason" => ""
];

try {
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $tabelEntry = new TabelEntry($databaseService, $messageService);

    echo "\\n🧪 ТЕСТ ФОРМАТОВ:\\n";
    echo "=================\\n";

    // Test 1: Automation (reference standard)
    $automationReport = $tabelEntry->createTabelMessage(
        $testData["fio"],
        $testData["date"], 
        $testData["projectCode"],
        "РабПоГр_Автом",
        $testData["idleReason"]
    );

    echo "\\n1️⃣ ЭТАЛОН (Автоматизация):\\n";
    echo "Тип: РабПоГр_Автом\\n";
    echo "Отчет: " . $automationReport . "\\n\\n";

    // Test 2: Supervision Installation Works  
    $supervisionReport = $tabelEntry->createTabelMessage(
        $testData["fio"],
        $testData["date"],
        $testData["projectCode"], 
        "ШМ_СдачаАвансовогоОтчета",
        $testData["idleReason"]
    );

    echo "2️⃣ ШЕФ-МОНТАЖ (должен быть как эталон):\\n";
    echo "Тип: ШМ_СдачаАвансовогоОтчета\\n";
    echo "Отчет: " . $supervisionReport . "\\n\\n";

    // Test 3: Another supervision activity
    $supervisionReport2 = $tabelEntry->createTabelMessage(
        $testData["fio"],
        $testData["date"],
        $testData["projectCode"],
        "ШМ_РабПоГрафику", 
        $testData["idleReason"]
    );

    echo "3️⃣ ШЕФ-МОНТАЖ (Работы по графику):\\n";
    echo "Тип: ШМ_РабПоГрафику\\n";
    echo "Отчет: " . $supervisionReport2 . "\\n\\n";

    // Analysis
    echo "📊 АНАЛИЗ:\\n";
    echo "==========\\n";
    
    if ($automationReport === $supervisionReport) {
        echo "✅ Форматы ИДЕНТИЧНЫ! Проблема не в логике форматирования.\\n";
    } else {
        echo "❌ Форматы ОТЛИЧАЮТСЯ!\\n";
        echo "\\nЭТАЛОН:\\n" . $automationReport . "\\n";
        echo "\\nШМР:\\n" . $supervisionReport . "\\n";
    }

    // Check if both use correct structure
    $automationHasCorrectFormat = strpos($automationReport, "Проект <b>") !== false;
    $supervisionHasCorrectFormat = strpos($supervisionReport, "Проект <b>") !== false;
    
    echo "\\nАвтоматизация использует правильный формат: " . ($automationHasCorrectFormat ? "✅ ДА" : "❌ НЕТ") . "\\n";
    echo "ШМР использует правильный формат: " . ($supervisionHasCorrectFormat ? "✅ ДА" : "❌ НЕТ") . "\\n";

} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\\n";
}
?>';

// Save the diagnostic script
file_put_contents(__DIR__ . '/run_diagnostic_test.php', $diagnosticScript);

echo "✅ Created diagnostic script: run_diagnostic_test.php\n";
echo "📋 This script will show format comparison directly\n\n";

echo "🎯 NEXT STEPS:\n";
echo "==============\n";
echo "1. I will run the diagnostic test\n";
echo "2. The results will show exactly what's different\n";
echo "3. We can fix the issue based on the results\n\n";

?>
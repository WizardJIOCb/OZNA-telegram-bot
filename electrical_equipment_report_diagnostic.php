<?php
echo "=== ДИАГНОСТИКА: Кнопка Электрооборудование не создает отчет ===\n\n";

echo "🔍 ПРОВЕРКА ПОЛНОГО ПОТОКА:\n";
echo "============================\n\n";

// Simulate the complete flow from button click to report generation
$steps = [
    1 => "User clicks '⚡ Электрооборудование' warranty button",
    2 => "Button sends callback: 'cat_Гарантии_ЭлектрОб_yes'", 
    3 => "CallbackqueryCommand early exit filter check",
    4 => "CallbackHandlerFactory routes to TabelHandler",
    5 => "TabelHandler.extractCategoryFromCallback processes callback",
    6 => "TabelHandler.isActivityExecution identifies as activity execution",
    7 => "TabelHandler.handleActivityExecution processes confirmation",
    8 => "TabelEntry.addEntry creates database record",
    9 => "MessageService.sendToHistoryChat sends report",
    10 => "User receives success confirmation"
];

echo "Ожидаемый поток выполнения:\n";
foreach ($steps as $num => $step) {
    echo "$num. $step\n";
}

echo "\n🔍 ПРОВЕРИМ КАЖДЫЙ ШАГ:\n";
echo "========================\n\n";

// Step 1: Check if warranty button exists and has correct callback
echo "Шаг 1: Проверка конфигурации кнопки\n";
echo "-----------------------------------\n";

// Check if MessageService has the warranty equipment keyboard method
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServicePath)) {
    $content = file_get_contents($messageServicePath);
    
    // Look for warranty equipment keyboard creation
    $hasWarrantyKeyboard = strpos($content, 'createWarrantyEquipmentKeyboard') !== false;
    echo "✓ createWarrantyEquipmentKeyboard method: " . ($hasWarrantyKeyboard ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    // Look for electrical equipment warranty button
    $hasElectricalButton = strpos($content, "Электрооборудование") !== false && 
                          strpos($content, "cat_Гарантии_ЭлектрОб") !== false;
    echo "✓ Electrical equipment warranty button: " . ($hasElectricalButton ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    if ($hasElectricalButton) {
        // Extract the exact button configuration
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (strpos($line, "Электрооборудование") !== false && strpos($line, "cat_Гарантии") !== false) {
                echo "Button config found at line " . ($i + 1) . ":\n";
                echo "  $line\n";
                break;
            }
        }
    }
} else {
    echo "❌ MessageService.php not found\n";
}

echo "\nШаг 2: Проверка фильтра CallbackqueryCommand\n";
echo "--------------------------------------------\n";

$callbackData = 'cat_Гарантии_ЭлектрОб_yes';
echo "Testing callback: '$callbackData'\n";

// Test the early exit filter
$passesFilter = (
    strpos($callbackData, 'category_') === 0 || 
    strpos($callbackData, 'adm_req_') === 0 || 
    strpos($callbackData, 'acc_lvl_') === 0 || 
    strpos($callbackData, 'cat_Таб') === 0 ||
    strpos($callbackData, 'proj_ctrl_') === 0 ||
    strpos($callbackData, 'proj_list_') === 0 ||
    strpos($callbackData, 'cat_Гарантии') === 0
);

echo "✓ Passes early exit filter: " . ($passesFilter ? "✅ YES" : "❌ NO") . "\n";

echo "\nШаг 3: Проверка обработки в TabelHandler\n";
echo "----------------------------------------\n";

$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Check if warranty confirmation mapping exists
    $hasWarrantyMapping = strpos($content, "'cat_Гарантии_ЭлектрОб_yes' => 'РабГарантии_ЭлектрОб_yes'") !== false;
    echo "✓ Warranty callback mapping: " . ($hasWarrantyMapping ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    // Check if isActivityExecution recognizes warranty callbacks
    $hasActivityExecution = strpos($content, "isActivityExecution") !== false;
    echo "✓ isActivityExecution method: " . ($hasActivityExecution ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    // Check if handleActivityExecution exists
    $hasHandleActivityExecution = strpos($content, "handleActivityExecution") !== false;
    echo "✓ handleActivityExecution method: " . ($hasHandleActivityExecution ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    // Check for warranty equipment confirmation pattern
    $hasWarrantyPattern = strpos($content, "cat_Гарантии_(.+)_yes") !== false;
    echo "✓ Warranty confirmation pattern: " . ($hasWarrantyPattern ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
} else {
    echo "❌ TabelHandler.php not found\n";
}

echo "\n🎯 ВОЗМОЖНЫЕ ПРОБЛЕМЫ:\n";
echo "======================\n";

$possibleIssues = [
    "Кнопка отправляет неправильный callback",
    "Callback не проходит через фильтр CallbackqueryCommand", 
    "TabelHandler не распознает warranty callback как activity execution",
    "Отсутствует mapping для warranty callback в extractCategoryFromCallback",
    "handleActivityExecution не обрабатывает warranty equipment типы",
    "addEntry не создает запись в базе данных",
    "sendToHistoryChat не отправляет сообщение",
    "Ошибка в конфигурации проекта или даты"
];

foreach ($possibleIssues as $i => $issue) {
    echo ($i + 1) . ". $issue\n";
}

echo "\n🔧 СЛЕДУЮЩИЕ ШАГИ ДИАГНОСТИКИ:\n";
echo "==============================\n";
echo "1. Проверить логи при нажатии кнопки\n";
echo "2. Убедиться что callback доходит до TabelHandler\n";  
echo "3. Проверить что isActivityExecution возвращает true\n";
echo "4. Проверить что handleActivityExecution вызывается\n";
echo "5. Проверить что addEntry успешно создает запись\n";
echo "6. Проверить что отчет отправляется в history chat\n";

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>
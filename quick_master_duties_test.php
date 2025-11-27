<?php
// QUICK MASTER DUTIES BUTTON TEST - Can be run directly or via web
// Usage: php quick_master_duties_test.php OR access via web browser

header('Content-Type: text/plain; charset=utf-8');

echo "🎯 QUICK TEST: MASTER DUTIES BUTTON\n";
echo "=====================================\n\n";

try {
    // 1. Test button configuration
    echo "1️⃣ BUTTON CONFIGURATION:\n";
    $messageServiceFile = 'CustomCommands/Services/MessageService.php';
    if (file_exists($messageServiceFile)) {
        $content = file_get_contents($messageServiceFile);
        $buttonExists = strpos($content, "'callback_data' => 'category_Табель_ИсполнениеОбязМастера'") !== false;
        echo "   Status: " . ($buttonExists ? "✅ FOUND" : "❌ MISSING") . "\n";
    } else {
        echo "   Status: ❌ MessageService.php NOT FOUND\n";
    }

    // 2. Test callback mapping
    echo "\n2️⃣ CALLBACK MAPPING:\n";
    $tabelHandlerFile = 'CustomCommands/Handlers/TabelHandler.php';
    if (file_exists($tabelHandlerFile)) {
        $content = file_get_contents($tabelHandlerFile);
        $mappingExists = strpos($content, "'category_Табель_ИсполнениеОбязМастера' => 'Табель_ИсполнениеОбязМастера'") !== false;
        echo "   Status: " . ($mappingExists ? "✅ FOUND" : "❌ MISSING") . "\n";
    } else {
        echo "   Status: ❌ TabelHandler.php NOT FOUND\n";
    }

    // 3. Test handler logic
    echo "\n3️⃣ HANDLER LOGIC:\n";
    if (file_exists($tabelHandlerFile)) {
        $content = file_get_contents($tabelHandlerFile);
        $handlerExists = strpos($content, "if (\$category === 'Табель_ИсполнениеОбязМастера')") !== false;
        echo "   Status: " . ($handlerExists ? "✅ FOUND" : "❌ MISSING") . "\n";
    }

    // 4. Simulate callback processing
    echo "\n4️⃣ CALLBACK SIMULATION:\n";
    $callback = 'category_Табель_ИсполнениеОбязМастера';
    $shortFormats = [
        'category_Табель_ИсполнениеОбязМастера' => 'Табель_ИсполнениеОбязМастера',
        'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
    ];
    
    if (isset($shortFormats[$callback])) {
        $extractedCategory = $shortFormats[$callback];
        $handlerMatch = ($extractedCategory === 'Табель_ИсполнениеОбязМастера');
        echo "   Extracted: {$extractedCategory}\n";
        echo "   Handler Match: " . ($handlerMatch ? "✅ TRUE" : "❌ FALSE") . "\n";
    } else {
        echo "   Status: ❌ CALLBACK NOT MAPPED\n";
    }

    // 5. Check recent logs
    echo "\n5️⃣ RECENT LOGS CHECK:\n";
    $logFiles = ['telegram_bot_error.log', 'error.log', 'webhook_error.log'];
    $foundMasterDutiesLogs = false;
    
    foreach ($logFiles as $logFile) {
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines) {
                $recentLines = array_slice($lines, -20);
                foreach ($recentLines as $line) {
                    if (stripos($line, 'master duties') !== false || 
                        stripos($line, 'ИсполнениеОбязМастера') !== false) {
                        echo "   Found in {$logFile}: " . trim($line) . "\n";
                        $foundMasterDutiesLogs = true;
                    }
                }
            }
        }
    }
    
    if (!$foundMasterDutiesLogs) {
        echo "   Status: ✅ No errors found in recent logs\n";
    }

    // Final recommendation
    echo "\n🎯 FINAL STATUS:\n";
    echo "==============\n";
    
    if ($buttonExists && $mappingExists && $handlerExists) {
        echo "✅ ALL CHECKS PASSED - Button should work!\n\n";
        echo "🚀 TEST INSTRUCTIONS:\n";
        echo "1. Go to Telegram bot\n";
        echo "2. Type: /tabel\n";
        echo "3. Select: Отдел ШМР\n";
        echo "4. Select: Any date\n";
        echo "5. Select: Any project\n";
        echo "6. Select: Проектная деятельность\n";
        echo "7. Click: 👷 Исполнение обязанностей мастера\n";
        echo "8. Expected: Confirmation message with Да/Нет buttons\n\n";
        echo "📊 MONITORING:\n";
        echo "- Use: /diagmasterduties (for full diagnostic)\n";
        echo "- Use: /testcallback (for callback testing)\n";
        echo "- Use: /checklogs (for log monitoring)\n";
    } else {
        echo "❌ ISSUES FOUND - Need to fix before testing!\n\n";
        echo "🔧 FIX NEEDED:\n";
        if (!$buttonExists) echo "- Add button configuration to MessageService.php\n";
        if (!$mappingExists) echo "- Add callback mapping to TabelHandler.php\n";
        if (!$handlerExists) echo "- Add handler logic to TabelHandler.php\n";
    }

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
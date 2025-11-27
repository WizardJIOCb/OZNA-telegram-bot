<?php
// Diagnostic test to identify why metrology reports aren't being generated

echo "=== METROLOGY DIAGNOSTIC TEST ===\n\n";

// 1. Check if the basic routing infrastructure is in place
echo "1. BASIC INFRASTRUCTURE CHECK:\n";
echo str_repeat("-", 40) . "\n";

// Check if TabelHandler exists and has required methods
$tabelHandlerFile = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    echo "✅ TabelHandler.php file exists\n";
    
    $content = file_get_contents($tabelHandlerFile);
    
    // Check for metrology-related methods
    $methodsToCheck = [
        'handleMetrologyMenu',
        'handleAdditionalWorksMetrologyMenu',
        'isActivityExecution',
        'handleActivityExecution'
    ];
    
    foreach ($methodsToCheck as $method) {
        if (strpos($content, "function {$method}") !== false || strpos($content, "private function {$method}") !== false) {
            echo "✅ Method {$method} exists\n";
        } else {
            echo "❌ Method {$method} MISSING\n";
        }
    }
    
    // Check for metrology pattern matching
    if (strpos($content, 'cat_ТабМетрол_(.+)_yes') !== false) {
        echo "✅ Metrology pattern matching exists\n";
    } else {
        echo "❌ Metrology pattern matching MISSING\n";
    }
    
    // Check for metrology activity mappings
    if (strpos($content, 'РабПоГр_Метрол') !== false) {
        echo "✅ Metrology activity mappings exist\n";
    } else {
        echo "❌ Metrology activity mappings MISSING\n";
    }
} else {
    echo "❌ TabelHandler.php file NOT FOUND\n";
}

echo "\n2. TABEL ENTRY SERVICE CHECK:\n";
echo str_repeat("-", 40) . "\n";

// Check TabelEntry.php
$tabelEntryFile = 'c:\ozna_bot\CustomCommands\Services\TabelEntry.php';
if (file_exists($tabelEntryFile)) {
    echo "✅ TabelEntry.php file exists\n";
    
    $content = file_get_contents($tabelEntryFile);
    
    // Check for metrology activity type mappings
    $metrologyMappings = [
        'РабПоГр_Метрол',
        'ФормИД_Метрол',
        'ПодтВех_Метрол',
        'ИзмКонф_Метрол',
        'КалПов_Метрол',
        'КМХ_Метрол',
        'КоррХар_Метрол',
        'Проч_Метрол'
    ];
    
    foreach ($metrologyMappings as $mapping) {
        if (strpos($content, $mapping) !== false) {
            echo "✅ Activity mapping {$mapping} exists\n";
        } else {
            echo "❌ Activity mapping {$mapping} MISSING\n";
        }
    }
} else {
    echo "❌ TabelEntry.php file NOT FOUND\n";
}

echo "\n3. MESSAGE SERVICE CHECK:\n";
echo str_repeat("-", 40) . "\n";

// Check MessageService.php
$messageServiceFile = 'c:\ozna_bot\CustomCommands\Services\MessageService.php';
if (file_exists($messageServiceFile)) {
    echo "✅ MessageService.php file exists\n";
    
    $content = file_get_contents($messageServiceFile);
    
    // Check for metrology keyboard methods
    $keyboardMethods = [
        'createMetrologyKeyboard',
        'createAdditionalWorksMetrologyKeyboard'
    ];
    
    foreach ($keyboardMethods as $method) {
        if (strpos($content, "function {$method}") !== false) {
            echo "✅ Method {$method} exists\n";
        } else {
            echo "❌ Method {$method} MISSING\n";
        }
    }
} else {
    echo "❌ MessageService.php file NOT FOUND\n";
}

echo "\n4. POTENTIAL ISSUES TO CHECK:\n";
echo str_repeat("-", 40) . "\n";

echo "If metrology functionality is implemented but reports aren't generated:\n\n";

echo "A. USER DATA ISSUES:\n";
echo "   - User might not have FIO set (use /fio command)\n";
echo "   - User might not have functional_direction set (use /profile)\n";
echo "   - User might not have project_code in conversation notes\n\n";

echo "B. CONVERSATION STATE ISSUES:\n";
echo "   - project_code might be missing from conversation->notes\n";
echo "   - tabel_data (date) might be missing\n";
echo "   - User might not have gone through project selection\n\n";

echo "C. DATABASE ISSUES:\n";
echo "   - DatabaseService->addUserTabel() might be failing\n";
echo "   - Database connection might be down\n";
echo "   - User permissions might be insufficient\n\n";

echo "D. ROUTING ISSUES:\n";
echo "   - Callback might not be reaching handleActivityExecution()\n";
echo "   - Pattern matching might not be working correctly\n";
echo "   - Activity type mapping might be incorrect\n\n";

echo "5. DEBUGGING RECOMMENDATIONS:\n";
echo str_repeat("-", 40) . "\n";

echo "To debug this issue:\n\n";

echo "1. Add error_log statements in:\n";
echo "   - handleActivityExecution() at the start\n";
echo "   - TabelEntry->addEntry() method\n";
echo "   - TabelEntry->createTabelMessage() method\n\n";

echo "2. Check the user's profile:\n";
echo "   - Verify FIO is set\n";
echo "   - Verify functional_direction is set\n";
echo "   - Check user access level\n\n";

echo "3. Test the conversation flow:\n";
echo "   - Start from equipment selection\n";
echo "   - Go through date selection\n";
echo "   - Go through project selection\n";
echo "   - Then try metrology activity\n\n";

echo "4. Check server logs for:\n";
echo "   - PHP errors\n";
echo "   - Database connection errors\n";
echo "   - Telegram API errors\n\n";

echo "6. TESTING SEQUENCE:\n";
echo str_repeat("-", 40) . "\n";

echo "To test metrology functionality:\n\n";
echo "1. User clicks 'Пусконаладочные работы' -> date selection\n";
echo "2. User selects date -> project selection\n";
echo "3. User selects project -> commissioning activities menu\n";
echo "4. User clicks 'Метрология' -> metrology menu\n";
echo "5. User clicks activity (e.g., 'Работы по графику') -> confirmation\n";
echo "6. User clicks 'Да' -> should generate report\n\n";

echo "If step 6 fails, the issue is likely in:\n";
echo "- handleActivityExecution() method\n";
echo "- TabelEntry->addEntry() method\n";
echo "- User data validation\n";
echo "- Database operations\n\n";

echo "Test completed. Check the items above to identify the issue.\n";
?>
<?php
/**
 * SERVICE TEST COMMAND FOR MASTER DUTIES BUTTON
 * Run this script to test the Master Duties button behavior
 * Usage: php test_master_duties_service.php
 */

echo "🔍 MASTER DUTIES BUTTON DIAGNOSTIC TEST\n";
echo "=====================================\n\n";

// Test 1: Check if button exists in MessageService
echo "1. CHECKING BUTTON EXISTENCE IN MESSAGESERVICE:\n";
$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $messageContent = file_get_contents($messageServiceFile);
    
    if (strpos($messageContent, 'Исполнение обязанностей мастера') !== false) {
        echo "   ✅ Button TEXT FOUND: 'Исполнение обязанностей мастера'\n";
        
        // Look for callback data
        if (preg_match("/'callback_data'\\s*=>\\s*'([^']*ТабИсполнОбяз[^']*)'|\"callback_data\"\\s*=>\\s*\"([^\"]*ТабИсполнОбяз[^\"]*)\"/", $messageContent, $matches)) {
            $callback = $matches[1] ?: $matches[2];
            echo "   📱 Callback FOUND: '$callback'\n";
        } else {
            echo "   ⚠️  Callback data NOT FOUND or different pattern\n";
        }
    } else {
        echo "   ❌ Button TEXT NOT FOUND in MessageService\n";
    }
} else {
    echo "   ❌ MessageService.php file not found\n";
}

echo "\n";

// Test 2: Check callback mapping in TabelHandler
echo "2. CHECKING CALLBACK MAPPING IN TABELHANDLER:\n";
$handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    // Look for callback mapping
    if (preg_match("/'ТабИсполнОбяз'\\s*=>\\s*'([^']+)'/", $handlerContent, $matches)) {
        echo "   ✅ Callback mapping FOUND: 'cat_ТабИсполнОбяз' → '{$matches[1]}'\n";
    } else {
        echo "   ❌ Callback mapping NOT FOUND for 'cat_ТабИсполнОбяз'\n";
    }
} else {
    echo "   ❌ TabelHandler.php file not found\n";
}

echo "\n";

// Test 3: Check if the critical fix is in place
echo "3. CHECKING CRITICAL FIX IN isActivityExecution():\n";
if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    if (strpos($handlerContent, "category === 'Табель_ИсполнениеОбязМастера'") !== false) {
        echo "   ✅ Critical exclusion fix FOUND in isActivityExecution()\n";
    } else {
        echo "   ❌ Critical exclusion fix NOT FOUND in isActivityExecution()\n";
    }
} else {
    echo "   ❌ TabelHandler.php file not found\n";
}

echo "\n";

// Test 4: Check if handler method exists
echo "4. CHECKING HANDLER METHOD:\n";
if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    if (strpos($handlerContent, 'function handleMasterDuties') !== false || 
        strpos($handlerContent, 'handleMasterDuties(') !== false) {
        echo "   ✅ handleMasterDuties() method FOUND\n";
    } else {
        echo "   ❌ handleMasterDuties() method NOT FOUND\n";
    }
} else {
    echo "   ❌ TabelHandler.php file not found\n";
}

echo "\n";

// Test 5: Check activity types array
echo "5. CHECKING ACTIVITY TYPES ARRAY:\n";
if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    if (strpos($handlerContent, "'ИсполнениеОбязМастера'") !== false) {
        echo "   ✅ 'ИсполнениеОбязМастера' FOUND in activityTypes\n";
    } else {
        echo "   ❌ 'ИсполнениеОбязМастера' NOT FOUND in activityTypes\n";
    }
} else {
    echo "   ❌ TabelHandler.php file not found\n";
}

echo "\n";

// Test 6: Check handleOtherWorkTypes method
echo "6. CHECKING handleOtherWorkTypes() METHOD:\n";
if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    if (strpos($handlerContent, 'handleOtherWorkTypes') !== false) {
        echo "   ✅ handleOtherWorkTypes() method FOUND\n";
        
        // Check if it handles Master Duties
        if (strpos($handlerContent, "category === 'Табель_ИсполнениеОбязМастера'") !== false || 
            strpos($handlerContent, "ИсполнениеОбязМастера') {") !== false) {
            echo "   ✅ Master Duties case FOUND in handleOtherWorkTypes()\n";
        } else {
            echo "   ❌ Master Duties case NOT FOUND in handleOtherWorkTypes()\n";
        }
    } else {
        echo "   ❌ handleOtherWorkTypes() method NOT FOUND\n";
    }
} else {
    echo "   ❌ TabelHandler.php file not found\n";
}

echo "\n";

// Test 7: Check canHandle method
echo "7. CHECKING canHandle() METHOD:\n";
if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    if (strpos($handlerContent, 'ТабИсполнОбяз') !== false) {
        echo "   ✅ 'ТабИсполнОбяз' pattern FOUND in canHandle()\n";
    } else {
        echo "   ❌ 'ТабИсполнОбяз' pattern NOT FOUND in canHandle()\n";
    }
} else {
    echo "   ❌ TabelHandler.php file not found\n";
}

echo "\n";

// Test 8: Provide test scenario  
echo "8. TEST SCENARIO FOR TELEGRAM:\n";
echo "   📱 Navigate to: /tabel → ШМР → Project → Проектная деятельность\n";
echo "   👆 Click: '👷 Исполнение обязанностей мастера'\n";
echo "   📝 Expected behavior: Should show date selection or work type options\n";
echo "   ❌ Problem behavior: Button doesn't respond or shows error\n";

echo "\n=====================================\n";
echo "🏁 DIAGNOSTIC COMPLETE\n";
echo "Please test the button in Telegram and report what you see!\n";
echo "\nAfter testing, please tell me:\n";
echo "1. Does the button appear in the menu?\n";
echo "2. What happens when you click it?\n";
echo "3. Do you see any error messages?\n";
echo "4. Does anything appear in the chat?\n";
?>
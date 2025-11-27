<?php
// Extended Real-Time Debug Test for RK Comments Elimination Issue

echo "=== EXTENDED RK COMMENTS ELIMINATION DEBUG TEST ===\n\n";

// Test 1: Verify TabelHandler RK handler exists and is positioned correctly
echo "1. TABELHANDLER RK HANDLER VERIFICATION:\n";
echo "=======================================\n";

$tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$content = file_get_contents($tabelHandlerFile);

// Search for RK handler in handleOtherWorkTypes
$searchPattern = '/Handle RK comments elimination menu.*?handleRKCommentsEliminationMenu/s';
if (preg_match($searchPattern, $content, $matches)) {
    echo "✅ RK handler found in handleOtherWorkTypes\n";
    $lines = explode("\n", $content);
    
    // Find the line number where RK handler is located
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'Handle RK comments elimination menu') !== false) {
            echo "   Location: Line " . ($lineNum + 1) . "\n";
            break;
        }
    }
} else {
    echo "❌ RK handler NOT found or improperly formatted\n";
}

// Check if RK handler is at the beginning (highest priority)
$handleOtherWorkTypesStart = strpos($content, 'private function handleOtherWorkTypes');
$rkHandlerPosition = strpos($content, 'Handle RK comments elimination menu');
$commissioningHandlerPosition = strpos($content, 'Табель_Пусконаладка');

if ($rkHandlerPosition && $commissioningHandlerPosition) {
    $rkPriority = $rkHandlerPosition - $handleOtherWorkTypesStart;
    $commissioningPriority = $commissioningHandlerPosition - $handleOtherWorkTypesStart;
    
    echo "   RK handler position offset: {$rkPriority}\n";
    echo "   Commissioning handler offset: {$commissioningPriority}\n";
    
    if ($rkPriority < $commissioningPriority) {
        echo "✅ RK handler has HIGHER priority than commissioning\n";
    } else {
        echo "❌ RK handler has LOWER priority - this is the problem!\n";
    }
}

echo "\n";

// Test 2: Check if activityTypes contains conflicting entries
echo "2. ACTIVITY TYPES CONFLICT CHECK:\n";
echo "=================================\n";

// Extract activityTypes array from the file
if (preg_match('/private array \$activityTypes\s*=\s*\[(.*?)\];/s', $content, $matches)) {
    $activityTypesString = $matches[1];
    
    $conflictingTypes = ['УЗ_РК', 'ТабУстрЗамРК', 'УстрЗамРК'];
    
    foreach ($conflictingTypes as $type) {
        if (strpos($activityTypesString, "'$type'") !== false) {
            echo "❌ CONFLICT: '{$type}' found in activityTypes array!\n";
            echo "   This causes RK button to be treated as activity confirmation\n";
        } else {
            echo "✅ '{$type}' NOT in activityTypes - good\n";
        }
    }
} else {
    echo "❌ Could not extract activityTypes array\n";
}

echo "\n";

// Test 3: Check extractCategoryFromCallback mapping
echo "3. CALLBACK MAPPING VERIFICATION:\n";
echo "=================================\n";

if (preg_match('/function extractCategoryFromCallback.*?\{(.*?)(?=function|\$)/s', $content, $matches)) {
    $extractFunction = $matches[1];
    
    $expectedMappings = [
        'cat_ТабУстрЗамРК' => 'ТабУстрЗамРК',
        'cat_ТабУстрЗамЭС' => 'ТабУстрЗамЭС'
    ];
    
    foreach ($expectedMappings as $callback => $expected) {
        if (strpos($extractFunction, "'$callback' => '$expected'") !== false) {
            echo "✅ Mapping found: '$callback' => '$expected'\n";
        } else {
            echo "❌ Mapping MISSING: '$callback' => '$expected'\n";
        }
    }
} else {
    echo "❌ Could not find extractCategoryFromCallback function\n";
}

echo "\n";

// Test 4: Check handleRKCommentsEliminationMenu method exists
echo "4. RK MENU METHOD VERIFICATION:\n";
echo "===============================\n";

if (strpos($content, 'function handleRKCommentsEliminationMenu') !== false) {
    echo "✅ handleRKCommentsEliminationMenu method exists\n";
    
    // Check if it calls MessageService correctly
    if (strpos($content, 'createEliminationRemarksRKKeyboard') !== false) {
        echo "✅ Method calls createEliminationRemarksRKKeyboard\n";
    } else {
        echo "❌ Method does NOT call createEliminationRemarksRKKeyboard\n";
    }
} else {
    echo "❌ handleRKCommentsEliminationMenu method NOT found\n";
}

echo "\n";

// Test 5: Check MessageService RK keyboard method
echo "5. MESSAGESERVICE RK KEYBOARD VERIFICATION:\n";
echo "===========================================\n";

$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $messageContent = file_get_contents($messageServiceFile);
    
    if (strpos($messageContent, 'function createEliminationRemarksRKKeyboard') !== false) {
        echo "✅ createEliminationRemarksRKKeyboard method exists in MessageService\n";
        
        // Check if it has all 6 buttons
        $expectedButtons = [
            'Раздел ЭС',
            'Раздел АТХ', 
            'Раздел ОВКВ',
            'Раздел ПС/ОС',
            'Раздел ТХ',
            'Прочее оборудование'
        ];
        
        $buttonCount = 0;
        foreach ($expectedButtons as $buttonText) {
            if (strpos($messageContent, $buttonText) !== false) {
                $buttonCount++;
                echo "✅ Button found: '$buttonText'\n";
            } else {
                echo "❌ Button MISSING: '$buttonText'\n";
            }
        }
        
        echo "   Total buttons found: {$buttonCount}/6\n";
    } else {
        echo "❌ createEliminationRemarksRKKeyboard method NOT found\n";
    }
} else {
    echo "❌ MessageService file not found\n";
}

echo "\n";

// Test 6: Real-time execution flow simulation
echo "6. REAL-TIME EXECUTION FLOW SIMULATION:\n";
echo "=======================================\n";

// Add debug logging to actual TabelHandler temporarily
$backupFile = $tabelHandlerFile . '.backup_' . date('YmdHis');
copy($tabelHandlerFile, $backupFile);
echo "✅ Created backup: " . basename($backupFile) . "\n";

// Insert debug logging at critical points
$debugCode = '
        // === URGENT RK DEBUG LOGGING ===
        error_log("=== RK DEBUG: handleOtherWorkTypes ENTRY ===");
        error_log("RK DEBUG: Input category: \'{$category}\'");
        error_log("RK DEBUG: Category length: " . strlen($category));
        error_log("RK DEBUG: Category bytes: " . bin2hex($category));
        
        // Check exact match for RK
        if ($category === \'cat_ТабУстрЗамРК\') {
            error_log("RK DEBUG: EXACT MATCH cat_ТабУстрЗамРК - should call handleRKCommentsEliminationMenu");
        } elseif ($category === \'ТабУстрЗамРК\') {
            error_log("RK DEBUG: EXACT MATCH ТабУстрЗамРК - should call handleRKCommentsEliminationMenu");
        } else {
            error_log("RK DEBUG: NO MATCH for RK category");
        }
        // === END RK DEBUG LOGGING ===
';

// Insert debug code right after the encoding fixes in handleOtherWorkTypes
$newContent = str_replace(
    'error_log("TabelHandler: URGENT DEBUG - after encoding fixes: \'{$category}\'");',
    'error_log("TabelHandler: URGENT DEBUG - after encoding fixes: \'{$category}\'");' . $debugCode,
    $content
);

file_put_contents($tabelHandlerFile, $newContent);
echo "✅ Added debug logging to TabelHandler\n";

echo "\n";

// Test 7: Instructions for real test
echo "7. REAL TEST INSTRUCTIONS:\n";
echo "==========================\n";
echo "Now perform the following steps:\n\n";
echo "1. In Telegram, go to commissioning works menu\n";
echo "2. Click the '🔧 Устранения замечаний РК' button\n";
echo "3. Check the server logs immediately after clicking\n";
echo "4. Look for lines starting with 'RK DEBUG:'\n\n";

echo "What to look for in logs:\n";
echo "✅ 'RK DEBUG: EXACT MATCH' - means category matching works\n";
echo "❌ 'RK DEBUG: NO MATCH' - means category is wrong\n";
echo "✅ 'routing to RK comments elimination sub-menu' - means handler called\n";
echo "❌ Any other routing message - means wrong handler called\n\n";

echo "After testing, I'll remove the debug code and restore the original file.\n";

echo "\n=== DEBUG SETUP COMPLETE ===\n";
echo "Backup created: " . basename($backupFile) . "\n";
echo "Debug logging added to TabelHandler.php\n";
echo "Ready for real-time testing!\n";
?>
<?php

// Deep diagnostic for automation scheme changes button flow

echo "=== DEEP DIAGNOSTIC: Automation Scheme Changes Button ===\n\n";

// Test 1: Check TabelHandler canHandle method
echo "1. TESTING CANHANDLE METHOD\n";
echo "===========================\n";

function simulateCanHandle($callbackData) {
    // Simulate the exact canHandle logic from TabelHandler
    $canHandle = str_starts_with($callbackData, 'category_Табель') || 
                 str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
                 str_starts_with($callbackData, 'cat_Таб') ||
                 $callbackData === 'новое_прочее_yes' ||
                 $callbackData === 'cat_ТабЭлОб_Проч_yes';
    return $canHandle;
}

// Test initial button
$initialCallback = 'cat_ТабАвтом_ИзмСх';
$confirmationCallback = 'cat_ТабАвтом_ИзмСх_yes';

echo "Initial button callback: '{$initialCallback}'\n";
echo "canHandle('{$initialCallback}'): " . (simulateCanHandle($initialCallback) ? 'TRUE' : 'FALSE') . "\n";

echo "Confirmation button callback: '{$confirmationCallback}'\n";
echo "canHandle('{$confirmationCallback}'): " . (simulateCanHandle($confirmationCallback) ? 'TRUE' : 'FALSE') . "\n\n";

// Test 2: Check category extraction
echo "2. TESTING CATEGORY EXTRACTION\n";
echo "===============================\n";

function simulateExtractCategory($callbackData) {
    // Simulate extractCategoryFromCallback logic
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format mappings
    $shortFormats = [
        'cat_ТабАвтом_ИзмСх' => 'ТабАвтом_ИзмСх',
        'cat_ТабАвтом_ИзмСх_yes' => 'ТабАвтом_ИзмСх_yes'
    ];
    
    if (isset($shortFormats[$callbackData])) {
        return $shortFormats[$callbackData];
    }
    
    // Fallback - remove 'cat_' prefix
    if (strpos($callbackData, 'cat_') === 0) {
        return substr($callbackData, strlen('cat_'));
    }
    
    return $callbackData;
}

$initialCategory = simulateExtractCategory($initialCallback);
$confirmationCategory = simulateExtractCategory($confirmationCallback);

echo "Initial category: '{$initialCategory}'\n";
echo "Confirmation category: '{$confirmationCategory}'\n\n";

// Test 3: Check confirmation callback recognition
echo "3. TESTING CONFIRMATION CALLBACK RECOGNITION\n";
echo "=============================================\n";

function simulateIsConfirmationCallback($category) {
    // Check for automation equipment short format confirmations
    if (preg_match('/^cat_ТабАвтом_.+_yes$/u', $category)) {
        return true;
    }
    
    // Check for pattern in extracted category
    if (preg_match('/^ТабАвтом_.+_yes$/u', $category)) {
        return true;
    }
    
    return str_contains($category, '_yes') && str_contains($category, 'Табель_');
}

$isConfirmationInitial = simulateIsConfirmationCallback($initialCallback);
$isConfirmationConfirm = simulateIsConfirmationCallback($confirmationCallback);
$isConfirmationExtracted = simulateIsConfirmationCallback($confirmationCategory);

echo "isConfirmationCallback('{$initialCallback}'): " . ($isConfirmationInitial ? 'TRUE' : 'FALSE') . "\n";
echo "isConfirmationCallback('{$confirmationCallback}'): " . ($isConfirmationConfirm ? 'TRUE' : 'FALSE') . "\n";
echo "isConfirmationCallback('{$confirmationCategory}'): " . ($isConfirmationExtracted ? 'TRUE' : 'FALSE') . "\n\n";

// Test 4: Check activity execution recognition
echo "4. TESTING ACTIVITY EXECUTION RECOGNITION\n";
echo "==========================================\n";

function simulateIsActivityExecution($category) {
    // Check if this is a menu callback that should NOT be treated as activity execution
    $menuCallbacks = [
        'cat_ТабЭлОб',
        'cat_ТабАвтом',
        'cat_ТабПожСигн',
        'Табель_Электрооборудование',
        'Табель_Автоматизация',
        'Табель_ПожарнаяСигнализация'
    ];
    
    if (in_array($category, $menuCallbacks)) {
        return false;
    }
    
    // Check standard activity execution
    if (str_contains($category, '_yes')) {
        // Handle short callback format for automation equipment
        if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $category, $matches)) {
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_Автом',
                'ФормИД' => 'ФормИД_Автом',
                'ДопРаб' => 'ДопРаб_Автом',
                'ПодтВех' => 'ПодтВех_Автом',
                'ИзмСх' => 'ИзмСх_Автом',
                'МонтКб' => 'МонтКб_Автом',
                'РаскКб' => 'РаскКб_Автом',
                'МонтАСУ' => 'МонтАСУ_Автом',
                'УдалПО' => 'УдалПО_Автом',
                'Проч' => 'Прочее_Автом'
            ];
            
            $shortActivityKey = $matches[1];
            if (isset($shortToFullMap[$shortActivityKey])) {
                return true;
            }
        }
        
        // Also check extracted category format
        if (preg_match('/^ТабАвтом_(.+)_yes$/u', $category, $matches)) {
            return true; // Simplified check
        }
    }
    
    return false;
}

$isActivityExecutionConfirm = simulateIsActivityExecution($confirmationCallback);
$isActivityExecutionExtracted = simulateIsActivityExecution($confirmationCategory);

echo "isActivityExecution('{$confirmationCallback}'): " . ($isActivityExecutionConfirm ? 'TRUE' : 'FALSE') . "\n";
echo "isActivityExecution('{$confirmationCategory}'): " . ($isActivityExecutionExtracted ? 'TRUE' : 'FALSE') . "\n\n";

// Test 5: Check routing logic
echo "5. TESTING ROUTING LOGIC\n";
echo "========================\n";

function simulateRouting($category) {
    // Check confirmation callback
    if (simulateIsConfirmationCallback($category)) {
        if (simulateIsActivityExecution($category)) {
            return "Route to handleActivityExecution()";
        } else {
            return "Route to confirmation handler";
        }
    }
    
    // Check initial button
    if ($category === 'cat_ТабАвтом_ИзмСх' || $category === 'ТабАвтом_ИзмСх') {
        return "Route to scheme changes handler (show confirmation)";
    }
    
    return "Route to unknown command";
}

$initialRouting = simulateRouting($initialCallback);
$confirmationRouting = simulateRouting($confirmationCallback);
$extractedRouting = simulateRouting($confirmationCategory);

echo "Routing for '{$initialCallback}': {$initialRouting}\n";
echo "Routing for '{$confirmationCallback}': {$confirmationRouting}\n";
echo "Routing for '{$confirmationCategory}': {$extractedRouting}\n\n";

// Test 6: Summary and diagnosis
echo "6. DIAGNOSTIC SUMMARY\n";
echo "=====================\n";

$allWorking = simulateCanHandle($confirmationCallback) && 
              (simulateIsConfirmationCallback($confirmationCallback) || simulateIsConfirmationCallback($confirmationCategory)) &&
              (simulateIsActivityExecution($confirmationCallback) || simulateIsActivityExecution($confirmationCategory));

if ($allWorking) {
    echo "✅ ALL CHECKS PASS - The automation scheme changes button should work correctly!\n";
} else {
    echo "❌ ISSUES DETECTED:\n";
    
    if (!simulateCanHandle($confirmationCallback)) {
        echo "   - canHandle() fails for confirmation callback\n";
    }
    
    if (!simulateIsConfirmationCallback($confirmationCallback) && !simulateIsConfirmationCallback($confirmationCategory)) {
        echo "   - isConfirmationCallback() fails for both raw and extracted categories\n";
    }
    
    if (!simulateIsActivityExecution($confirmationCallback) && !simulateIsActivityExecution($confirmationCategory)) {
        echo "   - isActivityExecution() fails for both raw and extracted categories\n";
    }
}

echo "\n7. ACTUAL FILE CHECK\n";
echo "====================\n";

// Check if the actual TabelHandler.php file has the patterns we expect
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "Checking TabelHandler.php for automation patterns:\n";

// Check canHandle method
if (strpos($tabelHandlerContent, "str_starts_with(\$callbackData, 'cat_Таб')") !== false) {
    echo "✅ canHandle method has 'cat_Таб' pattern\n";
} else {
    echo "❌ canHandle method missing 'cat_Таб' pattern\n";
}

// Check isConfirmationCallback method
if (strpos($tabelHandlerContent, "preg_match('/^cat_ТабАвтом_.+_yes$/u'") !== false) {
    echo "✅ isConfirmationCallback has automation pattern\n";
} else {
    echo "❌ isConfirmationCallback missing automation pattern\n";
}

// Check isActivityExecution method
if (strpos($tabelHandlerContent, "preg_match('/^cat_ТабАвтом_(.+)_yes$/u'") !== false) {
    echo "✅ isActivityExecution has automation pattern\n";
} else {
    echo "❌ isActivityExecution missing automation pattern\n";
}

// Check automation scheme changes handler
if (strpos($tabelHandlerContent, "cat_ТабАвтом_ИзмСх") !== false) {
    echo "✅ Automation scheme changes handler exists\n";
} else {
    echo "❌ Automation scheme changes handler missing\n";
}

echo "\nDiagnostic complete.\n";

?>
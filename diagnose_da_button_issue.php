<?php

// Comprehensive diagnosis script for "Да" button not sending reports

echo "=== COMPREHENSIVE 'ДА' BUTTON DIAGNOSIS ===\n\n";

// Load the required files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Get the TabelHandler instance (via reflection to access private methods)
try {
    $reflection = new ReflectionClass(TabelHandler::class);
    
    // Read the actual source code to analyze patterns
    $sourceFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
    $sourceCode = file_get_contents($sourceFile);
    
    echo "1. CHECKING COMMON CONFIRMATION PATTERNS\n";
    echo "========================================\n";
    
    // Test different confirmation callback patterns
    $testCallbacks = [
        // Supervision project activities (from your issue report)
        'category_Табель_ШМ_УчастиеВЗПИ_yes',
        'category_Табель_ШМ_РазрПрограммы_yes',
        
        // Electrical equipment
        'cat_ТабЭлОб_РабПоГр_yes',
        'category_Табель_РабПоГр_ЭлектрОб_yes',
        
        // Automation equipment
        'cat_ТабАвтом_ИзмСх_yes',
        'category_Табель_ИзмСх_Автом_yes',
        
        // Warranty equipment
        'cat_Гарантии_ЭлектрОб_yes',
        'category_Табель_РабГарантии_ЭлектрОб_yes',
        
        // Commissioning works
        'cat_ТабИнстр_yes',
        'category_Табель_Инструктаж_yes',
        
        // Fire alarm equipment
        'cat_ТабПожСигн_РабПоГр_yes',
        'category_Табель_РабПоГр_ПожСигн_yes'
    ];
    
    foreach ($testCallbacks as $callback) {
        echo "Testing callback: $callback\n";
        
        // 1. Check if it contains supervision project pattern
        if (preg_match('/^(category_)?Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/', $callback)) {
            echo "  ✅ Matches supervision project activity pattern\n";
        }
        
        // 2. Check if it contains electrical equipment pattern
        elseif (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $callback)) {
            echo "  ✅ Matches electrical equipment short pattern\n";
        }
        
        // 3. Check if it contains automation equipment pattern
        elseif (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $callback)) {
            echo "  ✅ Matches automation equipment short pattern\n";
        }
        
        // 4. Check if it contains warranty equipment pattern
        elseif (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback)) {
            echo "  ✅ Matches warranty equipment short pattern\n";
        }
        
        // 5. Check if it contains commissioning short pattern
        elseif (preg_match('/^cat_(.+)_yes$/', $callback)) {
            $shortKey = preg_replace('/^cat_(.+)_yes$/', '$1', $callback);
            $basicCommissioningActivities = [
                'ТабИнстр', 'ТабДорога', 'ТабПодгПоезд', 'ТабСдачаОтч',
                'ТабОхрСигн', 'ТабОтоплВент', 'ТабТехОб', 'ТабМетрол',
                'ТабКомплОпр', 'ТабРежНал'
            ];
            if (in_array($shortKey, $basicCommissioningActivities)) {
                echo "  ✅ Matches commissioning works short pattern\n";
            } else {
                echo "  ❌ No matching pattern found for short key: $shortKey\n";
            }
        }
        
        // 6. Check if it contains fire alarm equipment pattern
        elseif (preg_match('/^cat_ТабПожСигн_(.+)_yes$/u', $callback)) {
            echo "  ✅ Matches fire alarm equipment short pattern\n";
        }
        
        else {
            echo "  ❌ NO MATCHING PATTERN FOUND\n";
        }
        
        echo "\n";
    }
    
    echo "2. ANALYZING isActivityExecution METHOD\n";
    echo "======================================\n";
    
    // Check if the patterns exist in the source code
    $patterns = [
        'supervision_project' => '/ШМ_УчастиеВЗПИ\|ШМ_РазрПрограммы\|ШМ_РазрГрафика\|ШМ_ОргДопоставки\|ШМ_УчастиеВОПИ\|ШМ_ИспобязМастера/',
        'electrical_equipment' => '/cat_ТабЭлОб_\(\.\+\)_yes/',
        'automation_equipment' => '/cat_ТабАвтом_\(\.\+\)_yes/',
        'warranty_equipment' => '/cat_Гарантии_\(\.\+\)_yes/',
        'commissioning_works' => '/basicCommissioningActivities/',
        'fire_alarm' => '/cat_ТабПожСигн_\(\.\+\)_yes/'
    ];
    
    foreach ($patterns as $name => $pattern) {
        if (preg_match($pattern, $sourceCode)) {
            echo "  ✅ Found $name pattern in source code\n";
        } else {
            echo "  ❌ Missing $name pattern in source code\n";
        }
    }
    
    echo "\n3. TESTING ACTUAL METHOD EXECUTION\n";
    echo "==================================\n";
    
    // Try to check if the methods exist and can be called
    $methodsToCheck = [
        'canHandle',
        'isActivityExecution', 
        'isConfirmationCallback',
        'handleActivityExecution',
        'extractCategoryFromCallback'
    ];
    
    foreach ($methodsToCheck as $methodName) {
        if ($reflection->hasMethod($methodName)) {
            echo "  ✅ Method $methodName exists\n";
        } else {
            echo "  ❌ Method $methodName missing\n";
        }
    }
    
    echo "\n4. PATTERN COVERAGE ANALYSIS\n";
    echo "============================\n";
    
    // Analyze which patterns are covered in isActivityExecution
    if (strpos($sourceCode, 'ШМ_УчастиеВЗПИ') !== false) {
        echo "  ✅ Supervision project activities pattern found\n";
    } else {
        echo "  ❌ CRITICAL: Supervision project activities pattern missing!\n";
    }
    
    if (strpos($sourceCode, 'cat_ТабЭлОб_(.+)_yes') !== false) {
        echo "  ✅ Electrical equipment confirmation pattern found\n";
    } else {
        echo "  ❌ CRITICAL: Electrical equipment confirmation pattern missing!\n";
    }
    
    if (strpos($sourceCode, 'cat_Гарантии_(.+)_yes') !== false) {
        echo "  ✅ Warranty equipment confirmation pattern found\n";
    } else {
        echo "  ❌ CRITICAL: Warranty equipment confirmation pattern missing!\n";
    }
    
    echo "\n5. SUMMARY AND RECOMMENDATIONS\n";
    echo "==============================\n";
    
    echo "Based on this analysis, here are the most likely issues:\n\n";
    
    echo "A. If supervision project activities fail:\n";
    echo "   - Check line ~1228 in TabelHandler.php for supervision pattern\n";
    echo "   - Pattern should be: '^(category_)?Табель_(ШМ_УчастиеВЗПИ|...)_yes$'\n\n";
    
    echo "B. If electrical equipment fails:\n";
    echo "   - Check line ~1270 in TabelHandler.php for electrical pattern\n";
    echo "   - Pattern should be: '^cat_ТабЭлОб_(.+)_yes$'\n\n";
    
    echo "C. If warranty equipment fails:\n";
    echo "   - Check line ~1450 in TabelHandler.php for warranty pattern\n";
    echo "   - Pattern should be: '^cat_Гарантии_(.+)_yes$'\n\n";
    
    echo "D. If commissioning works fail:\n";
    echo "   - Check line ~1350 in TabelHandler.php for commissioning pattern\n";
    echo "   - Pattern should be: '^cat_(.+)_yes$' with basicCommissioningActivities array\n\n";
    
} catch (Exception $e) {
    echo "Error during analysis: " . $e->getMessage() . "\n";
}

echo "\n📞 WHAT TO TEST NEXT:\n";
echo "====================\n";
echo "1. Try clicking any 'Да' button and see what error appears\n";
echo "2. Check the Telegram bot logs for error messages\n";
echo "3. Try different equipment types to see if the issue is specific\n";
echo "4. Let me know which specific button combinations don't work\n\n";

echo "This diagnosis will help pinpoint the exact missing pattern!\n";
?>
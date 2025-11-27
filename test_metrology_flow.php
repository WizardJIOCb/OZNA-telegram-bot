<?php

// Test the complete metrology flow to identify where it breaks

echo "=== COMPREHENSIVE METROLOGY FLOW TEST ===\n\n";

// Step 1: User clicks "Метрология" button
echo "1. User clicks 'Метрология' button\n";
echo "   Button callback: 'cat_ТабМетрол'\n";

// Step 2: Category extraction
function extractCategoryFromCallback(string $callbackData): string 
{
    // Category mapping for short callback formats
    $callbackMappings = [
        'cat_ТабМетрол' => 'Табель_Метрология',
        'cat_ТабМетрол_РабПоГр' => 'Табель_РабПоГр_Метрол',
    ];
    
    // Check for mapped callback first
    if (isset($callbackMappings[$callbackData])) {
        return $callbackMappings[$callbackData];
    }
    
    // Remove 'cat_' prefix if present
    if (str_starts_with($callbackData, 'cat_')) {
        return substr($callbackData, 4);
    }
    
    return $callbackData;
}

$metrologyMainCallback = 'cat_ТабМетрол';
$extractedMain = extractCategoryFromCallback($metrologyMainCallback);
echo "   Extracted category: '{$extractedMain}'\n";

// Check if this goes to handleMetrologyMenu
$metrologyMenuHandlers = ['cat_ТабМетрол', 'Табель_Метрология'];
$handlesMain = in_array($extractedMain, $metrologyMenuHandlers) || in_array($metrologyMainCallback, $metrologyMenuHandlers);
echo "   Handles main menu: " . ($handlesMain ? 'YES' : 'NO') . "\n";

if ($handlesMain) {
    echo "   → Calls handleMetrologyMenu() - shows metrology menu\n\n";
} else {
    echo "   → ERROR: Would not handle main metrology button!\n\n";
}

// Step 3: User clicks "Работы по графику" in metrology menu
echo "2. User clicks 'Работы по графику' in metrology menu\n";
$scheduleWorkCallback = 'cat_ТабМетрол_РабПоГр';
echo "   Button callback: '{$scheduleWorkCallback}'\n";

$extractedSchedule = extractCategoryFromCallback($scheduleWorkCallback);
echo "   Extracted category: '{$extractedSchedule}'\n";

// Check if this is handled by metrology schedule works handler
$scheduleHandlers = ['ТабМетрол_РабПоГр', 'cat_ТабМетрол_РабПоГр', 'Табель_РабПоГр_Метрол'];
$handlesSchedule = in_array($extractedSchedule, $scheduleHandlers);
echo "   Handler looks for: " . implode(', ', $scheduleHandlers) . "\n";
echo "   Extracted matches handler: " . ($handlesSchedule ? 'YES' : 'NO') . "\n";

if ($handlesSchedule) {
    echo "   → Should show confirmation dialog\n\n";
} else {
    echo "   → ERROR: Would show 'Неизвестная команда табеля'!\n\n";
}

// Step 4: Let's check what might be different in the actual execution
echo "3. Debugging potential issues:\n";

// Check if there might be an order issue - are other handlers intercepting first?
echo "   a) Order of handlers in routeTabelCallback:\n";
echo "      - Electrical equipment (IMMEDIATE) - checks 'cat_ТабЭлОб' or 'Табель_Электрооборудование'\n";
echo "      - Fire alarm (IMMEDIATE) - checks 'cat_ТабПожСигн' or 'Табель_ПожарнаяСигнализация'\n";
echo "      - Metrology (IMMEDIATE) - checks 'cat_ТабМетрол' or 'Табель_Метрология'\n";
echo "      - Main tabel menu - checks 'Табель'\n";
echo "      - Date selection, Project selection, etc.\n";
echo "      - Activity execution, Activity confirmation\n";
echo "      - handleOtherWorkTypes (FALLBACK)\n\n";

echo "   b) Metrology schedule callback should reach handleOtherWorkTypes where it's handled\n\n";

// Step 5: Check conversation context requirements
echo "4. Conversation context check:\n";
echo "   Metrology schedule handler expects:\n";
echo "   - conversation->notes['project_code'] ?? 'Unknown'\n";
echo "   This should work even if project_code is not set (defaults to 'Unknown')\n\n";

// Step 6: Check for potential conflicts
echo "5. Potential conflict analysis:\n";
echo "   Could 'cat_ТабМетрол_РабПоГр' be intercepted by:\n";
echo "   - isActivityExecution() - probably not, this checks for '_yes' suffix\n";
echo "   - isActivityConfirmation() - probably not, this also checks for specific patterns\n";
echo "   - Other equipment handlers - no, they check for different prefixes\n\n";

echo "6. Conclusion:\n";
echo "   The routing logic looks correct. The issue might be:\n";
echo "   a) Encoding problem with Cyrillic characters\n";
echo "   b) Handler order issue (but metrology is high priority)\n";
echo "   c) An exception or error in the metrology handler itself\n";
echo "   d) The conversation context is in an unexpected state\n\n";

echo "   Next step: Add detailed logging to trace actual execution flow\n";

echo "=== END COMPREHENSIVE TEST ===\n";
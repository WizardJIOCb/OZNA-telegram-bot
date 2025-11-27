<?php

// Simple test to verify project callback detection
echo "=== PROJECT CALLBACK DETECTION TEST ===\n\n";

// Test project selection callback detection
$testCallbacks = [
    'project_ПКПО-24',
    'project_TEST-01', 
    'project_АБВ-123',
    'category_Табель_ШМ_Сегодня',
    'category_Табель_Шеф_монтаж'
];

echo "Testing project callback detection:\n";

foreach ($testCallbacks as $callback) {
    echo "\nTesting: '{$callback}'\n";
    
    // Check if this looks like a project selection
    $isProjectSelection = strpos($callback, 'project_') === 0;
    echo "  Starts with 'project_': " . ($isProjectSelection ? 'YES' : 'NO') . "\n";
    
    if ($isProjectSelection) {
        $projectCode = substr($callback, strlen('project_'));
        echo "  Extracted project code: '{$projectCode}'\n";
        echo "  ✓ Should route to handleProjectSelection()\n";
    }
    
    // Check if TabelHandler would handle this
    $tabelCanHandle = (
        strpos($callback, 'category_') === 0 ||
        strpos($callback, 'project_') === 0 ||
        strpos($callback, 'cat_') === 0
    );
    echo "  TabelHandler canHandle: " . ($tabelCanHandle ? 'YES' : 'NO') . "\n";
}

echo "\n=== SUPERVISION FLOW SEQUENCE TEST ===\n";

$supervisionFlow = [
    'category_Табель',                           // Main menu
    'category_Табель_Шеф_монтаж',               // Supervision department  
    'category_Табель_ШМ_Сегодня',               // Date selection
    'project_ПКПО-24'                           // Project selection <- THIS IS WHERE IT BREAKS
];

echo "Testing full supervision flow sequence:\n";

foreach ($supervisionFlow as $i => $callback) {
    $step = $i + 1;
    echo "\nStep {$step}: '{$callback}'\n";
    
    // Test what should happen at each step
    switch ($step) {
        case 1:
            echo "  ✓ Should show work type menu (ШМР, ПНР, etc.)\n";
            break;
        case 2:
            echo "  ✓ Should route to handleSupervisionDateSelection()\n";
            echo "  ✓ Should show date selection menu\n";
            break;
        case 3:
            echo "  ✓ Should route to handleDateSelection()\n";
            echo "  ✓ Should set is_supervision = true\n";
            echo "  ✓ Should show project selection menu\n";
            break;
        case 4:
            echo "  ✓ Should route to handleProjectSelection()\n";
            echo "  ✓ Should check is_supervision flag\n";
            echo "  ✓ Should show supervision activities menu\n";
            echo "  ❌ THIS IS WHERE THE ISSUE OCCURS!\n";
            break;
    }
    
    // Check basic routing
    $startsWithCategory = strpos($callback, 'category_') === 0;
    $startsWithProject = strpos($callback, 'project_') === 0;
    
    echo "  Starts with 'category_': " . ($startsWithCategory ? 'YES' : 'NO') . "\n";
    echo "  Starts with 'project_': " . ($startsWithProject ? 'YES' : 'NO') . "\n";
    
    if ($startsWithProject) {
        echo "  🚨 CRITICAL: This is where the supervision flow breaks!\n";
        echo "  🚨 The project selection callback is not being processed!\n";
        
        // Check what could be wrong
        echo "\n  POSSIBLE ISSUES:\n";
        echo "  1. ProjectSelector->isProjectSelection() returns false\n";
        echo "  2. Conversation state (is_supervision flag) is lost\n";
        echo "  3. HandleProjectSelection method is not called\n";
        echo "  4. Exception occurs during project selection processing\n";
        echo "  5. Bot webhook is not receiving the callback\n";
    }
}

echo "\n=== RECOMMENDATION ===\n";
echo "The issue is likely that the project selection callback 'project_ПКПО-24' is either:\n";
echo "1. Not being received by the bot (webhook issue)\n";
echo "2. Not being detected as a project selection\n";
echo "3. Not being routed to handleProjectSelection()\n";
echo "4. Conversation state is lost between callbacks\n";
echo "\nTo debug: Add logging to see if 'project_*' callbacks are received at all.\n";

echo "\nDONE\n";
?>
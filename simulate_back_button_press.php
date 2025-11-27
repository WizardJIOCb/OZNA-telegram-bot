<?php
/**
 * Simulate back button press to test routing
 */

echo "=== SIMULATE BACK BUTTON PRESS ===\n\n";

// Test the routing logic as it would happen in TabelHandler::routeTabelCallback

$category = 'Табель_ШМ_Back';
echo "Processing category: {$category}\n";

// Check if this matches the back button routing condition
if ($category === 'Табель_ШМ_Back') {
    echo "✅ MATCHED - Would route to handleBackToSupervision\n";
    echo "This should show the supervision keyboard\n";
} else {
    echo "❌ NO MATCH - Would not route to handleBackToSupervision\n";
    
    // Let's check other conditions that might interfere
    echo "\nChecking other conditions:\n";
    
    // Check if it's a main tabel menu
    $isMainTabelMenu = in_array($category, ['Табель', 'Табель_Мобильная_Бригада_Дата']);
    echo "Is main tabel menu: " . ($isMainTabelMenu ? 'YES' : 'NO') . "\n";
    
    // Check if it's an idle menu callback
    $isIdleMenuCallback = $category === 'Табель_Простой' || 
                          $category === 'Табель_ШМ_Простой' ||
                          str_contains($category, '_Простой_') ||
                          str_contains($category, '_ШМ_Простой_');
    echo "Is idle menu callback: " . ($isIdleMenuCallback ? 'YES' : 'NO') . "\n";
    
    // Check if it's an activity execution
    $isActivityExecution = str_contains($category, '_yes') && str_contains($category, 'Табель_');
    echo "Is activity execution: " . ($isActivityExecution ? 'YES' : 'NO') . "\n";
}

echo "\nTest completed.\n";
?>
<?php

// Test script to diagnose both ШМР issues
echo "=== TESTING BOTH ШМР ISSUES ===\n\n";

echo "🔍 ISSUE 1: 'Работы по графику' Auto-Execution\n";
echo "===============================================\n";

// Test what might be causing auto-execution
$scheduleCallbacks = [
    'cat_ШМР_ВхКонтр_РабПоГр',     // Schedule button
    'cat_ШМР_ВхКонтр_РабПоГр_yes', // Confirmation button
];

foreach ($scheduleCallbacks as $callback) {
    echo "Testing: '{$callback}'\n";
    
    // Test if it matches the execution pattern
    if (preg_match('/^cat_ШМР_(.+)_РабПоГр_yes$/u', $callback)) {
        echo "  ✅ MATCHES execution pattern - would auto-execute\n";
    } else {
        echo "  ❌ Does NOT match execution pattern - should show confirmation\n";
    }
    
    // Test if it matches the confirmation pattern  
    if (preg_match('/^cat_ШМР_(.+)_РабПоГр$/u', $callback)) {
        echo "  ✅ MATCHES confirmation pattern - should show 'Да/Нет' dialog\n";
    } else {
        echo "  ❌ Does NOT match confirmation pattern\n";
    }
    echo "\n";
}

echo "🔍 ISSUE 2: 'Подтвердить достижение вехи' Silent\n";
echo "==================================================\n";

// Test milestone confirmation flow
$milestoneCallbacks = [
    'cat_ШМР_ПодтВех',     // General milestone button (what you're clicking)
    'cat_ШМР_ПодтВех_yes', // Confirmation button
];

foreach ($milestoneCallbacks as $callback) {
    echo "Testing: '{$callback}'\n";
    
    // Test simple supervision pattern
    $pattern = '/^ШМР_(.+)_yes$/u';
    $cleanCallback = str_replace('cat_', '', $callback);
    
    if (preg_match($pattern, $cleanCallback, $matches)) {
        echo "  ✅ MATCHES simple supervision pattern: '{$matches[1]}'\n";
        
        $simpleSupervisionMap = [
            'АналФунд' => 'ПроверкаМонтажнойПлощадки',
            'ПодпАкт' => 'ПодписаниеАктаШМР',
            'ПодтВех' => 'ПодтверждениеВехи_ШМР'
        ];
        
        if (isset($simpleSupervisionMap[$matches[1]])) {
            echo "  ✅ Found in mapping: '{$simpleSupervisionMap[$matches[1]]}'\n";
        } else {
            echo "  ❌ NOT FOUND in mapping\n";
        }
    } else {
        echo "  ❌ Does NOT match simple supervision pattern\n";
    }
    echo "\n";
}

echo "🎯 DIAGNOSIS SUMMARY:\n";
echo "=====================\n";

echo "Issue 1 - Auto-execution:\n";
echo "- 'cat_ШМР_ВхКонтр_РабПоГр' should show confirmation dialog\n";
echo "- 'cat_ШМР_ВхКонтр_РабПоГр_yes' should create timesheet entry\n\n";

echo "Issue 2 - Silent milestone:\n";
echo "- 'cat_ШМР_ПодтВех_yes' should now work with the mapping fix\n";
echo "- Pattern '/^ШМР_(.+)_yes$/u' should extract 'ПодтВех'\n";
echo "- 'ПодтВех' should map to 'ПодтверждениеВехи_ШМР'\n";

echo "\n🔧 NEXT STEPS:\n";
echo "==============\n";
echo "1. Test the milestone confirmation button again\n";
echo "2. Check if the 'Работы по графику' button is different than expected\n";
echo "3. Verify which exact buttons you're clicking\n";

echo "\nTest complete!\n";
<?php

echo "🔍 TESTING FIXED ШМР SITE INSPECTION CALLBACK PATTERN\n";
echo "====================================================\n\n";

// Test the callback as it would be passed to isActivityExecution
$testCallback = 'cat_ШМР_АналФунд_yes';
echo "🎯 Original callback: '{$testCallback}'\n";

// Extract category (remove 'cat_' prefix) as done in routeTabelCallback
$category = str_replace('cat_', '', $testCallback);
echo "📋 Extracted category: '{$category}'\n";
echo "Category length: " . strlen($category) . "\n";
echo "Category hex: " . bin2hex($category) . "\n\n";

// Test the FIXED regex pattern (without cat_ prefix)
echo "🔧 Testing FIXED regex pattern:\n";
$fixedPattern = '/^ШМР_(.+)_yes$/u';
echo "Fixed pattern: {$fixedPattern}\n";

if (preg_match($fixedPattern, $category, $matches)) {
    echo "✅ MATCHES Fixed Pattern!\n";
    echo "Full match: '{$matches[0]}'\n";
    echo "Captured group: '{$matches[1]}'\n";
    
    // Check the simple supervision mapping
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР'
    ];
    
    $shortActivityKey = $matches[1];
    echo "Short activity key: '{$shortActivityKey}'\n";
    
    if (isset($simpleSupervisionMap[$shortActivityKey])) {
        echo "✅ Key found in simple supervision map!\n";
        echo "Mapped to activity: '{$simpleSupervisionMap[$shortActivityKey]}'\n";
        echo "\n🎉 SUCCESS! The callback should now work correctly!\n";
    } else {
        echo "❌ Key NOT found in simple supervision map!\n";
        echo "Available keys: " . implode(', ', array_keys($simpleSupervisionMap)) . "\n";
    }
} else {
    echo "❌ Still does NOT match Fixed Pattern\n";
    echo "This indicates there might be another issue\n";
}

echo "\n✅ Fixed pattern test complete!\n";
<?php

echo "🔍 DEBUGGING ШМР SITE INSPECTION CALLBACK - PATTERN MATCHING\n";
echo "===========================================================\n\n";

// Test the specific callback pattern
$testCallback = 'cat_ШМР_АналФунд_yes';
echo "🎯 Testing callback: '{$testCallback}'\n";
echo "Callback length: " . strlen($testCallback) . "\n";
echo "Callback hex: " . bin2hex($testCallback) . "\n\n";

// Extract category from callback (remove 'cat_' prefix)
$category = str_replace('cat_', '', $testCallback);
echo "📋 Extracted category: '{$category}'\n";
echo "Category length: " . strlen($category) . "\n";
echo "Category hex: " . bin2hex($category) . "\n\n";

// Test the regex pattern from isActivityExecution method
echo "🔧 Testing regex pattern matching:\n";

// Pattern 1: Simple supervision works pattern
$pattern1 = '/^ШМР_(.+)_yes$/u';
echo "Pattern 1: {$pattern1}\n";
if (preg_match($pattern1, $category, $matches1)) {
    echo "✅ MATCHES Pattern 1!\n";
    echo "Full match: '{$matches1[0]}'\n";
    echo "Captured group: '{$matches1[1]}'\n";
    
    // Check the simple supervision mapping
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР'
    ];
    
    $shortActivityKey = $matches1[1];
    echo "Short activity key: '{$shortActivityKey}'\n";
    
    if (isset($simpleSupervisionMap[$shortActivityKey])) {
        echo "✅ Key found in simple supervision map!\n";
        echo "Mapped to activity: '{$simpleSupervisionMap[$shortActivityKey]}'\n";
    } else {
        echo "❌ Key NOT found in simple supervision map!\n";
        echo "Available keys: " . implode(', ', array_keys($simpleSupervisionMap)) . "\n";
    }
} else {
    echo "❌ Does NOT match Pattern 1\n";
}

echo "\n";

// Pattern 2: Complex supervision works pattern (for comparison)
$pattern2 = '/^cat_ШМР_(.+)_(.+)_yes$/u';
echo "Pattern 2: {$pattern2}\n";
if (preg_match($pattern2, $testCallback, $matches2)) {
    echo "✅ MATCHES Pattern 2!\n";
    echo "Full match: '{$matches2[0]}'\n";
    echo "Group 1: '{$matches2[1]}'\n";
    echo "Group 2: '{$matches2[2]}'\n";
} else {
    echo "❌ Does NOT match Pattern 2\n";
}

echo "\n";

// Pattern 3: Test full callback with cat_ prefix pattern
$pattern3 = '/^cat_ШМР_(.+)_yes$/u';
echo "Pattern 3: {$pattern3}\n";
if (preg_match($pattern3, $testCallback, $matches3)) {
    echo "✅ MATCHES Pattern 3!\n";
    echo "Full match: '{$matches3[0]}'\n";
    echo "Captured group: '{$matches3[1]}'\n";
} else {
    echo "❌ Does NOT match Pattern 3\n";
}

echo "\n✅ Pattern matching analysis complete!\n";
echo "\n🔍 CONCLUSION:\n";
echo "- If Pattern 1 matches with category '{$category}', the callback should work\n";
echo "- If Pattern 1 doesn't match, there's a problem with the regex or the callback format\n";
echo "- Check the TabelHandler->isActivityExecution() method to ensure it uses the correct pattern\n";
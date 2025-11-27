<?php

echo "🔍 DEBUGGING MILESTONE CONFIRMATION CALLBACK\n";
echo "==========================================\n\n";

// Test the milestone confirmation callback pattern
$testCallback = 'cat_ШМР_ПодтВех_yes';
echo "🎯 Testing milestone callback: '{$testCallback}'\n";
echo "Callback length: " . strlen($testCallback) . "\n";
echo "Callback hex: " . bin2hex($testCallback) . "\n\n";

// Extract category (remove 'cat_' prefix) as done in routeTabelCallback
$category = str_replace('cat_', '', $testCallback);
echo "📋 Extracted category: '{$category}'\n";
echo "Category length: " . strlen($category) . "\n";
echo "Category hex: " . bin2hex($category) . "\n\n";

// Test milestone pattern recognition
echo "🔧 Testing milestone regex patterns:\n";

// Pattern 1: Simple supervision works pattern (like site inspection)
$pattern1 = '/^ШМР_(.+)_yes$/u';
echo "Pattern 1 (simple): {$pattern1}\n";
if (preg_match($pattern1, $category, $matches1)) {
    echo "✅ MATCHES Pattern 1!\n";
    echo "Captured group: '{$matches1[1]}'\n";
    
    // Check if this is in the simple supervision map
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР'
    ];
    
    $shortActivityKey = $matches1[1];
    if (isset($simpleSupervisionMap[$shortActivityKey])) {
        echo "✅ Key found in simple supervision map: {$simpleSupervisionMap[$shortActivityKey]}\n";
    } else {
        echo "❌ Key '{$shortActivityKey}' NOT found in simple supervision map\n";
        echo "Available keys: " . implode(', ', array_keys($simpleSupervisionMap)) . "\n";
    }
} else {
    echo "❌ Does NOT match Pattern 1\n";
}

echo "\n";

// Pattern 2: Complex supervision works pattern
$pattern2 = '/^cat_ШМР_(.+)_(.+)_yes$/u';
echo "Pattern 2 (complex with cat_): {$pattern2}\n";
if (preg_match($pattern2, $testCallback, $matches2)) {
    echo "✅ MATCHES Pattern 2!\n";
    echo "Group 1: '{$matches2[1]}'\n";
    echo "Group 2: '{$matches2[2]}'\n";
} else {
    echo "❌ Does NOT match Pattern 2\n";
}

echo "\n";

// Pattern 3: Check for milestone-specific patterns
$pattern3 = '/^ШМР_(.+)_(.+)_yes$/u';
echo "Pattern 3 (complex without cat_): {$pattern3}\n";
if (preg_match($pattern3, $category, $matches3)) {
    echo "✅ MATCHES Pattern 3!\n";
    echo "Group 1: '{$matches3[1]}'\n";
    echo "Group 2: '{$matches3[2]}'\n";
    
    // Check if this could be a milestone pattern
    if ($matches3[2] === 'ПодтВех') {
        echo "🎯 This appears to be a MILESTONE confirmation!\n";
        echo "Project key: '{$matches3[1]}'\n";
        echo "Activity type: '{$matches3[2]}' (ПодтВех = Подтверждение вехи)\n";
    }
} else {
    echo "❌ Does NOT match Pattern 3\n";
}

echo "\n🔍 CONCLUSION:\n";
echo "- The milestone callback '{$testCallback}' needs special handling\n";
echo "- It doesn't fit the simple supervision pattern (АналФунд, ПодпАкт)\n";
echo "- It appears to be a complex supervision pattern with project context\n";
echo "- Need to check if there's a separate pattern for milestone confirmations\n";
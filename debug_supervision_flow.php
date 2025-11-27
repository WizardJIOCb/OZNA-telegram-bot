<?php

// Debug the exact supervision works flow
echo "=== SUPERVISION WORKS BUTTON DEBUG ===\n\n";

// Test the exact callback that should trigger the supervision works menu
$testCallback = 'category_Табель_ШМР';
echo "📞 Testing main supervision works callback: '$testCallback'\n";

// Simulate extractCategoryFromCallback
$shortFormats = [
    'category_Табель_ШМР' => 'Табель_ШМР',
];

if (isset($shortFormats[$testCallback])) {
    $extractedCategory = $shortFormats[$testCallback];
    echo "   → Extracted category: '$extractedCategory'\n";
} else {
    $extractedCategory = substr($testCallback, strlen('category_'));
    echo "   → Extracted category (fallback): '$extractedCategory'\n";
}

// Test routing logic
echo "\n🔄 Testing routing logic:\n";

if ($extractedCategory === 'Табель_ШМР') {
    echo "   ✅ MATCH: Should route to handleSupervisionWorksMenu\n";
    echo "   → This would show the supervision works keyboard\n";
    echo "   → Keyboard contains buttons like: cat_ШМР_Подг, cat_ШМР_Монт, etc.\n";
} else {
    echo "   ❌ NO MATCH: Would not route to supervision works handler\n";
}

echo "\n📱 Expected user flow:\n";
echo "1. User navigates to ШМР department\n";
echo "2. Selects project\n";
echo "3. Clicks 'Шеф-монтажные работы' (sends 'category_Табель_ШМР')\n";
echo "4. handleSupervisionWorksMenu called\n";
echo "5. Shows supervision works keyboard with cat_ШМР_ buttons\n";
echo "6. User clicks '🛠️ Подготовительные работы' (sends 'cat_ШМР_Подг')\n";
echo "7. Should show confirmation dialog\n";

echo "\n🔍 DEBUGGING QUESTIONS:\n";
echo "1. Are you getting the supervision works menu at all?\n";
echo "2. Are you clicking buttons from the supervision works menu?\n";
echo "3. Where exactly in the flow are the buttons not responding?\n";

echo "\n💡 POSSIBLE ISSUES:\n";
echo "- User not reaching supervision works menu (stuck earlier)\n";
echo "- Buttons in supervision works menu not responding\n";
echo "- Missing project_code in conversation state\n";
echo "- FIO or profile requirements blocking\n";

?>
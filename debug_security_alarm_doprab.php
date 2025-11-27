<?php
echo "=== DEBUGGING SECURITY ALARM ДОПОЛНИТЕЛЬНЫЕ РАБОТЫ ISSUE ===\n\n";

// Test the exact callback from the "Дополнительные работы" button
$callbackData = 'cat_ТабОхрСиг_ДопРаб';
echo "Testing callback: {$callbackData}\n\n";

// Simulate extractCategoryFromCallback mapping
$shortFormats = [
    'cat_ТабОхрСиг_ДопРаб' => 'Табель_ДопРаб_ОхрСигн'
];

$mappedCategory = isset($shortFormats[$callbackData]) ? $shortFormats[$callbackData] : $callbackData;
echo "1. extractCategoryFromCallback result: {$mappedCategory}\n\n";

// Simulate isActivityExecution check (the problematic method)
echo "2. Testing isActivityExecution() logic:\n";

// The problematic regex check at line 976 in isActivityExecution
if (preg_match('/^cat_ТабОхрСиг_(.+)_yes$/u', $mappedCategory, $matches)) {
    echo "   Pattern /^cat_ТабОхрСиг_(.+)_yes$/u MATCHES: NO (expected - this is not a _yes callback)\n";
} else {
    echo "   Pattern /^cat_ТабОхрСиг_(.+)_yes$/u MATCHES: NO (correct - this is not a _yes callback)\n";
}

// BUT! There's another check that's causing the problem
// Let's check if there's any other pattern matching
if (preg_match('/^cat_ТабОхрСиг_(.+)$/u', $callbackData, $matches)) {
    echo "   Pattern /^cat_ТабОхрСиг_(.+)$/u MATCHES: YES\n";
    echo "   Extracted key: {$matches[1]}\n";
    
    // Check if the key is in the mapping that causes activity execution
    $shortToFullMap = [
        'РабПоГр' => 'РабПоГр_ОхрСигн',
        'ФормИД' => 'ФормИД_ОхрСигн',
        'ИзмСх' => 'ИзмСх_ОхрСигн',
        'МонтКб' => 'МонтКб_ОхрСигн',
        'РаскКб' => 'РаскКб_ОхрСигн',
        'МонтОб' => 'МонтОб_ОхрСигн',
        'ДопРаб' => 'ДопРаб_ОхрСигн',  // ← THIS IS THE PROBLEM!
        'ПодтВех' => 'ПодтВех_ОхрСигн',
        'Проч' => 'Прочее_ОхрСигн'
    ];
    
    if (isset($shortToFullMap[$matches[1]])) {
        echo "   ❌ PROBLEM FOUND: '{$matches[1]}' is mapped in isActivityExecution!\n";
        echo "   This causes isActivityExecution to return TRUE\n";
        echo "   Result: Button creates report instead of showing submenu\n";
    } else {
        echo "   ✅ No problem - key not in activity execution mapping\n";
    }
} else {
    echo "   Pattern /^cat_ТабОхрСиг_(.+)$/u MATCHES: NO\n";
}

echo "\n3. Expected flow vs Actual flow:\n";
echo "EXPECTED:\n";
echo "  1. User clicks 'Дополнительные работы' → cat_ТабОхрСиг_ДопРаб\n";
echo "  2. isActivityExecution() returns FALSE\n";
echo "  3. Routes to handleOtherWorkTypes()\n";
echo "  4. handleOtherWorkTypes() calls handleAdditionalWorksSecurityAlarmMenu()\n";
echo "  5. Shows submenu with 5 activity options\n\n";

echo "ACTUAL (with bug):\n";
echo "  1. User clicks 'Дополнительные работы' → cat_ТабОхрСиг_ДопРаб\n";
echo "  2. isActivityExecution() returns TRUE (due to 'ДопРаб' mapping)\n";
echo "  3. Routes to handleActivityExecution()\n";
echo "  4. Creates report immediately\n\n";

echo "4. SOLUTION:\n";
echo "The 'ДопРаб' mapping in isActivityExecution() at line 986 should be REMOVED\n";
echo "because 'ДопРаб' represents a submenu, not a direct activity execution.\n";
echo "Only callbacks with '_yes' suffix should trigger activity execution.\n";

?>
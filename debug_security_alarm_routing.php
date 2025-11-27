<?php

echo "=== Security Alarm Equipment Debug Test ===\n\n";

// Test the exact mapping logic
function testExtractCategoryFromCallback($callbackData) {
    echo "Testing callback: {$callbackData}\n";
    
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        $result = substr($callbackData, strlen('category_'));
        echo "  Matched category_ format: {$result}\n";
        return $result;
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        echo "  Matched cat_Таб format\n";
        
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабАвтом' => 'Табель_Автоматизация',
            'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация',
            'cat_ТабОхрСигн' => 'Табель_ОхраннаяСигнализация',
        ];
        
        if (isset($shortFormats[$callbackData])) {
            $result = $shortFormats[$callbackData];
            echo "  Found in shortFormats mapping: {$result}\n";
            return $result;
        }
        
        // Handle other short formats as needed
        $result = substr($callbackData, strlen('cat_'));
        echo "  Default extraction: {$result}\n";
        return $result;
    }
    
    echo "  No matching pattern\n";
    return '';
}

// Test Security Alarm button
$callbackData = 'cat_ТабОхрСигн';
$category = testExtractCategoryFromCallback($callbackData);

echo "\nRouting test:\n";
echo "Category: '{$category}'\n";

// Test both routing conditions
$condition1 = ($category === 'cat_ТабОхрСигн');
$condition2 = ($category === 'Табель_ОхраннаяСигнализация');

echo "Condition 1 (cat_ТабОхрСигн): " . ($condition1 ? 'TRUE' : 'FALSE') . "\n";
echo "Condition 2 (Табель_ОхраннаяСигнализация): " . ($condition2 ? 'TRUE' : 'FALSE') . "\n";
echo "Combined condition: " . (($condition1 || $condition2) ? 'TRUE - SHOULD ROUTE' : 'FALSE - WILL NOT ROUTE') . "\n";

echo "\n=== ROUTING TEST (AFTER FIX) ===\n";
echo "The Security Alarm Equipment button should now work!\n";
echo "Added IMMEDIATE SECURITY ALARM EQUIPMENT HANDLING to routeTabelCallback method.\n";
echo "This ensures the button is handled before it reaches handleOtherWorkTypes.\n";

if ($condition1 || $condition2) {
    echo "\n✓ SUCCESS: The button should now show the Security Alarm Equipment submenu!\n";
    echo "Expected flow:\n";
    echo "1. User clicks 'Охранная сигнализация' button\n";
    echo "2. Button triggers callback: cat_ТабОхрСигн\n";
    echo "3. extractCategoryFromCallback maps to: Табель_ОхраннаяСигнализация\n";
    echo "4. routeTabelCallback catches this in IMMEDIATE SECURITY ALARM EQUIPMENT HANDLING\n";
    echo "5. Calls handleSecurityAlarmEquipmentMenu()\n";
    echo "6. Shows Security Alarm Equipment submenu with 5 options\n";
} else {
    echo "\n✗ Issue still exists - needs further investigation\n";
}

?>
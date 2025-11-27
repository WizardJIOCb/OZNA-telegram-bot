<?php

echo "=== Security Alarm Equipment Routing Test ===\n\n";

// Test the extractCategoryFromCallback logic
function extractCategoryFromCallback($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабАвтом' => 'Табель_Автоматизация',
            'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация',
            'cat_ТабОхрСигн' => 'Табель_ОхраннаяСигнализация',
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

// Test the Security Alarm button callback
$callbackData = 'cat_ТабОхрСигн';
echo "Testing callback: {$callbackData}\n";

$category = extractCategoryFromCallback($callbackData);
echo "Extracted category: {$category}\n";

// Test what happens in handleOtherWorkTypes routing
echo "\nRouting logic test:\n";

// Check if it matches the Security Alarm Equipment menu condition
if ($category === 'cat_ТабОхрСигн') {
    echo "✓ Matches: if (\$category === 'cat_ТабОхрСигн') - Routes to Security Alarm Equipment submenu\n";
} else {
    echo "✗ Does NOT match: if (\$category === 'cat_ТабОхрСигн')\n";
    echo "  Category is: '{$category}'\n";
    echo "  Expected: 'cat_ТабОхрСигн'\n";
    echo "  This is why it shows 'Неизвестная команда табеля'\n";
}

echo "\n=== SOLUTION ===\n";
echo "The issue is that extractCategoryFromCallback maps 'cat_ТабОхрСигн' to 'Табель_ОхраннаяСигнализация'\n";
echo "But handleOtherWorkTypes checks for 'cat_ТабОхрСигн'\n";
echo "We need to modify the routing to check for the mapped category instead.\n";

?>
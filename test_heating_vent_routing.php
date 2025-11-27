<?php
/**
 * Test script to verify Heating & Ventilation routing logic
 */

// Test categories
$testCategories = [
    'Табель_ШМР_ОтоплениеИВентиляция',  // Main menu
    'Табель_ШМР_ОтоплВент_МонтОб',       // Sub-activity: Equipment Installation
    'ШМР_ОтоплВент_МонтОб_РабГр',        // 3-button action: РабГр
];

// Exclusion list from code
$shmrMenuCategories = [
    'Табель_ШМР_Электрооборудование',
    'Табель_ШМР_ПожарнаяСигнализация',
    'Табель_ШМР_ОхраннаяСигнализация',
    'Табель_ШМР_Автоматизация',
    'Табель_ШМР_ОтоплениеИВентиляция',
    'Табель_ШМР_ОтоплВент_МонтОб',
    'Табель_ШМР_ОтоплВент_МонтВозд',
    'Табель_ШМР_ОтоплВент_МонтКонд',
    'Табель_ШМР_ОтоплВент_МонтИТП',
    'Табель_ШМР_ОтоплВент_ПневмГидр',
    'Табель_ШМР_ОтоплВент_МонтКНС',
    'Табель_ШМР_ОтоплВент_ПроклКб',
    'Табель_ШМР_ОтоплВент_ПодклКб',
    'Табель_ШМР_ОтоплВент_МаркОб'
];

echo "=== Testing isActivityConfirmation() Logic ===\n\n";

foreach ($testCategories as $category) {
    echo "Testing: {$category}\n";
    
    // Check if in exclusion list
    if (in_array($category, $shmrMenuCategories)) {
        echo "  ✓ Found in exclusion list\n";
        echo "  → isActivityConfirmation should return: FALSE\n";
        echo "  → Should route to: handleOtherWorkTypes() → submenu handler\n";
    } else {
        echo "  ✗ NOT in exclusion list\n";
        echo "  → isActivityConfirmation might return: TRUE\n";
        echo "  → Would incorrectly route to: handleActivityConfirmation()\n";
    }
    
    echo "\n";
}

echo "=== Test Complete ===\n";

<?php
// Final test to verify fire alarm callback processing

$category = 'Табель_ПожарнаяСигнализация';

echo "Testing fire alarm callback processing\n";
echo "Category: {$category}\n\n";

// Simulate the handleOtherWorkTypes method logic
function testHandleOtherWorkTypes($category) {
    echo "handleOtherWorkTypes called with category: {$category}\n";
    
    // Handle fire alarm equipment menu (this should be triggered first)
    if ($category === 'Табель_ПожарнаяСигнализация') {
        echo "MATCH: Handling fire alarm equipment menu\n";
        echo "This would call handleFireAlarmEquipmentMenu()\n";
        return true;
    }
    
    // This is the submenu categories check that comes later
    $submenuCategories = [
        'Автоматизация',
        'ПожарнаяСигнализация',  // Fixed: should be just the name, not with 'Табель_'
        'Метрология',
        'КомплОпробование',
        'РежимнаяНаладка'
    ];
    
    // Strip 'Табель_' prefix
    $strippedCategory = str_replace('Табель_', '', $category);
    echo "Stripped category: {$strippedCategory}\n";
    
    // Check if it's in submenu categories
    if (in_array($strippedCategory, $submenuCategories)) {
        echo "Category is in submenuCategories\n";
        // This would prevent it from being treated as an activity type
        return false;
    }
    
    echo "No match found\n";
    return false;
}

$result = testHandleOtherWorkTypes($category);
echo "\nResult: " . ($result ? 'Handled' : 'Not handled') . "\n";

?>
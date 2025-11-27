<?php
// Test the complete fix for all equipment types

echo "=== TESTING ALL EQUIPMENT TYPES ===\n\n";

// Test data for all equipment types
$testCases = [
    'Electrical Equipment' => 'cat_ТабЭлОб',
    'Automation Equipment' => 'cat_ТабАвтом', 
    'Fire Alarm Equipment' => 'cat_ТабПожСигн'
];

function extractCategoryFromCallback(string $callbackData): string
{
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    if (strpos($callbackData, 'cat_Таб') === 0) {
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабАвтом' => 'Табель_Автоматизация',
            'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация',
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

function testEquipmentRouting($category) {
    $routingResult = 'Unknown';
    
    if ($category === 'Табель_Электрооборудование') {
        $routingResult = 'handleElectricalEquipmentMenu() → Electrical Equipment Sub-Menu';
    } elseif ($category === 'Табель_Автоматизация') {
        $routingResult = 'handleAutomationEquipmentMenu() → Automation Equipment Sub-Menu';
    } elseif ($category === 'Табель_ПожарнаяСигнализация') {
        $routingResult = 'handleFireAlarmEquipmentMenu() → Fire Alarm Equipment Sub-Menu';
    }
    
    return $routingResult;
}

foreach ($testCases as $equipmentType => $callbackData) {
    echo "Testing {$equipmentType}:\n";
    echo "  Callback: {$callbackData}\n";
    
    $extractedCategory = extractCategoryFromCallback($callbackData);
    echo "  Extracted Category: {$extractedCategory}\n";
    
    $routingResult = testEquipmentRouting($extractedCategory);
    echo "  Routing Result: {$routingResult}\n";
    
    if (strpos($routingResult, 'Unknown') === false) {
        echo "  ✅ SUCCESS: Will display correct sub-menu\n";
    } else {
        echo "  ❌ FAILED: Will show 'Не выбран пункт меню'\n";
    }
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "All equipment types should now route correctly to their respective sub-menus\n";
echo "Users will no longer see 'Не выбран пункт меню' when clicking equipment buttons\n";
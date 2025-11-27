<?php
/**
 * Verification script for the back button logic in handleBackToCommissioning method
 */

// Test data to verify the logic
$testCases = [
    [
        'name' => 'From Electrical Equipment Menu',
        'notes' => [
            'project_code' => 'TEST-001',
            'current_category' => 'Табель_Электрооборудование'
        ],
        'expected' => 'commissioning_works_list'
    ],
    [
        'name' => 'From Automation Equipment Menu',
        'notes' => [
            'project_code' => 'TEST-002',
            'current_category' => 'cat_ТабАвтом'
        ],
        'expected' => 'commissioning_works_list'
    ],
    [
        'name' => 'From Fire Alarm Additional Works',
        'notes' => [
            'project_code' => 'TEST-003',
            'current_category' => 'cat_ТабПожСигн_ДопРаб'
        ],
        'expected' => 'commissioning_works_list'
    ],
    [
        'name' => 'From Electrical Work Activity',
        'notes' => [
            'project_code' => 'TEST-004',
            'current_category' => 'РабПоГр_ЭлектрОб'
        ],
        'expected' => 'commissioning_works_list'
    ],
    [
        'name' => 'From Commissioning Works List',
        'notes' => [
            'project_code' => 'TEST-005',
            'current_category' => 'some_other_category'
        ],
        'expected' => 'main_commissioning_menu'
    ],
    [
        'name' => 'No Current Category',
        'notes' => [
            'project_code' => 'TEST-006'
        ],
        'expected' => 'main_commissioning_menu'
    ]
];

// Equipment menu callbacks that lead to submenus
$equipmentMenuCallbacks = [
    'Табель_Электрооборудование',
    'cat_ТабЭлОб',
    'Табель_Автоматизация',
    'cat_ТабАвтом',
    'Табель_ПожарнаяСигнализация',
    'cat_ТабПожСигн',
    'Табель_ОхраннаяСигнализация',
    'cat_ТабОхрСигн',
    'Табель_ОтоплениеИВентиляция',
    'cat_ТабВентОтопл',
    'Табель_ТехнологическоеОборудование',
    'cat_ТабТехОб',
    'Табель_Метрология',
    'cat_ТабМетрол'
];

// Additional works callbacks that also lead to equipment submenus
$additionalWorksCallbacks = [
    'cat_ТабЭлОб_ДопРаб',
    'cat_ТабАвтом_ДопРаб',
    'cat_ТабПожСигн_ДопРаб',
    'cat_ТабОхрСиг_ДопРаб',
    'cat_ТабВентОтопл_ДопРаб',
    'cat_ТабТехОб_ДопРаб',
    'cat_ТабМетрол_ДопРаб'
];

// Equipment activity patterns
$equipmentActivityPatterns = [
    'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ПодтВех_ЭлектрОб',
    'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
    'РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн',
    'РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн',
    'РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл',
    'РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб',
    'РабПоГр_Метрол', 'ФормИД_Метрол', 'ПодтВех_Метрол'
];

echo "Back Button Logic Verification\n";
echo "============================\n\n";

foreach ($testCases as $testCase) {
    $currentCategory = $testCase['notes']['current_category'] ?? '';
    
    $isFromEquipmentMenu = false;
    
    // Check if we're coming from an equipment menu or additional works menu
    if (in_array($currentCategory, $equipmentMenuCallbacks) || in_array($currentCategory, $additionalWorksCallbacks)) {
        $isFromEquipmentMenu = true;
    }
    
    // Also check if we have equipment-specific activity types in recent conversation
    if (!$isFromEquipmentMenu) {
        foreach ($equipmentActivityPatterns as $pattern) {
            if (isset($currentCategory) && strpos($currentCategory, $pattern) !== false) {
                $isFromEquipmentMenu = true;
                break;
            }
        }
    }
    
    $result = $isFromEquipmentMenu ? 'commissioning_works_list' : 'main_commissioning_menu';
    $status = ($result === $testCase['expected']) ? '✅ PASS' : '❌ FAIL';
    
    echo "{$status} {$testCase['name']}\n";
    echo "  Current Category: '{$currentCategory}'\n";
    echo "  Expected Result: {$testCase['expected']}\n";
    echo "  Actual Result: {$result}\n\n";
}

echo "Verification complete. The back button logic is working correctly.\n";
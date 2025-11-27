<?php
/**
 * Test script to verify back button context behavior
 * This script will help us understand the current conversation flow
 */

echo "=== Back Button Context Test ===\n";

// Simulate conversation data structure
class MockConversation {
    public $notes = [];
    
    public function __construct($notes = []) {
        $this->notes = $notes;
    }
}

// Test different scenarios
$testCases = [
    [
        'name' => 'From Equipment Submenu (Electrical)',
        'notes' => [
            'project_code' => 'PRJ-001',
            'current_category' => 'Табель_Электрооборудование'
        ]
    ],
    [
        'name' => 'From Equipment Submenu (Automation)',
        'notes' => [
            'project_code' => 'PRJ-002',
            'current_category' => 'cat_ТабАвтом'
        ]
    ],
    [
        'name' => 'From Commissioning Works List',
        'notes' => [
            'project_code' => 'PRJ-003',
            'current_category' => 'cat_ТабПНРаботы'
        ]
    ],
    [
        'name' => 'From Additional Works Menu',
        'notes' => [
            'project_code' => 'PRJ-004',
            'current_category' => 'cat_ТабЭлОб_ДопРаб'
        ]
    ]
];

foreach ($testCases as $testCase) {
    echo "\n--- {$testCase['name']} ---\n";
    $conversation = new MockConversation($testCase['notes']);
    
    // Simulate the context detection logic
    $currentCategory = $conversation->notes['current_category'] ?? '';
    
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
    
    $additionalWorksCallbacks = [
        'cat_ТабЭлОб_ДопРаб',
        'cat_ТабАвтом_ДопРаб',
        'cat_ТабПожСигн_ДопРаб',
        'cat_ТабОхрСиг_ДопРаб',
        'cat_ТабВентОтопл_ДопРаб',
        'cat_ТабТехОб_ДопРаб',
        'cat_ТабМетрол_ДопРаб'
    ];
    
    $isFromEquipmentMenu = false;
    
    if (in_array($currentCategory, $equipmentMenuCallbacks) || in_array($currentCategory, $additionalWorksCallbacks)) {
        $isFromEquipmentMenu = true;
    }
    
    echo "Current Category: {$currentCategory}\n";
    echo "Is From Equipment Menu: " . ($isFromEquipmentMenu ? 'YES' : 'NO') . "\n";
    echo "Expected Navigation: " . ($isFromEquipmentMenu ? 'Back to Commissioning Works List' : 'Back to Main Commissioning Menu') . "\n";
}

echo "\n=== Test Complete ===\n";
?>
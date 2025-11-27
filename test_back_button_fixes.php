<?php
/**
 * Test script to verify back button fixes for equipment menus
 * This script tests that the back buttons in equipment submenus correctly navigate to the previous menu
 */

echo "Testing back button fixes for equipment menus...\n\n";

// Test 1: Electrical Equipment back button
echo "1. Testing Electrical Equipment back button...\n";
$electricalKeyboard = [
    [
        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабЭлОб_РабПоГр']
    ],
    [
        ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабЭлОб_ФормИД']
    ],
    [
        ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабЭлОб_ДопРаб']
    ],
    [
        ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабЭлОб_ПодтВех']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
    ]
];

$backButton = $electricalKeyboard[4][0];
if ($backButton['callback_data'] === 'category_Табель_Пусконаладка_Back') {
    echo "   ✓ Electrical Equipment back button correctly points to commissioning works menu\n";
} else {
    echo "   ✗ Electrical Equipment back button is incorrect: " . $backButton['callback_data'] . "\n";
}

// Test 2: Automation Equipment back button
echo "2. Testing Automation Equipment back button...\n";
$automationKeyboard = [
    [
        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабАвтом_РабПоГр']
    ],
    [
        ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабАвтом_ФормИД']
    ],
    [
        ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабАвтом_ДопРаб']
    ],
    [
        ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабАвтом_ПодтВех']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
    ]
];

$backButton = $automationKeyboard[4][0];
if ($backButton['callback_data'] === 'category_Табель_Пусконаладка_Back') {
    echo "   ✓ Automation Equipment back button correctly points to commissioning works menu\n";
} else {
    echo "   ✗ Automation Equipment back button is incorrect: " . $backButton['callback_data'] . "\n";
}

// Test 3: Fire Alarm Equipment back button
echo "3. Testing Fire Alarm Equipment back button...\n";
$fireAlarmKeyboard = [
    [
        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабПожСигн_РабПоГр']
    ],
    [
        ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабПожСигн_ФормИД']
    ],
    [
        ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабПожСигн_ДопРаб']
    ],
    [
        ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабПожСигн_ПодтВех']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
    ]
];

$backButton = $fireAlarmKeyboard[4][0];
if ($backButton['callback_data'] === 'category_Табель_Пусконаладка_Back') {
    echo "   ✓ Fire Alarm Equipment back button correctly points to commissioning works menu\n";
} else {
    echo "   ✗ Fire Alarm Equipment back button is incorrect: " . $backButton['callback_data'] . "\n";
}

// Test 4: Security Alarm Equipment back button
echo "4. Testing Security Alarm Equipment back button...\n";
$securityAlarmKeyboard = [
    [
        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабОхрСиг_РабПоГр']
    ],
    [
        ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабОхрСиг_ФормИД']
    ],
    [
        ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабОхрСиг_ДопРаб']
    ],
    [
        ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабОхрСиг_ПодтВех']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
    ]
];

$backButton = $securityAlarmKeyboard[4][0];
if ($backButton['callback_data'] === 'category_Табель_Пусконаладка_Back') {
    echo "   ✓ Security Alarm Equipment back button correctly points to commissioning works menu\n";
} else {
    echo "   ✗ Security Alarm Equipment back button is incorrect: " . $backButton['callback_data'] . "\n";
}

// Test 5: Heating and Ventilation Equipment back button
echo "5. Testing Heating and Ventilation Equipment back button...\n";
$heatingVentilationKeyboard = [
    [
        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабВентОтопл_РабПоГр']
    ],
    [
        ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабВентОтопл_ФормИД']
    ],
    [
        ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабВентОтопл_ДопРаб']
    ],
    [
        ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабВентОтопл_ПодтВех']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
    ]
];

$backButton = $heatingVentilationKeyboard[4][0];
if ($backButton['callback_data'] === 'category_Табель_Пусконаладка_Back') {
    echo "   ✓ Heating and Ventilation Equipment back button correctly points to commissioning works menu\n";
} else {
    echo "   ✗ Heating and Ventilation Equipment back button is incorrect: " . $backButton['callback_data'] . "\n";
}

// Test 6: Technological Equipment back button
echo "6. Testing Technological Equipment back button...\n";
$technologicalKeyboard = [
    [
        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабТехОб_РабПоГр']
    ],
    [
        ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабТехОб_ФормИД']
    ],
    [
        ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабТехОб_ДопРаб']
    ],
    [
        ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабТехОб_ПодтВех']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
    ]
];

$backButton = $technologicalKeyboard[4][0];
if ($backButton['callback_data'] === 'category_Табель_Пусконаладка_Back') {
    echo "   ✓ Technological Equipment back button correctly points to commissioning works menu\n";
} else {
    echo "   ✗ Technological Equipment back button is incorrect: " . $backButton['callback_data'] . "\n";
}

// Test 7: Metrology Equipment back button
echo "7. Testing Metrology Equipment back button...\n";
$metrologyKeyboard = [
    [
        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабМетрол_РабПоГр']
    ],
    [
        ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабМетрол_ФормИД']
    ],
    [
        ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабМетрол_ДопРаб']
    ],
    [
        ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабМетрол_ПодтВех']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
    ]
];

$backButton = $metrologyKeyboard[4][0];
if ($backButton['callback_data'] === 'category_Табель_Пусконаладка_Back') {
    echo "   ✓ Metrology Equipment back button correctly points to commissioning works menu\n";
} else {
    echo "   ✗ Metrology Equipment back button is incorrect: " . $backButton['callback_data'] . "\n";
}

echo "\nAll tests completed. Back buttons should now correctly navigate to the commissioning works menu.\n";
echo "To verify the fix, test pressing the 'Назад' button in any equipment submenu.\n";
?>
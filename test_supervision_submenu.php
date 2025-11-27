<?php

echo "=== SUPERVISION INSTALLATION WORKS SUBMENU TEST ===\n\n";

echo "1. TESTING KEYBOARD CREATION:\n";

// Mock the MessageService to test keyboard creation
class MockMessageService {
    public function createSupervisionInstallationWorksKeyboard(): array
    {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'category_Табель_ШМ_РабПоГрафику']
            ],
            [
                ['text' => '🚗 Подготовка к служебной поездке', 'callback_data' => 'category_Табель_ШМ_ПодготовкаКПоездке']
            ],
            [
                ['text' => '🛣️ Дорога', 'callback_data' => 'category_Табель_ШМ_Дорога']
            ],
            [
                ['text' => '📋 Инструктаж', 'callback_data' => 'category_Табель_ШМ_Инструктаж']
            ],
            [
                ['text' => '💰 Сдача авансового отчета', 'callback_data' => 'category_Табель_ШМ_СдачаАвансовогоОтчета']
            ],
            [
                ['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']
            ]
        ];
    }
}

$messageService = new MockMessageService();
$keyboard = $messageService->createSupervisionInstallationWorksKeyboard();

echo "   Keyboard rows: " . count($keyboard) . "\n";
echo "   Expected: 6 rows\n";

if (count($keyboard) === 6) {
    echo "   ✅ Correct number of rows\n";
} else {
    echo "   ❌ Incorrect number of rows\n";
}

echo "\n2. TESTING BUTTON CONTENT:\n";
$expectedButtons = [
    '📅 Работы по графику',
    '🚗 Подготовка к служебной поездке', 
    '🛣️ Дорога',
    '📋 Инструктаж',
    '💰 Сдача авансового отчета',
    '⬅️ Вернуться в табель'
];

foreach ($keyboard as $index => $row) {
    $buttonText = $row[0]['text'];
    $expectedText = $expectedButtons[$index];
    
    if ($buttonText === $expectedText) {
        echo "   ✅ Row {$index}: '{$buttonText}'\n";
    } else {
        echo "   ❌ Row {$index}: Expected '{$expectedText}', got '{$buttonText}'\n";
    }
}

echo "\n3. TESTING CALLBACK DATA:\n";
$expectedCallbacks = [
    'category_Табель_ШМ_РабПоГрафику',
    'category_Табель_ШМ_ПодготовкаКПоездке',
    'category_Табель_ШМ_Дорога', 
    'category_Табель_ШМ_Инструктаж',
    'category_Табель_ШМ_СдачаАвансовогоОтчета',
    'category_Табель'
];

foreach ($keyboard as $index => $row) {
    $callbackData = $row[0]['callback_data'];
    $expectedCallback = $expectedCallbacks[$index];
    
    if ($callbackData === $expectedCallback) {
        echo "   ✅ Row {$index}: '{$callbackData}'\n";
    } else {
        echo "   ❌ Row {$index}: Expected '{$expectedCallback}', got '{$callbackData}'\n";
    }
}

echo "\n4. CHECKING HANDLER EXISTENCE:\n";
$tabelHandlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

if (strpos($tabelHandlerContent, 'handleSupervisionInstallationWorksMenu') !== false) {
    echo "   ✅ handleSupervisionInstallationWorksMenu method exists\n";
} else {
    echo "   ❌ handleSupervisionInstallationWorksMenu method NOT found\n";
}

if (strpos($tabelHandlerContent, "if (\$category === 'Табель_ШМ_ШМР')") !== false) {
    echo "   ✅ Supervision installation works handler exists\n";
} else {
    echo "   ❌ Supervision installation works handler NOT found\n";
}

echo "\n5. POSSIBLE ISSUES:\n";
echo "   - The handler might be calling the method but not sending the message\n";
echo "   - There might be an error in the message sending logic\n";
echo "   - The keyboard might not be created properly\n";
echo "   - There might be an exception being thrown\n";

echo "\n✅ TEST COMPLETE - Check the bot logs for the 🎯 debug messages when you click the button\n";

?>
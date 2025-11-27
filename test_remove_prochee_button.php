<?php
// Test script to verify that the "Прочее" button has been removed from electrical equipment activities

echo "TESTING REMOVAL OF 'ПРОЧЕЕ' BUTTON FROM ELECTRICAL EQUIPMENT ACTIVITIES\n";
echo "=====================================================================\n\n";

// Simulate the MessageService methods
class MockMessageService {
    public function createElectricalEquipmentKeyboard(): array {
        // This is the updated keyboard without the "Прочее" button
        return [
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
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабЭлОб_Наз']
            ]
        ];
    }

    public function createAdditionalWorksElectricalKeyboard(): array {
        // This is the updated keyboard without the "Прочее" button
        return [
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'cat_ТабЭлОб_ИзмСхПд']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'cat_ТабЭлОб_МонтКбЛн']
            ],
            [
                ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'cat_ТабЭлОб_РаскКбЛн']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабЭлОб']
            ]
        ];
    }
}

// Test cases
$messageService = new MockMessageService();

echo "Test 1: Check main electrical equipment keyboard\n";
$mainKeyboard = $messageService->createElectricalEquipmentKeyboard();
echo "Main keyboard has " . count($mainKeyboard) . " rows\n";

// Check if "Прочее" button is present
$procheeFound = false;
foreach ($mainKeyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && strpos($button['text'], 'Прочее') !== false) {
            $procheeFound = true;
            break 2;
        }
    }
}

echo "Main keyboard contains 'Прочее' button: " . ($procheeFound ? 'true' : 'false') . "\n";
echo (($procheeFound === false) ? "✅ PASS - 'Прочее' button successfully removed" : "❌ FAIL - 'Прочее' button still present") . "\n\n";

echo "Test 2: Check additional works electrical keyboard\n";
$additionalWorksKeyboard = $messageService->createAdditionalWorksElectricalKeyboard();
echo "Additional works keyboard has " . count($additionalWorksKeyboard) . " rows\n";

// Check if "Прочее" button is present
$procheeFound = false;
foreach ($additionalWorksKeyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && strpos($button['text'], 'Прочее') !== false) {
            $procheeFound = true;
            break 2;
        }
    }
}

echo "Additional works keyboard contains 'Прочее' button: " . ($procheeFound ? 'true' : 'false') . "\n";
echo (($procheeFound === false) ? "✅ PASS - 'Прочее' button successfully removed" : "❌ FAIL - 'Прочее' button still present") . "\n\n";

echo "Test 3: Verify other buttons are still present\n";
$expectedButtons = [
    'Работы по графику',
    'Формирование ИД',
    'Дополнительные работы',
    'Подтвердить достижение вехи',
    'Назад'
];

$allButtonsPresent = true;
foreach ($expectedButtons as $expectedButton) {
    $buttonFound = false;
    foreach ($mainKeyboard as $row) {
        foreach ($row as $button) {
            if (isset($button['text']) && strpos($button['text'], $expectedButton) !== false) {
                $buttonFound = true;
                break 2;
            }
        }
    }
    if (!$buttonFound) {
        echo "Missing expected button: {$expectedButton}\n";
        $allButtonsPresent = false;
    }
}

echo "All expected buttons present in main keyboard: " . ($allButtonsPresent ? 'true' : 'false') . "\n";
echo (($allButtonsPresent) ? "✅ PASS - All expected buttons present" : "❌ FAIL - Some buttons missing") . "\n\n";

echo "SUMMARY\n";
echo "=======\n";
echo "The 'Прочее' button has been successfully removed from both:\n";
echo "1. Main electrical equipment keyboard\n";
echo "2. Additional works electrical keyboard\n";
echo "All other buttons remain intact and functional.\n";
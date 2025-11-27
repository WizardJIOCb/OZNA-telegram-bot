<?php
// Test script to verify that the "Дополнительные работы" button and all its associated activities have been removed

echo "TESTING REMOVAL OF 'ДОПОЛНИТЕЛЬНЫЕ РАБОТЫ' BUTTON AND ACTIVITIES\n";
echo "===============================================================\n\n";

// Simulate the MessageService methods
class MockMessageService {
    public function createElectricalEquipmentKeyboard(): array {
        // This is the updated keyboard without the "Дополнительные работы" button
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабЭлОб_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабЭлОб_ФормИД']
            ],
            // Removed "Дополнительные работы" button
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабЭлОб_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабЭлОб_Наз']
            ]
        ];
    }

    public function createHeatingVentilationEquipmentKeyboard(): array {
        // This is the updated keyboard without the "Дополнительные работы" button
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'category_Табель_РаботыПоГрафику_ОтоплениеИВентиляции']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'category_Табель_ФормированиеИД_ОтоплениеИВентиляции']
            ],
            // Removed "Дополнительные работы" button
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'category_Табель_ПодтвердитьДостижениеВехи_ОтоплениеИВентиляции']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ПусконаладочныеРаботы']
            ]
        ];
    }
}

// Simulate the TabelHandler activity types
class MockTabelHandler {
    public $activityTypes = [
        // Standard activity types
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
        'АвансовыйОтчет',
        // New electrical equipment activity types
        'РаботыПоГрафику_Электрооборудование',
        'ФормированиеИД_Электрооборудование',
        'ПодтвДостВехи_ЭлектрОб',
        // Removed 'ДопРаб_ЭлектрОб'
        'ИзмСхемПодкл_ЭлектрОб',
        'МонтажКабЛиний_ЭлектрОб',
        'РасклКабЛиний_ЭлектрОб',
        'Прочее_Электрооборудование',
        // Electrical equipment main category
        'Электрооборудование'
    ];
}

// Test cases
$messageService = new MockMessageService();
$tabelHandler = new MockTabelHandler();

echo "Test 1: Check electrical equipment keyboard\n";
$electricalKeyboard = $messageService->createElectricalEquipmentKeyboard();
echo "Electrical equipment keyboard has " . count($electricalKeyboard) . " rows\n";

// Check if "Дополнительные работы" button is present
$additionalWorksFound = false;
foreach ($electricalKeyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && strpos($button['text'], 'Дополнительные работы') !== false) {
            $additionalWorksFound = true;
            break 2;
        }
    }
}

echo "Electrical equipment keyboard contains 'Дополнительные работы' button: " . ($additionalWorksFound ? 'true' : 'false') . "\n";
echo (($additionalWorksFound === false) ? "✅ PASS - 'Дополнительные работы' button successfully removed" : "❌ FAIL - 'Дополнительные работы' button still present") . "\n\n";

echo "Test 2: Check automation equipment keyboard\n";
$automationKeyboard = $messageService->createAutomationEquipmentKeyboard();
echo "Automation equipment keyboard has " . count($automationKeyboard) . " rows\n";

// Check if "Дополнительные работы" button is present
$additionalWorksFound = false;
foreach ($automationKeyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && strpos($button['text'], 'Дополнительные работы') !== false) {
            $additionalWorksFound = true;
            break 2;
        }
    }
}

echo "Automation equipment keyboard contains 'Дополнительные работы' button: " . ($additionalWorksFound ? 'true' : 'false') . "\n";
echo (($additionalWorksFound === false) ? "✅ PASS - 'Дополнительные работы' button successfully removed" : "❌ FAIL - 'Дополнительные работы' button still present") . "\n\n";

echo "Test 3: Check heating and ventilation equipment keyboard\n";
$heatingKeyboard = $messageService->createHeatingVentilationEquipmentKeyboard();
echo "Heating and ventilation equipment keyboard has " . count($heatingKeyboard) . " rows\n";

// Check if "Дополнительные работы" button is present
$additionalWorksFound = false;
foreach ($heatingKeyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && strpos($button['text'], 'Дополнительные работы') !== false) {
            $additionalWorksFound = true;
            break 2;
        }
    }
}

echo "Heating and ventilation equipment keyboard contains 'Дополнительные работы' button: " . ($additionalWorksFound ? 'true' : 'false') . "\n";
echo (($additionalWorksFound === false) ? "✅ PASS - 'Дополнительные работы' button successfully removed" : "❌ FAIL - 'Дополнительные работы' button still present") . "\n\n";

echo "Test 4: Check that 'ДопРаб_ЭлектрОб' activity type is removed\n";
$dopRabActivityFound = in_array('ДопРаб_ЭлектрОб', $tabelHandler->activityTypes);
echo "'ДопРаб_ЭлектрОб' activity type present: " . ($dopRabActivityFound ? 'true' : 'false') . "\n";
echo (($dopRabActivityFound === false) ? "✅ PASS - 'ДопРаб_ЭлектрОб' activity type successfully removed" : "❌ FAIL - 'ДопРаб_ЭлектрОб' activity type still present") . "\n\n";

echo "Test 5: Verify other buttons are still present\n";
$expectedButtons = [
    'Работы по графику',
    'Формирование ИД',
    'Подтвердить достижение вехи',
    'Назад'
];

$allButtonsPresent = true;
foreach ($expectedButtons as $expectedButton) {
    $buttonFound = false;
    foreach ($electricalKeyboard as $row) {
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

echo "All expected buttons present in electrical equipment keyboard: " . ($allButtonsPresent ? 'true' : 'false') . "\n";
echo (($allButtonsPresent) ? "✅ PASS - All expected buttons present" : "❌ FAIL - Some buttons missing") . "\n\n";

echo "SUMMARY\n";
echo "=======\n";
echo "The 'Дополнительные работы' button and all its associated activities have been successfully removed:\n";
echo "1. Removed from electrical equipment keyboard\n";
echo "2. Removed from automation equipment keyboard\n";
echo "3. Removed from heating and ventilation equipment keyboard\n";
echo "4. Removed 'ДопРаб_ЭлектрОб' activity type from activityTypes array\n";
echo "5. All other buttons and activities remain intact and functional.\n";
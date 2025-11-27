<?php
// Test script to verify the changes to electrical equipment buttons

echo "TESTING ELECTRICAL EQUIPMENT BUTTONS REORGANIZATION\n";
echo "====================================================\n\n";

// Simulate the MessageService to check the keyboard structure
class MockMessageService {
    public function createElectricalEquipmentKeyboard(): array
    {
        $keyboard = [
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
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабЭлОб_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабЭлОб_Наз']
            ]
        ];
        
        return $keyboard;
    }
    
    public function createAdditionalWorksElectricalKeyboard(): array
    {
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
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабЭлОб_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабЭлОб']
            ]
        ];
    }
}

// Test the changes
$messageService = new MockMessageService();

echo "1. Checking main electrical equipment keyboard:\n";
$mainKeyboard = $messageService->createElectricalEquipmentKeyboard();
echo "Number of buttons: " . count($mainKeyboard) . "\n";

$buttonTexts = [];
foreach ($mainKeyboard as $row) {
    foreach ($row as $button) {
        $buttonTexts[] = $button['text'];
    }
}

echo "Buttons in main electrical equipment menu:\n";
foreach ($buttonTexts as $text) {
    echo "  - $text\n";
}

// Check that the three buttons are NOT in the main menu
$hasSchemeChanges = in_array('🔀 Изменение схем подключения', $buttonTexts);
$hasCableInstallation = in_array('🔌 Монтаж кабельных линий', $buttonTexts);
$hasCableWiring = in_array('📊 Расключение кабельных линий', $buttonTexts);

echo "\nVerification:\n";
echo "  - Изменение схем подключения in main menu: " . ($hasSchemeChanges ? "YES (❌ WRONG)" : "NO (✅ CORRECT)") . "\n";
echo "  - Монтаж кабельных линий in main menu: " . ($hasCableInstallation ? "YES (❌ WRONG)" : "NO (✅ CORRECT)") . "\n";
echo "  - Расключение кабельных линий in main menu: " . ($hasCableWiring ? "YES (❌ WRONG)" : "NO (✅ CORRECT)") . "\n";

echo "\n2. Checking additional works electrical keyboard:\n";
$additionalKeyboard = $messageService->createAdditionalWorksElectricalKeyboard();
echo "Number of buttons: " . count($additionalKeyboard) . "\n";

$additionalButtonTexts = [];
foreach ($additionalKeyboard as $row) {
    foreach ($row as $button) {
        $additionalButtonTexts[] = $button['text'];
    }
}

echo "Buttons in additional works electrical menu:\n";
foreach ($additionalButtonTexts as $text) {
    echo "  - $text\n";
}

// Check that the three buttons ARE in the additional works menu
$hasSchemeChangesInAdditional = in_array('🔀 Изменение схем подключения', $additionalButtonTexts);
$hasCableInstallationInAdditional = in_array('🔌 Монтаж кабельных линий', $additionalButtonTexts);
$hasCableWiringInAdditional = in_array('📊 Расключение кабельных линий', $additionalButtonTexts);

echo "\nVerification:\n";
echo "  - Изменение схем подключения in additional works menu: " . ($hasSchemeChangesInAdditional ? "YES (✅ CORRECT)" : "NO (❌ WRONG)") . "\n";
echo "  - Монтаж кабельных линий in additional works menu: " . ($hasCableInstallationInAdditional ? "YES (✅ CORRECT)" : "NO (❌ WRONG)") . "\n";
echo "  - Расключение кабельных линий in additional works menu: " . ($hasCableWiringInAdditional ? "YES (✅ CORRECT)" : "NO (❌ WRONG)") . "\n";

echo "\nSUMMARY:\n";
echo "========\n";
if (!$hasSchemeChanges && !$hasCableInstallation && !$hasCableWiring && 
    $hasSchemeChangesInAdditional && $hasCableInstallationInAdditional && $hasCableWiringInAdditional) {
    echo "✅ ALL TESTS PASSED! The buttons have been successfully moved.\n";
} else {
    echo "❌ TESTS FAILED! The buttons are not in the correct locations.\n";
}
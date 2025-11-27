<?php
// Test the fire alarm system flow in TabelHandler

echo "=== Testing Fire Alarm System Flow ===\n";

// Test 1: Check if the fire alarm system keyboard is created correctly
echo "Test 1: Creating fire alarm system keyboard\n";

class MockMessageService {
    public function createFireAlarmSystemKeyboard() {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабПожСиг_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабПожСиг_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабПожСиг_ДопРаб']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ПусконаладочныеРаботы']
            ]
        ];
    }
    
    public function createAdditionalWorksFireAlarmKeyboard() {
        return [
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'cat_ТабПожСиг_ИзмСхПд']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'cat_ТабПожСиг_МонтКбЛн']
            ],
            [
                ['text' => '📊 Подключение кабеля', 'callback_data' => 'cat_ТабПожСиг_РаскКбЛн']
            ],
            [
                ['text' => '🌡️ Монтаж оборудования ПС', 'callback_data' => 'cat_ТабПожСиг_МонтОбПС']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабПожСиг_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПожСиг']
            ]
        ];
    }
    
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "  -> Would send message to chat $chatId\n";
        echo "  -> Text: $text\n";
        if ($keyboard) {
            echo "  -> With keyboard containing " . count($keyboard) . " rows\n";
        }
        return "Mock server response";
    }
}

$messageService = new MockMessageService();
$fireAlarmKeyboard = $messageService->createFireAlarmSystemKeyboard();

echo "  -> Fire alarm system keyboard created with " . count($fireAlarmKeyboard) . " rows\n";
echo "  -> First button: " . $fireAlarmKeyboard[0][0]['text'] . " with callback " . $fireAlarmKeyboard[0][0]['callback_data'] . "\n";

// Test 2: Simulate button press
echo "\nTest 2: Simulating button press\n";
$callbackData = $fireAlarmKeyboard[0][0]['callback_data'];
echo "  -> Callback data: $callbackData\n";

// Extract category
$category = substr($callbackData, strlen('cat_'));
echo "  -> Extracted category: $category\n";

// Test 3: Simulate additional works button press
echo "\nTest 3: Simulating additional works button press\n";
$additionalWorksButton = $fireAlarmKeyboard[2][0]['callback_data'];
echo "  -> Additional works callback data: $additionalWorksButton\n";

// Test 4: Check if additional works keyboard is created correctly
echo "\nTest 4: Creating additional works fire alarm keyboard\n";
$additionalWorksKeyboard = $messageService->createAdditionalWorksFireAlarmKeyboard();
echo "  -> Additional works keyboard created with " . count($additionalWorksKeyboard) . " rows\n";

// Print all buttons in additional works keyboard
foreach ($additionalWorksKeyboard as $index => $row) {
    foreach ($row as $button) {
        echo "  -> Button: " . $button['text'] . " with callback " . $button['callback_data'] . "\n";
    }
}

echo "\nTest completed!\n";
?>
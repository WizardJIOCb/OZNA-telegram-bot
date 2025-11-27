<?php

// Test the electrical equipment keyboard creation without database dependencies
echo "=== Testing Electrical Equipment Keyboard Creation ===\n\n";

// Simulate MessageService createElectricalEquipmentKeyboard method
function createElectricalEquipmentKeyboard() {
    $keyboard = [
        [
            ['text' => '📅 Работы по графику', 'callback_data' => 'category_Табель_РаботыПоГрафику_Электрооборудование']
        ],
        [
            ['text' => '📄 Формирование ИД', 'callback_data' => 'category_Табель_ФормированиеИД_Электрооборудование']
        ],
        [
            ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'category_Табель_ИзмСхемПодкл_ЭлектрОб']
        ],
        [
            ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'category_Табель_МонтажКабЛиний_ЭлектрОб']
        ],
        [
            ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'category_Табель_РасклКабЛиний_ЭлектрОб']
        ],
        [
            ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'category_Табель_ДопРаб_ЭлектрОб']
        ],
        [
            ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'category_Табель_ПодтвДостВехи_ЭлектрОб']
        ],
        [
            ['text' => '📝 Прочее', 'callback_data' => 'category_Табель_Прочее_Электрооборудование']
        ],
        [
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ПусконаладочныеРаботы']
        ]
    ];
    
    echo "Created keyboard with " . count($keyboard) . " rows\n";
    foreach ($keyboard as $index => $row) {
        echo "Row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    return $keyboard;
}

$keyboard = createElectricalEquipmentKeyboard();

echo "\n=== Keyboard JSON ===\n";
echo json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE) . "\n";

echo "\n=== Test Complete ===\n";
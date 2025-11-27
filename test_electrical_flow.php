<?php
// Comprehensive test for electrical equipment flow

require_once 'vendor/autoload.php';

// Mock classes
class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "SEND MESSAGE:\n";
        echo "  Chat ID: {$chatId}\n";
        echo "  Text: {$text}\n";
        if ($keyboard) {
            echo "  Keyboard has " . count($keyboard) . " rows\n";
            foreach ($keyboard as $rowIndex => $row) {
                echo "    Row {$rowIndex}: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
        echo "\n";
        return new \stdClass();
    }
    
    public function createConfirmationKeyboard($confirmCallbackData) {
        return [
            [
                ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
                ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
            ]
        ];
    }
    
    public function createElectricalEquipmentKeyboard() {
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
}

class MockTabelEntry {
    public function getConfirmationMessage($activityType, $projectCode) {
        $activityNames = [
            'РаботыПоГрафику_Электрооборудование' => 'Работы по графику (Электрооборудование)',
            'ФормированиеИД_Электрооборудование' => 'Формирование ИД (Электрооборудование)',
            'ПодтвДостВехи_ЭлектрОб' => 'Подтвердить достижение вехи (Электрооборудование)',
            'ДопРаб_ЭлектрОб' => 'Дополнительные работы (Электрооборудование)',
            'ИзмСхемПодкл_ЭлектрОб' => 'Изменение схем подключения (Электрооборудование)',
            'МонтажКабЛиний_ЭлектрОб' => 'Монтаж кабельных линий (Электрооборудование)',
            'РасклКабЛиний_ЭлектрОб' => 'Расключение кабельных линий (Электрооборудование)',
            'Прочее_Электрооборудование' => 'Прочее (Электрооборудование)'
        ];
        
        $activityName = $activityNames[$activityType] ?? $activityType;
        return "<b>{$activityName}</b>, отправить запись в табель в проект <b>{$projectCode}</b>?";
    }
}

// Simulate the complete flow
function testElectricalEquipmentFlow() {
    echo "=== Testing Electrical Equipment Flow ===\n\n";
    
    $messageService = new MockMessageService();
    $tabelEntry = new MockTabelEntry();
    
    // 1. Show electrical equipment menu
    echo "1. Showing electrical equipment menu:\n";
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    $messageService->sendMessage('test_chat_id', '<b>⚡ Электрооборудование</b>' . "\n\n" . 'Выберите тип работ:', $keyboard);
    
    // 2. User clicks "Работы по графику" button (callback: cat_ТабЭлОб_РабПоГр)
    echo "2. User clicked 'Работы по графику' button:\n";
    $category = 'cat_ТабЭлОб_РабПоГр';
    $conversation = new MockConversation();
    $conversation->notes['project_code'] = 'ELEC-2025';
    
    if ($category === 'cat_ТабЭлОб_РабПоГр') {
        $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        $confirmationText = $tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
        $keyboard = $messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
        $messageService->sendMessage('test_chat_id', $confirmationText, $keyboard);
    }
    
    // 3. User clicks "Да" button (callback: category_Табель_РаботыПоГрафику_Электрооборудование_yes)
    echo "3. User clicked 'Да' button:\n";
    $executionCategory = 'category_Табель_РаботыПоГрафику_Электрооборудование_yes';
    if (strpos($executionCategory, '_yes') && preg_match('/^Табель_(.+)_yes$/', $executionCategory, $matches)) {
        $activityType = $matches[1];
        $messageService->sendMessage('test_chat_id', "✅ Запись в табель от <b>Test User</b>, в проект <b>ELEC-2025</b> о <b>Работах по графику (Электрооборудование) добавлена</b>");
    }
}

testElectricalEquipmentFlow();
?>
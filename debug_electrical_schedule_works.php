<?php
// Debug script to test electrical equipment "Работы по графику" callback processing

require_once 'vendor/autoload.php';

// Mock classes to simulate the environment
class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "Sending message to chat {$chatId}:\n";
        echo "Text: {$text}\n";
        if ($keyboard) {
            echo "Keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "---\n";
        return new \stdClass(); // Mock response
    }
    
    public function createConfirmationKeyboard($confirmCallbackData) {
        return [
            [
                ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
                ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
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
            'ИзмСхемПодкл_ЭлектрОб' => 'Изменение схем подключения (Электрооборудование)',
            'МонтажКабЛиний_ЭлектрОб' => 'Монтаж кабельных линий (Электрооборудование)',
            'РасклКабЛиний_ЭлектрОб' => 'Расключение кабельных линий (Электрооборудование)',
            'Прочее_Электрооборудование' => 'Прочее (Электрооборудование)'
        ];
        
        $activityName = $activityNames[$activityType] ?? $activityType;
        return "<b>{$activityName}</b>, отправить запись в табель в проект <b>{$projectCode}</b>?";
    }
}

// Simulate the TabelHandler's handleOtherWorkTypes method
function debugHandleOtherWorkTypes($category, $projectCode = 'TEST-001') {
    echo "Debugging handleOtherWorkTypes with category: {$category}\n";
    echo "Project code: {$projectCode}\n\n";
    
    $conversation = new MockConversation();
    $conversation->notes['project_code'] = $projectCode;
    
    $messageService = new MockMessageService();
    $tabelEntry = new MockTabelEntry();
    
    // Handle works by schedule for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || 
        $category === 'cat_ТабЭлОб_РабПоГр') {
        echo "MATCH: Handling works by schedule for electrical equipment\n";
        $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
        $confirmationText = $tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
        $keyboard = $messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
        return $messageService->sendMessage('test_chat_id', $confirmationText, $keyboard);
    }
    
    echo "NO MATCH: Category did not match expected electrical equipment 'Работы по графику' patterns\n";
    return $messageService->sendMessage('test_chat_id', 'Неизвестная команда табеля');
}

// Test the specific case
echo "Testing 'cat_ТабЭлОб_РабПоГр' callback (electrical equipment works by schedule):\n";
echo "=====================================================================\n";
debugHandleOtherWorkTypes('cat_ТабЭлОб_РабПоГр');

echo "\n\nTesting 'Табель_РаботыПоГрафику_Электрооборудование' callback:\n";
echo "=====================================================================\n";
debugHandleOtherWorkTypes('Табель_РаботыПоГрафику_Электрооборудование');

echo "\n\nTesting unknown callback:\n";
echo "=====================================================================\n";
debugHandleOtherWorkTypes('unknown_callback');
?>
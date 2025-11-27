<?php
// Test script to verify the fix for electrical equipment "Работы по графику" callback processing

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
            'ИзмСхемПодкл_ЭлектрОб' => 'Изменение схем подключения (Электрооборудование)',
            'МонтажКабЛиний_ЭлектрОб' => 'Монтаж кабельных линий (Электрооборудование)',
            'РасклКабЛиний_ЭлектрОб' => 'Расключение кабельных линий (Электрооборудование)',
            'Прочее_Электрооборудование' => 'Прочее (Электрооборудование)'
        ];
        
        $activityName = $activityNames[$activityType] ?? $activityType;
        return "<b>{$activityName}</b>, отправить запись в табель в проект <b>{$projectCode}</b>?";
    }
}

// Simulate the TabelHandler's handle method with the fixed logic
function debugHandleWithFixedLogic($callbackData, $projectCode = 'TEST-001') {
    echo "Debugging handle method with callbackData: {$callbackData}\n";
    echo "Project code: {$projectCode}\n\n";
    
    // Extract the actual category from the callback data
    $actualCategory = extractCategoryFromCallback($callbackData);
    echo "Extracted actual category: {$actualCategory}\n";
    
    $conversation = new MockConversation();
    $conversation->notes['project_code'] = $projectCode;
    
    $messageService = new MockMessageService();
    $tabelEntry = new MockTabelEntry();
    
    // Special handling for electrical equipment with encoding issues
    // This should come AFTER category extraction to avoid interfering with short callback formats
    if (strpos($callbackData, 'Электрооборудование') !== false) {
        // Only handle as general electrical equipment menu if it's not a specific activity
        if ($actualCategory === 'Табель_Электрооборудование' || 
            $actualCategory === '' ||
            ($actualCategory !== 'Табель_РаботыПоГрафику_Электрооборудование' &&
             $actualCategory !== 'Табель_ФормированиеИД_Электрооборудование' &&
             $actualCategory !== 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование' &&
             $actualCategory !== 'Табель_ДопРаб_ЭлектрОб' &&
             $actualCategory !== 'Табель_ИзменениеСхемПодключения_Электрооборудование' &&
             $actualCategory !== 'Табель_МонтажКабельныхЛиний_Электрооборудование' &&
             $actualCategory !== 'Табель_РасключениеКабельныхЛиний_Электрооборудование' &&
             $actualCategory !== 'Табель_Прочее_Электрооборудование')) {
            echo "Special handling: showing electrical equipment menu\n";
            $keyboard = $messageService->createElectricalEquipmentKeyboard();
            return $messageService->sendMessage('test_chat_id', '<b>⚡ Электрооборудование</b>' . "\n\n" . 'Выберите тип работ:', $keyboard);
        } else {
            echo "Special handling: NOT showing electrical equipment menu, continuing with normal processing\n";
        }
    }
    
    // Handle works by schedule for electrical equipment (must come before general electrical equipment check)
    if ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
        $actualCategory === 'cat_ТабЭлОб_РабПоГр') {
        echo "MATCH: Handling works by schedule for electrical equipment\n";
        $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
        $confirmationText = $tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
        $keyboard = $messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
        return $messageService->sendMessage('test_chat_id', $confirmationText, $keyboard);
    }
    
    echo "NO MATCH: Category did not match expected electrical equipment 'Работы по графику' patterns\n";
    return $messageService->sendMessage('test_chat_id', 'Неизвестная команда табеля');
}

// Extract category from callback data for both original and short formats
function extractCategoryFromCallback(string $callbackData): string
{
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for electrical equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_РабПоГр' => 'Табель_РаботыПоГрафику_Электрооборудование',
            'cat_ТабЭлОб_ФормИД' => 'Табель_ФормированиеИД_Электрооборудование',
            'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзменениеСхемПодключения_Электрооборудование',
            'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтажКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
            'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
            'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

// Test the specific case
echo "Testing 'cat_ТабЭлОб_РабПоГр' callback (electrical equipment works by schedule):\n";
echo "=====================================================================\n";
debugHandleWithFixedLogic('cat_ТабЭлОб_РабПоГр');

echo "\n\nTesting 'Табель_РаботыПоГрафику_Электрооборудование' callback:\n";
echo "=====================================================================\n";
debugHandleWithFixedLogic('category_Табель_РаботыПоГрафику_Электрооборудование');

echo "\n\nTesting electrical equipment menu callback:\n";
echo "=====================================================================\n";
debugHandleWithFixedLogic('cat_ТабЭлОб_РабПоГр_with_Электрооборудование'); // Simulate encoding issue
?>
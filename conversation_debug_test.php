<?php
// Conversation debug test to check if conversation data is causing issues

echo "CONVERSATION DEBUG TEST: 'Работы по графику' processing\n";
echo "====================================================\n\n";

// Simulate a conversation object with various states
class MockConversation {
    public array $notes = [];
    
    public function __construct(array $notes = []) {
        $this->notes = $notes;
    }
    
    public function update() {
        // Mock update method
        echo "   Conversation updated\n";
    }
}

// Simulate the TabelHandler processing with conversation data
class ConversationDebugTest {
    public function testWithDifferentConversationStates() {
        echo "TESTING WITH DIFFERENT CONVERSATION STATES:\n";
        echo "==========================================\n\n";
        
        // Test case 1: Normal conversation with project code
        echo "TEST CASE 1: Normal conversation with project code\n";
        $conversation1 = new MockConversation([
            'project_code' => 'PROJECT-123',
            'tabel_data' => '01.01.2023'
        ]);
        $this->testProcessing('cat_ТабЭлОб_РабПоГр', $conversation1);
        
        echo "\n" . str_repeat("-", 50) . "\n\n";
        
        // Test case 2: Conversation without project code
        echo "TEST CASE 2: Conversation without project code\n";
        $conversation2 = new MockConversation([
            'tabel_data' => '01.01.2023'
        ]);
        $this->testProcessing('cat_ТабЭлОб_РабПоГр', $conversation2);
        
        echo "\n" . str_repeat("-", 50) . "\n\n";
        
        // Test case 3: Empty conversation
        echo "TEST CASE 3: Empty conversation\n";
        $conversation3 = new MockConversation([]);
        $this->testProcessing('cat_ТабЭлОб_РабПоГр', $conversation3);
    }
    
    private function testProcessing(string $callbackData, MockConversation $conversation) {
        echo "Processing callback: '{$callbackData}'\n";
        
        // Extract category
        $actualCategory = $this->extractCategoryFromCallback($callbackData);
        echo "Extracted category: '{$actualCategory}'\n";
        
        // Check if it's "Работы по графику"
        $isScheduleWorks = ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
                           $actualCategory === 'cat_ТабЭлОб_РабПоГр');
        echo "Is 'Работы по графику': " . ($isScheduleWorks ? 'YES' : 'NO') . "\n";
        
        if ($isScheduleWorks) {
            // Try to get project code from conversation
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            echo "Project code from conversation: '{$projectCode}'\n";
            
            // Try to get date from conversation
            $date = $conversation->notes['tabel_data'] ?? date('d.m.Y');
            echo "Date from conversation: '{$date}'\n";
            
            // Prepare confirmation message
            $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
            $confirmationText = $this->getConfirmationMessage($strippedCategory, $projectCode);
            echo "Confirmation text: \"" . substr($confirmationText, 0, 50) . (strlen($confirmationText) > 50 ? '...' : '') . "\"\n";
            
            // Create keyboard
            $keyboard = $this->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
            echo "Keyboard created: " . (count($keyboard) > 0 ? 'YES' : 'NO') . "\n";
            
            echo "✅ Processing would succeed with this conversation state\n";
        } else {
            echo "❌ Not recognized as 'Работы по графику'\n";
        }
    }
    
    private function extractCategoryFromCallback(string $callbackData): string {
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
    
    private function getConfirmationMessage(string $activityType, string $projectCode): string {
        $activityNames = [
            // New electrical equipment activity types
            'РаботыПоГрафику_Электрооборудование' => 'Работы по графику (Электрооборудование)',
            'ФормированиеИД_Электрооборудование' => 'Формирование ИД (Электрооборудование)',
            'ПодтвДостВехи_ЭлектрОб' => 'Подтвердить достижение вехи (Электрооборудование)',
            'ИзмСхемПодкл_ЭлектрОб' => 'Изменение схем подключения (Электрооборудование)',
            'МонтажКабЛиний_ЭлектрОб' => 'Монтаж кабельных линий (Электрооборудование)',
            'РасклКабЛиний_ЭлектрОб' => 'Расключение кабельных линий (Электрооборудование)',
            'Прочее_Электрооборудование' => 'Прочее (Электрооборудование)',
            // Electrical equipment main category
            'Электрооборудование' => 'Электрооборудование'
        ];

        $activityName = $activityNames[$activityType] ?? $activityType;
        
        return sprintf(
            "<b>%s</b>, отправить запись в табель в проект <b>%s</b>?",
            $activityName,
            $projectCode
        );
    }
    
    private function createConfirmationKeyboard(string $confirmCallbackData): array {
        return [
            [
                ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
                ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
            ]
        ];
    }
}

$test = new ConversationDebugTest();
$test->testWithDifferentConversationStates();

echo "\n" . str_repeat("=", 60) . "\n";
echo "CONCLUSION:\n";
echo "If all test cases show 'Processing would succeed', then the issue\n";
echo "is not with the conversation data. The problem might be:\n";
echo "1. The callback is not reaching the TabelHandler at all\n";
echo "2. There's an exception being thrown that's not visible\n";
echo "3. The sendMessage method is failing silently\n";
echo "4. Telegram client issues\n";
echo "5. Caching issues\n";
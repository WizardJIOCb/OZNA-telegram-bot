<?php
// Complete flow test to simulate the entire "Работы по графику" process

echo "COMPLETE FLOW TEST: 'Работы по графику' processing\n";
echo "================================================\n\n";

// Simulate the complete flow
class CompleteFlowTest {
    public function testCompleteFlow() {
        echo "SIMULATING COMPLETE FLOW:\n";
        echo "========================\n";
        
        // Step 1: User clicks "Работы по графику" button
        $callbackData = 'cat_ТабЭлОб_РабПоГр';
        echo "1. User clicks 'Работы по графику' button\n";
        echo "   Callback data: '{$callbackData}'\n\n";
        
        // Step 2: System processes in TabelHandler::handle()
        echo "2. TabelHandler::handle() processing...\n";
        
        // Extract category
        $actualCategory = $this->extractCategoryFromCallback($callbackData);
        echo "   Extracted actualCategory: '{$actualCategory}'\n";
        
        // Check if it's "Работы по графику"
        $isScheduleWorks = ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
                           $actualCategory === 'cat_ТабЭлОб_РабПоГр');
        echo "   Is 'Работы по графику': " . ($isScheduleWorks ? 'YES' : 'NO') . "\n\n";
        
        if (!$isScheduleWorks) {
            echo "❌ ERROR: Not recognized as 'Работы по графику'\n";
            return;
        }
        
        // Step 3: Prepare confirmation message
        echo "3. Preparing confirmation message...\n";
        $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
        $projectCode = 'TEST-PROJECT'; // Simulated project code
        $confirmationText = $this->getConfirmationMessage($strippedCategory, $projectCode);
        echo "   Confirmation text: \"{$confirmationText}\"\n\n";
        
        // Step 4: Create confirmation keyboard
        echo "4. Creating confirmation keyboard...\n";
        $keyboard = $this->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
        echo "   Keyboard structure:\n";
        foreach ($keyboard as $rowIndex => $row) {
            echo "   Row {$rowIndex}: ";
            foreach ($row as $buttonIndex => $button) {
                echo "[{$button['text']}: {$button['callback_data']}] ";
            }
            echo "\n";
        }
        echo "\n";
        
        // Step 5: Send message
        echo "5. Sending confirmation message...\n";
        $chatId = '123456789'; // Simulated chat ID
        $result = $this->simulateSendMessage($chatId, $confirmationText, $keyboard);
        echo "   Message send result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n\n";
        
        echo "✅ COMPLETE FLOW TEST FINISHED\n";
        echo "If all steps show SUCCESS, the issue is likely elsewhere.\n";
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
    
    private function simulateSendMessage(string $chatId, string $text, array $keyboard): bool {
        echo "   Simulating sendMessage to chat {$chatId}\n";
        echo "   Text: " . substr($text, 0, 50) . (strlen($text) > 50 ? '...' : '') . "\n";
        echo "   Keyboard: " . json_encode($keyboard) . "\n";
        
        // In real implementation, this would call Telegram API
        // For simulation, we'll just return true
        return true;
    }
}

$test = new CompleteFlowTest();
$test->testCompleteFlow();

echo "\n" . str_repeat("=", 60) . "\n";
echo "NEXT STEPS TO DEBUG:\n";
echo "1. Check error logs for any issues\n";
echo "2. Verify that the sendMessage method is actually being called\n";
echo "3. Check if there are any network issues with Telegram API\n";
echo "4. Verify that the conversation object has the correct data\n";
echo "5. Check if there are any exceptions being thrown\n";
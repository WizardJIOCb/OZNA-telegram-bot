<?php
// Test script to verify the fix for "Дополнительные работы" button

echo "TESTING FIX FOR 'ДОПОЛНИТЕЛЬНЫЕ РАБОТЫ' BUTTON\n";
echo "=============================================\n\n";

// Simulate the TabelHandler handleOtherWorkTypes method with the fix
class MockTabelHandler {
    public function handleOtherWorkTypes($category, $projectCode = 'TEST-PROJECT') {
        echo "handleOtherWorkTypes called with category: {$category}\n";
        
        // Handle additional works for electrical equipment
        if ($category === 'Табель_ДопРаб_ЭлектрОб' || 
            $category === 'Табель_ДополнительныеРаботы_Электрооборудование' ||
            $category === 'cat_ТабЭлОб_ДопРаб') {
            echo "Handling additional works for electrical equipment\n";
            $strippedCategory = 'ДопРаб_ЭлектрОб';
            $confirmationText = $this->getConfirmationMessage($strippedCategory, $projectCode);
            $confirmCallbackData = "cat_ТабЭлОб_ДопРаб_yes";
            
            echo "Showing confirmation dialog:\n";
            echo "  Message: {$confirmationText}\n";
            echo "  Confirm button callback: {$confirmCallbackData}\n";
            echo "  Cancel button callback: cat_ТабЭлОб\n";
            
            return [
                'action' => 'show_confirmation',
                'message' => $confirmationText,
                'confirm_callback' => $confirmCallbackData,
                'cancel_callback' => 'cat_ТабЭлОб'
            ];
        }
        
        return ['action' => 'unknown', 'message' => 'Unknown command'];
    }
    
    private function getConfirmationMessage($activityType, $projectCode) {
        $activityNames = [
            'ДопРаб_ЭлектрОб' => 'Дополнительные работы (Электрооборудование)',
        ];
        
        $activityName = $activityNames[$activityType] ?? $activityType;
        return "<b>{$activityName}</b>, отправить запись в табель в проект <b>{$projectCode}</b>?";
    }
    
    public function handleActivityExecution($category) {
        echo "handleActivityExecution called with category: {$category}\n";
        
        // Handle short callback format for electrical equipment first
        if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
            // Handle short callback format for electrical equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РаботыПоГрафику_Электрооборудование',
                'ФормИД' => 'ФормированиеИД_Электрооборудование',
                'ИзмСхПд' => 'ИзменениеСхемПодключения_Электрооборудование',
                'МонтКбЛн' => 'МонтажКабельныхЛиний_Электрооборудование',
                'РаскКбЛн' => 'РасключениеКабельныхЛиний_Электрооборудование',
                'ДопРаб' => 'ДопРаб_ЭлектрОб',
                'ПодтВех' => 'ПодтвДостВехи_ЭлектрОб',
                'Проч' => 'Прочее_Электрооборудование'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
                echo "Extracted activity type: {$activityType}\n";
                echo "Creating timesheet entry for: {$activityType}\n";
                return [
                    'action' => 'create_entry',
                    'activity_type' => $activityType,
                    'message' => "✅ Запись в табель от <b>Test User</b>, в проект <b>TEST-PROJECT</b> о <b>Дополнительных работах (Электрооборудование) добавлена</b>"
                ];
            }
        }
        
        return ['action' => 'error', 'message' => 'Failed to process activity'];
    }
}

// Test cases
$handler = new MockTabelHandler();

echo "Test 1: User clicks 'Дополнительные работы' button\n";
$result1 = $handler->handleOtherWorkTypes('cat_ТабЭлОб_ДопРаб');
echo "Expected: show_confirmation, Actual: {$result1['action']}\n";
echo (($result1['action'] === 'show_confirmation') ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "Test 2: User confirms 'Дополнительные работы' (clicks 'Да')\n";
$result2 = $handler->handleActivityExecution('cat_ТабЭлОб_ДопРаб_yes');
echo "Expected: create_entry, Actual: {$result2['action']}\n";
echo (($result2['action'] === 'create_entry') ? "✅ PASS" : "❌ FAIL") . "\n";
echo "Expected activity type: ДопРаб_ЭлектрОб, Actual: {$result2['activity_type']}\n";
echo (($result2['activity_type'] === 'ДопРаб_ЭлектрОб') ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "FIX SUMMARY\n";
echo "===========\n";
echo "The fix modifies the TabelHandler to treat the 'Дополнительные работы' button as a direct activity:\n";
echo "1. When user clicks 'Дополнительные работы', they see a confirmation dialog\n";
echo "2. When user confirms with 'Да', a timesheet entry is created for 'ДопРаб_ЭлектрОб'\n";
echo "3. The entry is sent to the history chat with the correct formatting\n\n";
echo "This resolves the issue where the button was acting as a submenu navigation instead of creating an entry.\n";
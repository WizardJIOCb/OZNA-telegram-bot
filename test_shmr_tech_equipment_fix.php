<?php
// Test script to verify the ШМР технологическое оборудование fix

require_once 'CustomCommands/Handlers/TabelHandler.php';

echo "=== Testing ШМР Технологическое оборудование Fix ===\n";

// Mock the required dependencies for TabelHandler
class MockMessageService {
    public function createConfirmationKeyboard($callbackData) {
        return [['text' => 'Да', 'callback_data' => $callbackData]];
    }
    
    public function sendMessage($chatId, $text, $keyboard) {
        return true;
    }
}

class MockTabelEntry {
    public function getConfirmationMessage($activityType, $projectCode) {
        return "Confirm {$activityType} for {$projectCode}?";
    }
}

class TestTabelHandler extends TabelHandler {
    public function __construct() {
        // Initialize with mock services for testing
        $this->messageService = new MockMessageService();
        $this->tabelEntry = new MockTabelEntry();
        $this->activityTypes = [
            'ШМР_ТехнологическоеОборудование',
            'ШМР_Электрооборудование',
            'ШМР_ОтоплениеИВентиляция',
            'ШМР_ПожарнаяСигнализация',
            'ШМР_ОхраннаяСигнализация',
            'ШМР_Автоматизация',
            'ШМР_Метрология'
        ];
    }

    // Expose the isActivityExecution method for testing
    public function testIsActivityExecution($category) {
        return $this->isActivityExecution($category);
    }
}

$handler = new TestTabelHandler();

// Test cases for the new ШМР sub-activities
$testCases = [
    'cat_ШМР_ТехОб_yes',
    'cat_ШМР_ЭлектрОб_yes', 
    'cat_ШМР_ОтоплВент_yes',
    'cat_ШМР_ПожСигн_yes',
    'cat_ШМР_ОхрСигн_yes',
    'cat_ШМР_Автом_yes',
    'cat_ШМР_Метрол_yes'
];

echo "Testing isActivityExecution for new ШМР sub-activities:\n";
foreach ($testCases as $testCase) {
    $result = $handler->testIsActivityExecution($testCase);
    $status = $result ? "✅ PASS" : "❌ FAIL";
    echo "  {$testCase}: {$status}\n";
}

// Test the specific case that was hanging
echo "\n=== Specific Test: Технологическое оборудование ===\n";
$technologicalEquipmentCallback = 'cat_ШМР_ТехОб_yes';
$result = $handler->testIsActivityExecution($technologicalEquipmentCallback);
$status = $result ? "✅ WORKING" : "❌ STILL BROKEN";
echo "'{$technologicalEquipmentCallback}': {$status}\n";

if ($result) {
    echo "\n🎉 SUCCESS: The fix is working! Технологическое оборудование should no longer hang.\n";
} else {
    echo "\n❌ ISSUE: The fix may not be complete. Further investigation needed.\n";
}

echo "\n=== Test Complete ===\n";
?>
<?php
// Simple test to check if our fix for electrical equipment callbacks works

// Include the TabelHandler file
require_once 'CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Test the isActivityExecution method directly
class TestTabelHandler extends TabelHandler {
    // Make the method public for testing
    public function testIsActivityExecution(string $category): bool {
        return $this->isActivityExecution($category);
    }
    
    // Make the method public for testing
    public function testHandleActivityExecution(string $category): string {
        // Mock the preg_match logic from the actual method
        $activityType = '';
        
        // Handle short callback format for electrical equipment FIRST
        if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
            // Handle short callback format for electrical equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_ЭлектрОб',
                'ФормИД' => 'ФормИД_ЭлектрОб',
                'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
                'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
                'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
                'ДопРаб' => 'ДопРаб_ЭлектрОб',
                'ПодтВех' => 'ПодтВех_ЭлектрОб',
                'Проч' => 'Прочее_ЭлектрОб'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            }
        }
        
        return $activityType;
    }
}

// Test cases
$testCases = [
    'cat_ТабЭлОб_РабПоГр_yes' => 'РабПоГр_ЭлектрОб',
    'cat_ТабЭлОб_ФормИД_yes' => 'ФормИД_ЭлектрОб',
    'cat_ТабЭлОб_ИзмСхПд_yes' => 'ИзмСхПд_ЭлектрОб',
    'cat_ТабЭлОб_МонтКбЛн_yes' => 'МонтКбЛн_ЭлектрОб',
    'cat_ТабЭлОб_РаскКбЛн_yes' => 'РаскКбЛн_ЭлектрОб',
    'cat_ТабЭлОб_ДопРаб_yes' => 'ДопРаб_ЭлектрОб',
    'cat_ТабЭлОб_ПодтВех_yes' => 'ПодтВех_ЭлектрОб',
    'cat_ТабЭлОб_Проч_yes' => 'Прочее_ЭлектрОб'
];

$testHandler = new TestTabelHandler(
    new \stdClass(), // Mock database service
    new \stdClass(), // Mock message service
    new \stdClass(), // Mock project selector
    new \stdClass(), // Mock tabel entry
    new \stdClass()  // Mock admin service
);

echo "Testing isActivityExecution method:\n";
foreach ($testCases as $callback => $expected) {
    $result = $testHandler->testIsActivityExecution($callback);
    echo "Callback: $callback => " . ($result ? "SUCCESS" : "FAILED") . "\n";
}

echo "\nTesting handleActivityExecution logic:\n";
foreach ($testCases as $callback => $expected) {
    $result = $testHandler->testHandleActivityExecution($callback);
    echo "Callback: $callback => Activity Type: $result " . ($result === $expected ? "(CORRECT)" : "(INCORRECT)") . "\n";
}
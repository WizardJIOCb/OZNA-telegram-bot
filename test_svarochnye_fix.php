<?php
// Test script to verify the fix for "Сварочные работы" button

// Include the TabelHandler class
require_once 'CustomCommands/Handlers/TabelHandler.php';

// Create a mock class to test the isActivityExecution method
class TabelHandlerTest extends Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler {
    // Override the constructor to avoid dependencies
    public function __construct() {
        // Empty constructor for testing
    }
    
    // Public method to test isActivityExecution
    public function testIsActivityExecution($category) {
        return $this->isActivityExecution($category);
    }
}

// Create an instance of the test class
$handler = new TabelHandlerTest();

// Test the "Сварочные" activity execution
$testCategory = 'Табель_Сварочные_yes';
$result = $handler->testIsActivityExecution($testCategory);

echo "Testing isActivityExecution with category: {$testCategory}\n";
echo "Result: " . ($result ? 'true' : 'false') . "\n";

// Also test the handleOtherWorkTypes method indirectly by checking if "Сварочные" is in activityTypes
echo "\nChecking if 'Сварочные' is in activityTypes array:\n";
$reflection = new ReflectionClass($handler);
$activityTypesProperty = $reflection->getProperty('activityTypes');
$activityTypesProperty->setAccessible(true);
$activityTypes = $activityTypesProperty->getValue($handler);

if (in_array('Сварочные', $activityTypes)) {
    echo "'Сварочные' found in activityTypes array\n";
} else {
    echo "'Сварочные' NOT found in activityTypes array\n";
}

echo "\nActivity types array contents:\n";
foreach ($activityTypes as $type) {
    echo "- {$type}\n";
}
?>
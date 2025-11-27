<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

echo "=== DEBUGGING COMPLETE SUPERVISION WORKS FLOW ===\n\n";

// Test 1: Check if users can reach supervision works menu
echo "1. TESTING ACCESS TO SUPERVISION WORKS MENU:\n";
echo "   - Callback: 'category_Табель_ШМР'\n";
echo "   - Should trigger: handleSupervisionWorksMenu()\n\n";

// Test 2: Check supervision works keyboard generation
echo "2. TESTING SUPERVISION WORKS KEYBOARD GENERATION:\n";
$messageService = new \CustomCommands\Services\MessageService();
try {
    $keyboard = $messageService->createSupervisionWorksKeyboard();
    echo "   ✓ Supervision works keyboard created successfully\n";
    echo "   - Buttons should send callbacks: cat_ШМР_Подг, cat_ШМР_Монт, cat_ШМР_Сбор, cat_ШМР_Внутр, cat_ШМР_Пуск\n\n";
} catch (Exception $e) {
    echo "   ✗ Error creating supervision works keyboard: " . $e->getMessage() . "\n\n";
}

// Test 3: Check TabelHandler callback detection
echo "3. TESTING TABELHANDLER CALLBACK DETECTION:\n";
$testCallbacks = [
    'category_Табель_ШМР',
    'cat_ШМР_Подг', 
    'cat_ШМР_Монт',
    'cat_ШМР_Сбор',
    'cat_ШМР_Внутр',
    'cat_ШМР_Пуск'
];

$tabelHandler = new \CustomCommands\Handlers\TabelHandler();

foreach ($testCallbacks as $callback) {
    $canHandle = $tabelHandler->canHandle($callback);
    echo "   Callback: '$callback' -> " . ($canHandle ? "✓ CAN HANDLE" : "✗ CANNOT HANDLE") . "\n";
}
echo "\n";

// Test 4: Check activity execution detection
echo "4. TESTING ACTIVITY EXECUTION DETECTION:\n";
$supervisionCallbacks = ['cat_ШМР_Подг', 'cat_ШМР_Монт', 'cat_ШМР_Сбор', 'cat_ШМР_Внутр', 'cat_ШМР_Пуск'];

// Use reflection to access private method
$reflection = new ReflectionClass($tabelHandler);
$isActivityExecutionMethod = $reflection->getMethod('isActivityExecution');
$isActivityExecutionMethod->setAccessible(true);

foreach ($supervisionCallbacks as $callback) {
    $isExecution = $isActivityExecutionMethod->invoke($tabelHandler, $callback);
    echo "   Callback: '$callback' -> " . ($isExecution ? "✓ IS ACTIVITY EXECUTION" : "✗ NOT ACTIVITY EXECUTION") . "\n";
}
echo "\n";

// Test 5: Check activity types registration
echo "5. TESTING ACTIVITY TYPES REGISTRATION:\n";
$activityTypesProperty = $reflection->getProperty('activityTypes');
$activityTypesProperty->setAccessible(true);
$activityTypes = $activityTypesProperty->getValue($tabelHandler);

$expectedSupervisionTypes = [
    'ПодготовительныеРаботы_ШМР',
    'МонтажБлоков_ШМР', 
    'СборкаЗдания_ШМР',
    'ВнутриблочныйМонтаж_ШМР',
    'ПускоНаладочныеРаботы_ШМР'
];

foreach ($expectedSupervisionTypes as $activityType) {
    $isRegistered = in_array($activityType, $activityTypes);
    echo "   Activity Type: '$activityType' -> " . ($isRegistered ? "✓ REGISTERED" : "✗ NOT REGISTERED") . "\n";
}
echo "\n";

// Test 6: Check handleActivityExecution mapping
echo "6. TESTING HANDLEACTIVITYEXECUTION MAPPING:\n";
$handleActivityExecutionMethod = $reflection->getMethod('handleActivityExecution');
$handleActivityExecutionMethod->setAccessible(true);

foreach ($supervisionCallbacks as $callback) {
    try {
        echo "   Testing callback: '$callback'\n";
        echo "   - Should map to confirmation handler\n";
        
        // Check if the callback maps to correct activity type
        $expectedMappings = [
            'cat_ШМР_Подг' => 'ПодготовительныеРаботы_ШМР',
            'cat_ШМР_Монт' => 'МонтажБлоков_ШМР',
            'cat_ШМР_Сбор' => 'СборкаЗдания_ШМР',
            'cat_ШМР_Внутр' => 'ВнутриблочныйМонтаж_ШМР',
            'cat_ШМР_Пуск' => 'ПускоНаладочныеРаботы_ШМР'
        ];
        
        if (isset($expectedMappings[$callback])) {
            echo "   - Expected mapping: {$expectedMappings[$callback]}\n";
        }
        
    } catch (Exception $e) {
        echo "   ✗ Error testing callback '$callback': " . $e->getMessage() . "\n";
    }
}
echo "\n";

echo "=== DEBUG COMPLETE ===\n";
echo "Please check the results above to identify where the callback chain is breaking.\n";
?>
<?php
// Test to verify handler registration and priority

echo "Testing Handler Registration and Priority\n";
echo "=======================================\n\n";

// Simulate the handler registration process
class MockHandler {
    private $name;
    private $priority;
    
    public function __construct($name, $priority) {
        $this->name = $name;
        $this->priority = $priority;
    }
    
    public function getPriority() {
        return $this->priority;
    }
    
    public function getName() {
        return $this->name;
    }
}

// Create mock handlers with their actual priorities
$handlers = [
    new MockHandler('AdminHandler', 25),
    new MockHandler('AccessLevelHandler', 25),
    new MockHandler('SupervisorHandler', 25),
    new MockHandler('TabelHandler', 70),  // Our fix
    new MockHandler('ProfileHandler', 15),
    new MockHandler('FunctionalDirectionHandler', 10),
    new MockHandler('HelpHandler', 1),
    new MockHandler('MainMenuHandler', 50)
];

echo "1. Original handler priorities:\n";
foreach ($handlers as $handler) {
    echo "   {$handler->getName()}: {$handler->getPriority()}\n";
}

// Sort handlers by priority (highest first) - this is what CallbackHandlerFactory does
usort($handlers, function($a, $b) {
    return $b->getPriority() - $a->getPriority();
});

echo "\n2. Sorted handler order (processing order):\n";
foreach ($handlers as $index => $handler) {
    echo "   " . ($index + 1) . ". {$handler->getName()} (priority: {$handler->getPriority()})\n";
}

echo "\n3. Testing callback handling:\n";
$callbackData = 'category_Табель_Мобильная_Бригада';

echo "   Callback data: $callbackData\n";

// Simulate canHandle for each handler
$canHandleResults = [
    'AdminHandler' => false,
    'AccessLevelHandler' => false,
    'SupervisorHandler' => false,
    'TabelHandler' => true,  // Should handle this
    'ProfileHandler' => false,
    'FunctionalDirectionHandler' => false,
    'HelpHandler' => false,
    'MainMenuHandler' => false
];

echo "   Handler capability check:\n";
foreach ($canHandleResults as $handlerName => $canHandle) {
    echo "   - $handlerName: " . ($canHandle ? 'YES' : 'NO') . "\n";
}

echo "\n4. Expected processing flow:\n";
echo "   - First handler to check: {$handlers[0]->getName()} (priority: {$handlers[0]->getPriority()})\n";
echo "   - {$handlers[0]->getName()} canHandle: " . ($canHandleResults[$handlers[0]->getName()] ? 'YES' : 'NO') . "\n";

if ($canHandleResults[$handlers[0]->getName()]) {
    echo "   - {$handlers[0]->getName()} will process the callback\n";
    echo "   - User should see the mobile brigade date selection menu\n";
} else {
    echo "   - {$handlers[0]->getName()} cannot handle the callback\n";
    echo "   - Moving to next handler...\n";
}

echo "\nTest completed.\n";
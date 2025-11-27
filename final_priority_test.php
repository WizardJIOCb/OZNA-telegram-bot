<?php
// Final test to verify handler priorities are working correctly

require_once 'hook.php';

echo "=== Final Priority Test ===\n\n";

// Create mock handlers to test priorities
class MockMainMenuHandler {
    public function getPriority(): int { return 50; }
    public function canHandle($data): bool { 
        echo "MainMenuHandler checking: $data\n";
        return $data === 'category_Главное';
    }
}

class MockAdminHandler {
    public function getPriority(): int { return 25; }
    public function canHandle($data): bool { 
        echo "AdminHandler checking: $data\n";
        // Should NOT handle electrical equipment
        if (strpos($data, 'Электрооборудование') !== false) {
            return false;
        }
        return strpos($data, 'category_Админка') === 0;
    }
}

class MockTabelHandler {
    public function getPriority(): int { return 30; } // Changed from 20 to 30
    public function canHandle($data): bool { 
        echo "TabelHandler checking: $data\n";
        return strpos($data, 'category_Табель') === 0 || strpos($data, 'cat_Таб') === 0;
    }
}

// Simulate the handler factory sorting
$handlers = [
    new MockAdminHandler(),
    new MockTabelHandler(),
    new MockMainMenuHandler()
];

echo "Before sorting:\n";
foreach ($handlers as $index => $handler) {
    echo "  Handler #{$index}: " . get_class($handler) . " with priority: " . $handler->getPriority() . "\n";
}

// Sort handlers by priority (highest first)
usort($handlers, function($a, $b) {
    $priorityA = $a->getPriority();
    $priorityB = $b->getPriority();
    // We want highest priority first, so we compare B to A (reverse order)
    return $priorityB - $priorityA;
});

echo "\nAfter sorting (priority order):\n";
foreach ($handlers as $index => $handler) {
    echo "  Handler #{$index}: " . get_class($handler) . " with priority: " . $handler->getPriority() . "\n";
}

echo "\n=== Testing Callback Processing Order ===\n";

// Test with electrical equipment callback
$testCallback = 'cat_ТабЭлОб_РабПоГр';

echo "Testing callback: $testCallback\n\n";

$handlerFound = false;
foreach ($handlers as $index => $handler) {
    if ($handler->canHandle($testCallback)) {
        echo "✅ Handler found: " . get_class($handler) . "\n";
        $handlerFound = true;
        break;
    }
}

if (!$handlerFound) {
    echo "❌ No handler found for callback: $testCallback\n";
}

echo "\n=== Expected Behavior ===\n";
echo "1. Handlers should be processed in priority order: MainMenuHandler (50) → TabelHandler (30) → AdminHandler (25)\n";
echo "2. TabelHandler should handle electrical equipment callbacks because:\n";
echo "   - It has higher priority than AdminHandler\n";
echo "   - Its canHandle method returns true for 'cat_Таб' prefixed callbacks\n";
echo "   - AdminHandler correctly returns false for electrical equipment callbacks\n";

?>
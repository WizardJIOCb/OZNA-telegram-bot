<?php

// Test handler priority processing
echo "Testing Handler Priority Processing\n";
echo "=================================\n";

// Simulate handler classes
class MockMainMenuHandler {
    public function getPriority(): int { return 50; }
    public function canHandle($data): bool { 
        echo "MainMenuHandler checking: $data\n";
        return $data === 'category_Главное';
    }
}

class MockProjectManagementHandler {
    public function getPriority(): int { return 55; }
    public function canHandle($data): bool { 
        echo "ProjectManagementHandler checking: $data\n";
        return str_starts_with($data, 'category_УправлениеПроектом');
    }
}

class MockAdminHandler {
    public function getPriority(): int { return 25; }
    public function canHandle($data): bool { 
        echo "AdminHandler checking: $data\n";
        return str_starts_with($data, 'category_Админка');
    }
}

// Create handlers
$handlers = [
    new MockMainMenuHandler(),
    new MockProjectManagementHandler(),
    new MockAdminHandler()
];

// Sort handlers by priority (highest first)
usort($handlers, function($a, $b) {
    return $b->getPriority() - $a->getPriority();
});

echo "Handler priority order:\n";
foreach ($handlers as $index => $handler) {
    echo "  {$index}: " . get_class($handler) . " (priority: " . $handler->getPriority() . ")\n";
}

// Test callback processing
$testCallback = 'category_УправлениеПроектом';

echo "\nProcessing callback: '{$testCallback}'\n";
echo "--------------------------------\n";

foreach ($handlers as $index => $handler) {
    echo "Checking handler #{$index}: " . get_class($handler) . "\n";
    $canHandle = $handler->canHandle($testCallback);
    if ($canHandle) {
        echo "  -> HANDLED by " . get_class($handler) . "\n";
        break;
    } else {
        echo "  -> NOT HANDLED\n";
    }
}

echo "\nTest completed!\n";
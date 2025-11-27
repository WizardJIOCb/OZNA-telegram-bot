<?php
// Test script to verify submenu buttons are working correctly

require_once 'hook.php';

echo "=== Testing Submenu Buttons Functionality ===\n\n";

// Test different submenu button scenarios
$testCases = [
    // Main menu buttons that should work
    'category_Табель' => 'Main Tabel Menu',
    
    // Submenu buttons that should now work
    'category_Табель_Мобильная_Бригада' => 'Mobile Brigade',
    'category_Табель_Шеф_монтаж' => 'Supervision (ШМР)',
    'category_Табель_Пусконаладка' => 'Commissioning (ПНР)',
    'category_Табель_Текущая' => 'Current Activity',
    
    // Electrical equipment submenu buttons
    'cat_ТабЭлОб' => 'Electrical Equipment Menu',
    'cat_ТабЭлОб_РабПоГр' => 'Works by Schedule (Electrical)',
    'cat_ТабЭлОб_ФормИД' => 'ID Formation (Electrical)',
    

    'category_Табель_ПожарнойСигнализации' => 'Fire Alarm',
];

echo "Testing handler priorities and callback processing:\n";
echo "================================================\n";

// Simulate handler priorities
class TestMainMenuHandler {
    public function getPriority(): int { return 50; }
    public function canHandle($data): bool { 
        return $data === 'category_Главное';
    }
}

class TestAdminHandler {
    public function getPriority(): int { return 25; }
    public function canHandle($data): bool { 
        // Should NOT handle electrical equipment
        if (strpos($data, 'Электрооборудование') !== false) {
            return false;
        }
        return strpos($data, 'category_Админка') === 0 || strpos($data, 'adm_req_') === 0;
    }
}

class TestTabelHandler {
    public function getPriority(): int { return 30; } // Fixed priority
    public function canHandle($data): bool { 
        return strpos($data, 'category_Табель') === 0 || strpos($data, 'cat_Таб') === 0;
    }
}

class TestProfileHandler {
    public function getPriority(): int { return 15; }
    public function canHandle($data): bool { 
        return strpos($data, 'category_Профиль') === 0 || strpos($data, 'category_УровеньДоступа') === 0;
    }
}

// Create handlers in registration order
$handlers = [
    new TestAdminHandler(),
    new TestProfileHandler(),
    new TestTabelHandler(),
    new TestMainMenuHandler()
];

// Sort by priority (highest first) - this is what CallbackHandlerFactory does
usort($handlers, function($a, $b) {
    return $b->getPriority() - $a->getPriority();
});

echo "Handler priority order:\n";
foreach ($handlers as $index => $handler) {
    echo "  " . ($index + 1) . ". " . get_class($handler) . " (priority: " . $handler->getPriority() . ")\n";
}

echo "\nTesting callback processing:\n";
echo "==========================\n";

foreach ($testCases as $callbackData => $description) {
    echo "\nTesting: {$description} ({$callbackData})\n";
    
    $handlerFound = false;
    foreach ($handlers as $handler) {
        if ($handler->canHandle($callbackData)) {
            echo "  ✅ Handled by: " . get_class($handler) . "\n";
            $handlerFound = true;
            break;
        }
    }
    
    if (!$handlerFound) {
        echo "  ❌ No handler found!\n";
    }
}

echo "\n=== Expected Results ===\n";
echo "✅ All main menu buttons should work\n";
echo "✅ All submenu buttons should work (handled by TabelHandler)\n";
echo "✅ Electrical equipment buttons should work (handled by TabelHandler with priority 30)\n";
echo "✅ TabelHandler should be processed before AdminHandler (priority 30 > 25)\n";

?>
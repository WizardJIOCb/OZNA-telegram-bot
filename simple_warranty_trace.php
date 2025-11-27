<?php
// Simple warranty callback diagnostic
echo "=== SIMPLE WARRANTY CALLBACK DIAGNOSTIC ===\n\n";

echo "1. TESTING CALLBACK PATTERNS:\n";
echo "=============================\n";

$testCallbacks = [
    'cat_ТабГарантии' => 'Main warranty menu',
    'category_Табель_РабГарантии_ЭлектрОб' => 'Electrical equipment',
    'category_Табель_РабГарантии_НасОб' => 'Pumping equipment'
];

echo "Testing TabelHandler canHandle logic simulation:\n\n";

foreach ($testCallbacks as $callback => $description) {
    echo "Callback: {$callback} ({$description})\n";
    
    // Simulate the TabelHandler canHandle logic exactly
    $canHandle = str_starts_with($callback, 'category_Табель') || 
                 str_starts_with($callback, 'cat_ТабПНРаботы') ||
                 str_starts_with($callback, 'cat_Таб') ||
                 $callback === 'новое_прочее_yes' ||
                 $callback === 'cat_ТабЭлОб_Проч_yes' ||
                 $callback === 'cat_ТабВентОтопл' ||
                 $callback === 'cat_ТабОтоплВент' ||
                 str_starts_with($callback, 'cat_ТабВентОтопл_') ||
                 str_starts_with($callback, 'cat_ТабОтоплВент_') ||
                 $callback === 'cat_ТабУстрЗамРК' ||
                 $callback === 'category_Табель_УЗ_РК' ||
                 $callback === 'cat_ТабГарантии' ||
                 str_starts_with($callback, 'cat_ТабГарант');
    
    echo "canHandle: " . ($canHandle ? "✅ YES" : "❌ NO") . "\n";
    
    if ($canHandle) {
        // Simulate extractCategoryFromCallback
        $category = '';
        if (strpos($callback, 'category_') === 0) {
            $category = substr($callback, strlen('category_'));
        } elseif (strpos($callback, 'cat_Таб') === 0) {
            $category = $callback; // Keep as is for short format
        }
        
        echo "Extracted category: {$category}\n";
        
        // Test routing conditions
        if ($category === 'cat_ТабГарантии' || str_starts_with($category, 'cat_ТабГарант')) {
            echo "Routing: ✅ Main warranty menu handler\n";
        } elseif (str_starts_with($category, 'Табель_РабГарантии_')) {
            echo "Routing: ✅ Individual warranty equipment handler\n";
            $equipmentType = str_replace('Табель_РабГарантии_', '', $category);
            echo "Equipment type: {$equipmentType}\n";
            echo "Activity type: РабГарантии_{$equipmentType}\n";
        } else {
            echo "Routing: ❌ NO SPECIFIC ROUTING FOUND\n";
        }
    }
    echo "\n";
}

echo "2. TESTING ACTIVITY TYPE DEFINITIONS:\n";
echo "=====================================\n";

// Check if warranty activity types are defined in TabelEntry
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

try {
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    echo "✅ Successfully loaded MessageService and DatabaseService\n\n";
    
    // Test warranty equipment keyboard creation
    echo "Testing warranty equipment keyboard creation:\n";
    $keyboard = $messageService->createWarrantyEquipmentKeyboard();
    echo "✅ Warranty equipment keyboard created with " . count($keyboard) . " rows\n";
    
    foreach ($keyboard as $index => $row) {
        foreach ($row as $button) {
            if (isset($button['text']) && isset($button['callback_data'])) {
                echo "  - {$button['text']} → {$button['callback_data']}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error loading services: " . $e->getMessage() . "\n";
}

echo "\n3. CONCLUSION:\n";
echo "=============\n";
echo "If all tests above show ✅, then the warranty equipment should work.\n";
echo "If you see ❌ anywhere, that's where the problem is.\n\n";

echo "MOST LIKELY ISSUES:\n";
echo "1. Server hasn't restarted to load the fixed code\n";
echo "2. User conversation state is corrupted\n";
echo "3. User doesn't have proper access level\n";
echo "4. Project code is not set in conversation\n\n";

echo "RECOMMENDED SOLUTION:\n";
echo "Have the user type /cancel and /start, then try the full flow again:\n";
echo "Select date → Select project → Warranty works → Equipment type\n";
?>
<?php
// Runtime verification script for commissioning works button
// This script will help diagnose the exact issue

echo "=== Runtime Verification for Пусконаладочные работы Button ===\n\n";

// Include necessary files to test the actual methods
try {
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    echo "✅ MessageService loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Failed to load MessageService: " . $e->getMessage() . "\n";
    exit(1);
}

// Test MessageService keyboard creation
echo "\n1. Testing MessageService->createCommissioningWorksKeyboard():\n";
echo "============================================================\n";

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $keyboard = $messageService->createCommissioningWorksKeyboard();
    
    echo "✅ Keyboard created successfully\n";
    echo "Number of rows: " . count($keyboard) . "\n";
    echo "Activities included:\n";
    
    $activityCount = 0;
    foreach ($keyboard as $rowIndex => $row) {
        foreach ($row as $button) {
            if (isset($button['text']) && $button['text'] !== '⬅️ Назад') {
                $activityCount++;
                echo "  $activityCount. {$button['text']}\n";
                
                // Check callback data length
                $callbackLength = strlen($button['callback_data']);
                if ($callbackLength > 64) {
                    echo "    ⚠️  WARNING: Callback '{$button['callback_data']}' is {$callbackLength} bytes (exceeds 64-byte limit)\n";
                }
            }
        }
    }
    
    if ($activityCount === 13) {
        echo "✅ All 13 activities are present\n";
    } else {
        echo "❌ Expected 13 activities, but found $activityCount\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating keyboard: " . $e->getMessage() . "\n";
}

echo "\n2. Testing Callback Data Validation:\n";
echo "===================================\n";

$testCallbacks = [
    'cat_ТабПНРаботы' => 'Button callback',
    'category_Табель_Электрооборудование' => 'Electrical equipment',
    'category_Табель_ОхраннаяСигнализация' => 'Security alarm', 
    'category_Табель_ТехнологическоеОборудование' => 'Technological equipment'
];

foreach ($testCallbacks as $callback => $description) {
    $length = strlen($callback);
    echo "$description: '$callback' ({$length} bytes)";
    
    if ($length > 64) {
        echo " ❌ EXCEEDS LIMIT!\n";
    } else {
        echo " ✅ OK\n";
    }
}

echo "\n3. Recommended Actions:\n";
echo "=====================\n";
echo "A. Click the 'Пусконаладочные работы' button in your bot\n";
echo "B. Check the bot logs immediately after clicking\n";
echo "C. Look for this exact log message:\n";
echo "   'TabelHandler: handling commissioning works - showing commissioning works menu'\n";
echo "D. If you see that message, the routing works and issue is in keyboard display\n";
echo "E. If you don't see that message, there's a routing/callback issue\n\n";

echo "4. If the button still doesn't work, try this fix:\n";
echo "=================================================\n";
echo "The issue might be with long Russian callback data.\n";
echo "We may need to use even shorter callback format.\n";

?>
<?php
// Advanced warranty equipment diagnostic script
echo "=== ADVANCED WARRANTY EQUIPMENT DIAGNOSTIC ===\n\n";

// Test the complete warranty equipment flow
echo "1. COMPLETE FLOW SIMULATION:\n";
echo "============================\n";

$testScenarios = [
    [
        'step' => 'Show warranty submenu',
        'callback' => 'cat_ТабГарантии',
        'expected' => 'Display warranty equipment keyboard'
    ],
    [
        'step' => 'Click Electrical Equipment',
        'callback' => 'category_Табель_РабГарантии_ЭлектрОб',
        'expected' => 'Show confirmation for electrical warranty work'
    ],
    [
        'step' => 'Click Pumping Equipment', 
        'callback' => 'category_Табель_РабГарантии_НасОб',
        'expected' => 'Show confirmation for pumping warranty work'
    ]
];

foreach ($testScenarios as $index => $scenario) {
    echo "\nStep " . ($index + 1) . ": {$scenario['step']}\n";
    echo "Callback: {$scenario['callback']}\n";
    echo "Expected: {$scenario['expected']}\n";
    
    // Test TabelHandler canHandle
    $callbackData = $scenario['callback'];
    
    // Simulate the TabelHandler canHandle logic exactly as written in the code
    $canHandle = str_starts_with($callbackData, 'category_Табель') || 
                 str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
                 str_starts_with($callbackData, 'cat_Таб') ||
                 $callbackData === 'новое_прочее_yes' ||
                 $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
                 $callbackData === 'cat_ТабВентОтопл' ||
                 $callbackData === 'cat_ТабОтоплВент' ||
                 str_starts_with($callbackData, 'cat_ТабВентОтопл_') ||
                 str_starts_with($callbackData, 'cat_ТабОтоплВент_') ||
                 $callbackData === 'cat_ТабУстрЗамРК' ||
                 $callbackData === 'category_Табель_УЗ_РК' ||
                 $callbackData === 'cat_ТабГарантии' ||
                 str_starts_with($callbackData, 'cat_ТабГарант');
    
    echo "TabelHandler canHandle: " . ($canHandle ? "✅ YES" : "❌ NO") . "\n";
    
    if ($canHandle) {
        // Test routing logic
        if ($callbackData === 'cat_ТабГарантии' || str_starts_with($callbackData, 'cat_ТабГарант')) {
            echo "Routing: ✅ Main warranty menu handler\n";
        } elseif (str_starts_with($callbackData, 'category_Табель_РабГарантии_')) {
            echo "Routing: ✅ Individual warranty equipment handler\n";
            $equipmentType = str_replace('category_Табель_РабГарантии_', '', $callbackData);
            $activityType = 'РабГарантии_' . $equipmentType;
            echo "Equipment type: {$equipmentType}\n";
            echo "Activity type: {$activityType}\n";
        }
    }
}

echo "\n2. TROUBLESHOOTING GUIDE:\n";
echo "=========================\n";
echo "If warranty equipment buttons are not responding, try these solutions:\n\n";

echo "SOLUTION 1: Restart the bot server\n";
echo "   - The warranty equipment code may not be loaded\n";
echo "   - Stop and restart your Telegram bot process\n\n";

echo "SOLUTION 2: Check conversation state\n";
echo "   - Users need to have selected a date and project first\n";
echo "   - Try going through the normal flow: select date → select project → warranty works\n\n";

echo "SOLUTION 3: Check bot logs\n";
echo "   - Look for these debug messages in your bot logs:\n";
echo "   - '🛡️⚡ INDIVIDUAL WARRANTY EQUIPMENT DETECTED!'\n";
echo "   - '🔧 WARRANTY DEBUG: handleWarrantyEquipmentCallback called'\n";
echo "   - 'TabelHandler: canHandle result: true'\n\n";

echo "SOLUTION 4: Clear user conversation data\n";
echo "   - The user's conversation state might be corrupted\n";
echo "   - Have the user type /cancel and start over\n\n";

echo "SOLUTION 5: Test with working equipment first\n";
echo "   - Try the pumping equipment (НасОб) button\n";
echo "   - If it works, then individual equipment routing is fine\n";
echo "   - If it doesn't work, the issue is in the main routing\n\n";

echo "3. COMPARISON WITH WORKING EQUIPMENT:\n";
echo "====================================\n";
echo "The user mentioned pumping equipment works correctly.\n";
echo "Pumping equipment uses the SAME callback pattern:\n";
echo "Callback: category_Табель_РабГарантии_НасОб\n";
echo "This suggests the issue is NOT with the routing logic.\n\n";

echo "Most likely cause: SERVER CACHING or CONVERSATION STATE\n";
echo "Quick fix: Have the user restart their bot interaction:\n";
echo "1. Type /cancel\n";
echo "2. Type /start\n"; 
echo "3. Go through the full flow again\n";
?>
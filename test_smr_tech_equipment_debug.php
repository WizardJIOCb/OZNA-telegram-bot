<?php
// Simple test to check ШМР technological equipment flow

error_log("=== TESTING ШМР TECHNOLOGICAL EQUIPMENT FLOW ===");

echo "Testing ШМР Technological Equipment Button Flow\n";
echo "===============================================\n\n";

// Step 1: Test callback mapping
$callback = 'cat_ШМР_ТехОб';
echo "1. Button callback: {$callback}\n";

// Step 2: Test category extraction (from TabelHandler mapping)
$mapping = [
    'cat_ШМР_ТехОб' => 'Табель_ШМР_ТехнологическоеОборудование',
];

$extractedCategory = $mapping[$callback] ?? 'NOT_FOUND';
echo "2. Extracted category: {$extractedCategory}\n";

// Step 3: Test if handler condition matches
$handlerMatches = ($extractedCategory === 'Табель_ШМР_ТехнологическоеОборудование');
echo "3. Handler condition matches: " . ($handlerMatches ? "✅ YES" : "❌ NO") . "\n";

if ($handlerMatches) {
    echo "4. ✅ Handler should trigger and create submenu\n";
} else {
    echo "4. ❌ Handler will NOT trigger - this is the problem!\n";
}

// Step 4: Check what the MessageService keyboard would create
echo "\n5. Expected submenu structure:\n";
$expectedKeyboard = [
    [
        ['text' => '🔧 Монтаж трубопроводов', 'callback_data' => 'cat_ШМР_МонтТруб']
    ],
    [
        ['text' => '💧 Гидро/пневмо испытания', 'callback_data' => 'cat_ШМР_ГидрИсп']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
    ]
];

foreach ($expectedKeyboard as $row) {
    foreach ($row as $button) {
        echo "   • {$button['text']} ({$button['callback_data']})\n";
    }
}

echo "\n🎯 DEBUGGING STEPS:\n";
echo "===================\n";
echo "1. Click 'Технологическое оборудование' button\n";
echo "2. Check server logs for '🔧🔧🔧 CRITICAL DEBUG:' messages\n";
echo "3. If no debug messages appear, the handler is not being reached\n";
echo "4. If debug messages appear but submenu doesn't show, check MessageService\n";

echo "\n💡 MOST LIKELY ISSUE:\n";
echo "=====================\n";
echo "Since the handler exists and debug logging is in place,\n";
echo "the issue is probably in the routing BEFORE the handler.\n";
echo "Check for early routing interference or category extraction problems.\n";

// Test the early routing condition that might interfere
echo "\n6. Early routing interference test:\n";
$shouldBeBlocked = ($extractedCategory === 'cat_ТабТехОб' || $extractedCategory === 'Табель_ТехнологическоеОборудование') && 
                   $extractedCategory !== 'cat_ШМР_ТехОб' && 
                   $extractedCategory !== 'Табель_ШМР_ТехнологическоеОборудование';

echo "   Should be blocked by early routing: " . ($shouldBeBlocked ? "❌ YES (PROBLEM!)" : "✅ NO (GOOD)") . "\n";

echo "\nTest complete. Check the logs when clicking the button.\n";
?>
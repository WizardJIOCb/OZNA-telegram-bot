<?php

// Deep diagnostic for heating and ventilation callback issue
echo "=== DEEP CALLBACK DIAGNOSTIC ===\n\n";

// Test the actual callback being used
$callback = 'cat_ТабВентОтопл';
echo "1. Original callback: '{$callback}'\n";
echo "   Length in bytes: " . strlen($callback) . "\n";
echo "   Length in chars: " . mb_strlen($callback, 'UTF-8') . "\n";

// Check encoding
echo "\n2. Encoding check:\n";
echo "   Is valid UTF-8: " . (mb_check_encoding($callback, 'UTF-8') ? 'YES' : 'NO') . "\n";
echo "   Hex representation: " . bin2hex($callback) . "\n";

// Test pattern matching for canHandle
echo "\n3. Pattern matching tests:\n";
$patterns = [
    'category_Табель' => str_starts_with($callback, 'category_Табель'),
    'cat_ТабПНРаботы' => str_starts_with($callback, 'cat_ТабПНРаботы'),
    'cat_Таб' => str_starts_with($callback, 'cat_Таб'),
    'cat_ТабВентОтопл' => ($callback === 'cat_ТабВентОтопл'),
    'starts with cat_ТабВентОтопл_' => str_starts_with($callback, 'cat_ТабВентОтопл_')
];

foreach ($patterns as $pattern => $matches) {
    echo "   {$pattern}: " . ($matches ? 'MATCH' : 'NO MATCH') . "\n";
}

// Test the extraction logic
echo "\n4. Category extraction test:\n";
if (strpos($callback, 'cat_Таб') === 0) {
    echo "   Starts with 'cat_Таб': YES\n";
    
    $shortFormats = [
        'cat_ТабВентОтопл' => 'Табель_ОтоплениеИВентиляция',
    ];
    
    if (isset($shortFormats[$callback])) {
        $extracted = $shortFormats[$callback];
        echo "   Found in mapping: YES\n";
        echo "   Extracted category: '{$extracted}'\n";
        echo "   Extracted length: " . strlen($extracted) . " bytes\n";
    } else {
        echo "   Found in mapping: NO\n";
        $fallback = substr($callback, strlen('cat_'));
        echo "   Fallback category: '{$fallback}'\n";
    }
} else {
    echo "   Starts with 'cat_Таб': NO\n";
}

// Test if the actual button callback from MessageService matches
echo "\n5. Button callback check:\n";
echo "   Checking what MessageService generates...\n";

// Simulate the keyboard generation
$heatingButton = ['text' => '❄️ Вентиляция и отопление', 'callback_data' => 'cat_ТабВентОтопл'];
echo "   Button text: '{$heatingButton['text']}'\n";
echo "   Button callback: '{$heatingButton['callback_data']}'\n";
echo "   Button callback bytes: " . strlen($heatingButton['callback_data']) . "\n";

// Check if callback exceeds Telegram limits
if (strlen($heatingButton['callback_data']) > 64) {
    echo "   ⚠️  WARNING: Callback exceeds 64-byte Telegram limit!\n";
} else {
    echo "   ✓ Callback within Telegram 64-byte limit\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
?>
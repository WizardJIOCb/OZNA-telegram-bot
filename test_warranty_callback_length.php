<?php
/**
 * Test warranty equipment callback length and mapping to identify the exact issue
 */

echo "=== TESTING WARRANTY EQUIPMENT CALLBACK LENGTH ISSUE ===\n\n";

// Test current warranty equipment keyboard generation
echo "1. WARRANTY EQUIPMENT KEYBOARD CALLBACKS:\n";
echo "=" . str_repeat("=", 50) . "\n";

$warrantyKeyboard = [
    ['text' => '⚡ Электрооборудование', 'callback_data' => 'cat_Гарантии_ЭлектрОб'],
    ['text' => '📎 Средства измерений', 'callback_data' => 'cat_Гарантии_СрИзм'],
    ['text' => '💧 Насосное оборудование', 'callback_data' => 'cat_Гарантии_НасОб'],
    ['text' => '🔧 Трубопроводная арматура', 'callback_data' => 'cat_Гарантии_ТрубАрм'],
    ['text' => '⚙️ Исполнительные механизмы', 'callback_data' => 'cat_Гарантии_ИспМех'],
    ['text' => '💻 Контроллерное оборудование', 'callback_data' => 'cat_Гарантии_КонтрОб'],
    ['text' => '📦 Прочее оборудование', 'callback_data' => 'cat_Гарантии_Прочее']
];

foreach ($warrantyKeyboard as $button) {
    $text = $button['text'];
    $callback = $button['callback_data'];
    $length = strlen($callback);
    
    echo "Button: {$text}\n";
    echo "  Callback: {$callback}\n";
    echo "  Length: {$length} bytes " . ($length <= 64 ? "✅ OK" : "❌ TOO LONG") . "\n";
    
    // Test callback mapping (extractCategoryFromCallback simulation)
    $mappedCategory = '';
    $shortFormats = [
        'cat_Гарантии_ЭлектрОб' => 'Гарантии_ЭлектрОб',
        'cat_Гарантии_СрИзм' => 'Гарантии_СрИзм',
        'cat_Гарантии_НасОб' => 'Гарантии_НасОб',
        'cat_Гарантии_ТрубАрм' => 'Гарантии_ТрубАрм',
        'cat_Гарантии_ИспМех' => 'Гарантии_ИспМех',
        'cat_Гарантии_КонтрОб' => 'Гарантии_КонтрОб',
        'cat_Гарантии_Прочее' => 'Гарантии_Прочее'
    ];
    
    if (isset($shortFormats[$callback])) {
        $mappedCategory = $shortFormats[$callback];
        echo "  Mapped to: {$mappedCategory}\n";
        
        // Test if mapped category would be handled
        $handlerMatches = [
            "Short format: cat_Гарантии_ЭлектрОб" => $callback === 'cat_Гарантии_ЭлектрОб',
            "Mapped format: Гарантии_ЭлектрОб" => $mappedCategory === 'Гарантии_ЭлектрОб',
            "Long format: Табель_РабГарантии_ЭлектрОб" => $mappedCategory === 'Табель_РабГарантии_ЭлектрОб'
        ];
        
        echo "  Handler matching:\n";
        foreach ($handlerMatches as $format => $matches) {
            echo "    {$format}: " . ($matches ? "✅ MATCHES" : "❌ NO MATCH") . "\n";
        }
    }
    echo "\n";
}

echo "2. TESTING CONFIRMATION CALLBACK GENERATION:\n";
echo "=" . str_repeat("=", 50) . "\n";

// Test confirmation callback generation
foreach ($warrantyKeyboard as $button) {
    $callback = $button['callback_data'];
    $text = $button['text'];
    
    // Extract equipment type from callback
    if (preg_match('/^cat_Гарантии_(.+)$/', $callback, $matches)) {
        $equipmentType = $matches[1];
        $confirmationCallback = "cat_Гарантии_{$equipmentType}_yes";
        $confirmationLength = strlen($confirmationCallback);
        
        echo "Equipment: {$text}\n";
        echo "  Selection callback: {$callback} ({" . strlen($callback) . "} bytes)\n";
        echo "  Confirmation callback: {$confirmationCallback} ({$confirmationLength} bytes) " . 
             ($confirmationLength <= 64 ? "✅ OK" : "❌ TOO LONG") . "\n";
        echo "\n";
    }
}

echo "3. COMPARING WITH ELECTRICAL EQUIPMENT CALLBACKS:\n";
echo "=" . str_repeat("=", 50) . "\n";

// Compare with electrical equipment that works
$electricalCallbacks = [
    "Equipment menu: cat_ТабЭлОб" => strlen("cat_ТабЭлОб"),
    "Work by schedule: cat_ТабЭлОб_РабПоГр" => strlen("cat_ТабЭлОб_РабПоГр"),
    "Confirmation: cat_ТабЭлОб_РабПоГр_yes" => strlen("cat_ТабЭлОб_РабПоГр_yes")
];

$warrantyCallbacks = [
    "Equipment menu: cat_Гарантии_ЭлектрОб" => strlen("cat_Гарантии_ЭлектрОб"),
    "Confirmation: cat_Гарантии_ЭлектрОб_yes" => strlen("cat_Гарантии_ЭлектрОб_yes")
];

echo "ELECTRICAL EQUIPMENT CALLBACKS (WORKING):\n";
foreach ($electricalCallbacks as $desc => $length) {
    echo "  {$desc}: {$length} bytes " . ($length <= 64 ? "✅ OK" : "❌ TOO LONG") . "\n";
}

echo "\nWARRANTY EQUIPMENT CALLBACKS (NOT WORKING):\n";
foreach ($warrantyCallbacks as $desc => $length) {
    echo "  {$desc}: {$length} bytes " . ($length <= 64 ? "✅ OK" : "❌ TOO LONG") . "\n";
}

echo "\n=== ANALYSIS ===\n";
echo "All warranty equipment callbacks are under 64 bytes, so length is not the issue.\n";
echo "The issue is likely in callback mapping or handler recognition.\n";
echo "Need to check if handlers match the mapped category format.\n";
?>
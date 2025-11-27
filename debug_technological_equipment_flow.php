<?php

echo "=== Testing ШМР Technological Equipment Callback Issue ===\n";

// Simulate the exact scenario:
// User clicks "Технологическое оборудование" button in ШМР Intra-block installation menu
// Button has callback_data: 'cat_ШМР_ТехОб'
// This should map to category: 'Табель_ШМР_ТехнологическоеОборудование'
// And trigger the submenu with two options: Pipeline installation & Hydro/pneumatic testing

$callbackData = 'cat_ШМР_ТехОб';

echo "Step 1: Button clicked with callback: $callbackData\n";

// Step 2: TabelHandler canHandle check
$canHandle = str_starts_with($callbackData, 'cat_ШМР_');
echo "Step 2: TabelHandler canHandle result: " . ($canHandle ? "✅ TRUE" : "❌ FALSE") . "\n";

if (!$canHandle) {
    echo "❌ CRITICAL: TabelHandler will NOT handle this callback!\n";
    exit;
}

// Step 3: extractCategoryFromCallback
$shortFormats = [
    'cat_ШМР_ТехОб' => 'Табель_ШМР_ТехнологическоеОборудование',
    'cat_ШМР_ЭлектрОб' => 'Табель_ШМР_Электрооборудование',
    'cat_ШМР_ОтоплВент' => 'Табель_ШМР_ОтоплениеИВентиляция',
    'cat_ШМР_ПожСигн' => 'Табель_ШМР_ПожарнаяСигнализация',
    'cat_ШМР_ОхрСигн' => 'Табель_ШМР_ОхраннаяСигнализация',
    'cat_ШМР_Автом' => 'Табель_ШМР_Автоматизация',
    'cat_ШМР_Метрол' => 'Табель_ШМР_Метрология'
];

if (isset($shortFormats[$callbackData])) {
    $extractedCategory = $shortFormats[$callbackData];
    echo "Step 3: Category extracted: $extractedCategory\n";
    echo "✅ Mapping successful!\n";
} else {
    echo "❌ CRITICAL: Category extraction failed!\n";
    exit;
}

// Step 4: Check if handler condition matches
if ($extractedCategory === 'Табель_ШМР_ТехнологическоеОборудование') {
    echo "Step 4: ✅ Handler condition matches! Should trigger submenu.\n";
} else {
    echo "Step 4: ❌ Handler condition doesn't match!\n";
    exit;
}

// Step 5: Test submenu creation
$expectedSubmenu = [
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

echo "Step 5: Expected submenu structure:\n";
foreach ($expectedSubmenu as $row) {
    foreach ($row as $button) {
        echo "   - {$button['text']} -> {$button['callback_data']}\n";
    }
}

// Step 6: Check callback data lengths
echo "Step 6: Checking callback data lengths:\n";
foreach ($expectedSubmenu as $row) {
    foreach ($row as $button) {
        $length = strlen($button['callback_data']);
        if ($length > 64) {
            echo "   ❌ {$button['callback_data']} (length: $length - TOO LONG!)\n";
        } else {
            echo "   ✅ {$button['callback_data']} (length: $length - OK)\n";
        }
    }
}

echo "\n=== CONCLUSION ===\n";
echo "All steps should work correctly. If the button is still not responding:\n";
echo "1. Check if there are conflicting handlers with higher priority\n";
echo "2. Check if user access validation is blocking the action\n";
echo "3. Check if conversation state is causing issues\n";
echo "4. Check server/webhook logs for actual errors\n";
echo "\nTest completed.\n";
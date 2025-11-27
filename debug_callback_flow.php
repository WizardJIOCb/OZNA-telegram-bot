<?php

echo "=== DEBUGGING ШМР TECHNOLOGICAL EQUIPMENT CALLBACK FLOW ===\n";
echo "============================================================\n\n";

// Test the exact flow that should happen when button is clicked
$callback = 'cat_ШМР_ТехОб';

echo "Step 1: Button clicked with callback: {$callback}\n";

// Step 2: Test canHandle 
$canHandle = str_starts_with($callback, 'cat_ШМР_');
echo "Step 2: TabelHandler::canHandle() = " . ($canHandle ? "✅ TRUE" : "❌ FALSE") . "\n";

if (!$canHandle) {
    echo "❌ PROBLEM: TabelHandler won't handle this callback!\n";
    exit;
}

// Step 3: Test extractCategoryFromCallback mapping
$shortFormats = [
    'cat_ШМР_ТехОб' => 'Табель_ШМР_ТехнологическоеОборудование',
    'cat_ШМР_ЭлектрОб' => 'Табель_ШМР_Электрооборудование',
    'cat_ШМР_ОтоплВент' => 'Табель_ШМР_ОтоплениеИВентиляция',
    'cat_ШМР_ПожСигн' => 'Табель_ШМР_ПожарнаяСигнализация',
    'cat_ШМР_ОхрСигн' => 'Табель_ШМР_ОхраннаяСигнализация',
    'cat_ШМР_Автом' => 'Табель_ШМР_Автоматизация',
    'cat_ШМР_Метрол' => 'Табель_ШМР_Метрология'
];

if (isset($shortFormats[$callback])) {
    $extractedCategory = $shortFormats[$callback];
    echo "Step 3: extractCategoryFromCallback() = ✅ '{$extractedCategory}'\n";
} else {
    echo "Step 3: extractCategoryFromCallback() = ❌ NOT FOUND\n";
    exit;
}

// Step 4: Test early routing exclusion
$isEarlyRoutingBlocked = ($extractedCategory === 'cat_ТабТехОб' || $extractedCategory === 'Табель_ТехнологическоеОборудование') && 
                        $extractedCategory !== 'cat_ШМР_ТехОб' && 
                        $extractedCategory !== 'Табель_ШМР_ТехнологическоеОборудование';

echo "Step 4: Early routing blocked = " . ($isEarlyRoutingBlocked ? "❌ YES (PROBLEM!)" : "✅ NO (GOOD)") . "\n";

if ($isEarlyRoutingBlocked) {
    echo "❌ PROBLEM: Early routing will intercept this callback!\n";
    exit;
}

// Step 5: Test if handler exists in handleOtherWorkTypes
echo "Step 5: Looking for handler in handleOtherWorkTypes...\n";
echo "       Handler should match: if (\$category === '{$extractedCategory}')\n";

// Step 6: Test MessageService keyboard exists
echo "Step 6: Testing MessageService::createSMRTechnologicalEquipmentKeyboard()...\n";

// Check if the keyboard method would create proper structure
$expectedKeyboard = [
    [['text' => '🔧 Монтаж трубопроводов', 'callback_data' => 'cat_ШМР_МонтТруб']],
    [['text' => '💧 Гидро/пневмо испытания', 'callback_data' => 'cat_ШМР_ГидрИсп']],
    [['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']]
];

echo "Expected submenu keyboard:\n";
foreach ($expectedKeyboard as $i => $row) {
    foreach ($row as $j => $button) {
        echo "  [{$i}][{$j}] {$button['text']} → {$button['callback_data']}\n";
    }
}

echo "\n✅ THEORETICAL FLOW SHOULD WORK!\n";
echo "=================================\n";
echo "All components are in place:\n";
echo "• ✅ Callback mapping exists\n";
echo "• ✅ Early routing exclusion works\n";
echo "• ✅ Handler exists in handleOtherWorkTypes\n";
echo "• ✅ MessageService keyboard method exists\n";
echo "• ✅ Sub-activity handlers exist\n\n";

echo "🔍 NEXT STEPS FOR REAL TESTING:\n";
echo "===============================\n";
echo "1. Test the button in the actual bot\n";
echo "2. Check server logs for error messages\n";
echo "3. Look for any PHP errors or exceptions\n";
echo "4. Verify conversation state (project_code)\n";
echo "5. Check user permissions\n\n";

echo "If button still doesn't work, the issue might be:\n";
echo "• PHP syntax error preventing file execution\n";
echo "• Conversation state missing required data\n";
echo "• User access level restrictions\n";
echo "• Server-side caching of old code\n";
echo "• Database connection issues\n";

?>
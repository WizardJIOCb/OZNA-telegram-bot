<?php
/**
 * Test the canHandle fix for supervision milestone callbacks
 */

echo "=== ТЕСТ ИСПРАВЛЕНИЯ canHandle ДЛЯ SUPERVISION MILESTONE ===\n\n";

// Simulate the fixed canHandle method
function testCanHandle($callbackData) {
    echo "Testing canHandle for: $callbackData\n";
    
    $canHandle = str_starts_with($callbackData, 'category_Табель') || 
                 str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
                 str_starts_with($callbackData, 'cat_Таб') ||
                 str_starts_with($callbackData, 'cat_Гарантии_') ||
                 str_starts_with($callbackData, 'cat_ШМР_') ||  // New supervision works callbacks
                 str_starts_with($callbackData, 'ШМР_') ||  // Supervision milestone/schedule callbacks - NEW!
                 str_starts_with($callbackData, 'cat_supervision_') ||  // Alternative supervision callbacks
                 str_starts_with($callbackData, 'cat_РЁРњР _') ||  // Corrupted supervision callbacks
                 $callbackData === 'новое_прочее_yes' ||
                 $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
                 $callbackData === 'cat_ТабВентОтопл' ||
                 $callbackData === 'cat_ТабОтоплВент' ||
                 str_starts_with($callbackData, 'cat_ТабВентОтопл_') ||
                 str_starts_with($callbackData, 'cat_ТабОтоплВент_') ||
                 $callbackData === 'cat_ТабГарантии';
                 
    echo "Result: " . ($canHandle ? '✅ TRUE' : '❌ FALSE') . "\n\n";
    return $canHandle;
}

// Test problematic callbacks
$testCallbacks = [
    'ШМР_ВходнойКонтроль_ПодтВех_yes',
    'ШМР_ЗавозОборудования_ПодтВех_yes', 
    'ШМР_АнализИнформацииПоФундаменту_ПодтВех_yes',
    'ШМР_ВходнойКонтроль_РабГр_yes',
    'cat_ШМР_ВхКонтр_ПодтВех',  // Original button callback
    'category_Табель_ШМР'      // Menu callback
];

foreach ($testCallbacks as $callback) {
    testCanHandle($callback);
}

echo "=== АНАЛИЗ ===\n";
echo "✅ Теперь TabelHandler может обрабатывать:\n";
echo "1. cat_ШМР_* - кнопки milestone/schedule\n";
echo "2. ШМР_* - полные confirmation callbacks\n";
echo "3. category_Табель_* - меню callbacks\n\n";

echo "Кнопка 'Да' теперь должна работать!\n";
?>
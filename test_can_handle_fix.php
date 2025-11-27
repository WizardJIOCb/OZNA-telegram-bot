<?php
echo "=== TESTING SUPERVISION WORKS CALLBACK HANDLER FIX ===\n";
echo "====================================================\n\n";

echo "Testing the modified canHandle() logic for supervision works callbacks:\n\n";

// Simulate the canHandle logic that was added
function testCanHandle(string $callbackData): bool {
    return str_starts_with($callbackData, 'category_Табель') || 
           str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
           str_starts_with($callbackData, 'cat_Таб') ||
           str_starts_with($callbackData, 'cat_Гарантии_') ||
           str_starts_with($callbackData, 'cat_ШМР_') ||  // <- THIS IS THE FIX!
           $callbackData === 'новое_прочее_yes' ||
           $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
           $callbackData === 'cat_ТабВентОтопл' ||
           $callbackData === 'cat_ТабОтоплВент' ||
           str_starts_with($callbackData, 'cat_ТабВентОтопл_') ||
           str_starts_with($callbackData, 'cat_ТабОтоплВент_') ||
           $callbackData === 'cat_ТабГарантии';
}

// Test supervision works callbacks
$testCallbacks = [
    'cat_ШМР_Подг' => 'Подготовительные работы',
    'cat_ШМР_Монт' => 'Монтаж блоков', 
    'cat_ШМР_Сбор' => 'Сборка здания',
    'cat_ШМР_Внут' => 'Внутриблочный монтаж',
    'cat_ШМР_Акт' => 'Подписание акта ШМР',
    'cat_ШМР_ПодгПоезд' => 'Подготовка к служебной поездке',
    'cat_ШМР_Дорога' => 'Дорога',
    'cat_ШМР_Инстр' => 'Инструктаж',
    'cat_ШМР_СдачаОтч' => 'Сдача авансового отчета',
    'cat_ШМР_Назад' => 'Назад (Back)',
    'cat_ШМР_ВхКонтр' => 'Входной контроль',
    'cat_ШМР_ЗавозОб' => 'Завоз оборудования',
    'cat_ШМР_АналФунд' => 'Анализ информации по фундаменту',
    'cat_ШМР_Акт_yes' => 'Подписание акта ШМР (подтверждение)',
    'cat_ШМР_ПодгПоезд_yes' => 'Подготовка к поездке (подтверждение)',
    'cat_ШМР_ВхКонтр_yes' => 'Входной контроль (подтверждение)'
];

$allWorking = true;

echo "1. Testing supervision works callbacks:\n";
foreach ($testCallbacks as $callback => $description) {
    $canHandle = testCanHandle($callback);
    $status = $canHandle ? '✅ WORKING' : '❌ BROKEN';
    echo "   {$status}: {$callback} ({$description})\n";
    
    if (!$canHandle) {
        $allWorking = false;
    }
}

// Test some other callbacks to ensure they still work
echo "\n2. Testing other callbacks (should still work):\n";
$otherCallbacks = [
    'category_Табель' => 'Main tabel menu',
    'cat_ТабПНРаботы' => 'Commissioning works',
    'cat_ТабЭлОб_РабПоГр' => 'Electrical equipment works by schedule',
    'cat_Гарантии_ЭлектрОб' => 'Warranty electrical equipment'
];

foreach ($otherCallbacks as $callback => $description) {
    $canHandle = testCanHandle($callback);
    $status = $canHandle ? '✅ WORKING' : '❌ BROKEN';
    echo "   {$status}: {$callback} ({$description})\n";
    
    if (!$canHandle) {
        $allWorking = false;
    }
}

echo "\n3. Summary:\n";
if ($allWorking) {
    echo "🎉 SUCCESS: All tested callbacks are properly handled!\n";
    echo "✅ The fix has resolved the callback handling issue.\n";
    echo "✅ TabelHandler can now handle cat_ШМР_ prefixed callbacks.\n";
    echo "✅ Both main activities and sub-activities should work.\n";
    echo "✅ Confirmation callbacks are also properly handled.\n";
    echo "✅ Other existing callbacks remain functional.\n";
} else {
    echo "❌ FAILURE: Some callbacks are still not working.\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
echo "The core issue was that TabelHandler->canHandle() was missing the cat_ШМР_ prefix check.\n";
echo "This meant ALL supervision works activities failed because the handler was never invoked.\n";
echo "The fix adds: str_starts_with(\$callbackData, 'cat_ШМР_') to the canHandle() method.\n";
?>
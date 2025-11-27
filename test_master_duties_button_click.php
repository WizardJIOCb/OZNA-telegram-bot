<?php
/**
 * TEST MASTER DUTIES BUTTON CLICK
 * This simulates exactly what happens when you click the Master Duties button
 */

echo "🔍 SIMULATING MASTER DUTIES BUTTON CLICK\n";
echo "========================================\n\n";

// The exact callback that the Master Duties button sends
$masterDutiesCallback = 'cat_ТабИсполнОбяз';

echo "1. BUTTON CALLBACK ANALYSIS:\n";
echo "============================\n";
echo "Button text: '👷 Исполнение обязанностей мастера'\n";
echo "Button callback: '{$masterDutiesCallback}'\n";
echo "Callback length: " . strlen($masterDutiesCallback) . " bytes\n";
echo "Callback encoding: " . mb_detect_encoding($masterDutiesCallback) . "\n";
echo "Callback hex: " . bin2hex($masterDutiesCallback) . "\n\n";

echo "2. CALLBACKHANDLERFACTORY ROUTING:\n";
echo "==================================\n";
echo "CallbackHandlerFactory will receive: '{$masterDutiesCallback}'\n";

// Simulate extractCategory logic
if (strpos($masterDutiesCallback, 'cat_') === 0) {
    $extractedCategory = substr($masterDutiesCallback, strlen('cat_'));
    echo "Extracted category: '{$extractedCategory}'\n";
} else {
    $extractedCategory = $masterDutiesCallback;
    echo "No extraction needed: '{$extractedCategory}'\n";
}

echo "\n3. TABELHANDLER CANHANDLE CHECK:\n";
echo "================================\n";
echo "TabelHandler->canHandle('{$masterDutiesCallback}') conditions:\n";

$canHandleChecks = [
    'starts_with_category_Табель' => strpos($masterDutiesCallback, 'category_Табель') === 0,
    'starts_with_cat_ТабПНРаботы' => strpos($masterDutiesCallback, 'cat_ТабПНРаботы') === 0,
    'starts_with_cat_Таб' => strpos($masterDutiesCallback, 'cat_Таб') === 0,
    'starts_with_cat_Гарантии_' => strpos($masterDutiesCallback, 'cat_Гарантии_') === 0,
    'equals_новое_прочее_yes' => $masterDutiesCallback === 'новое_прочее_yes',
    'equals_cat_ТабЭлОб_Проч_yes' => $masterDutiesCallback === 'cat_ТабЭлОб_Проч_yes',
    'equals_cat_ТабВентОтопл' => $masterDutiesCallback === 'cat_ТабВентОтопл',
    'equals_cat_ТабОтоплВент' => $masterDutiesCallback === 'cat_ТабОтоплВент',
    'starts_with_cat_ТабВентОтопл_' => strpos($masterDutiesCallback, 'cat_ТабВентОтопл_') === 0,
    'starts_with_cat_ТабОтоплВент_' => strpos($masterDutiesCallback, 'cat_ТабОтоплВент_') === 0,
    'equals_cat_ТабГарантии' => $masterDutiesCallback === 'cat_ТабГарантии'
];

$canHandle = false;
foreach ($canHandleChecks as $condition => $result) {
    echo "  {$condition}: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    if ($result) {
        $canHandle = true;
    }
}

echo "\nOverall canHandle result: " . ($canHandle ? '✅ TRUE' : '❌ FALSE') . "\n";

if (!$canHandle) {
    echo "❌ PROBLEM: TabelHandler will NOT handle this callback!\n";
    echo "This means the callback never reaches TabelHandler.\n";
    exit();
}

echo "\n4. TABELHANDLER EXTRACTCATEGORYFROMCALLBACK:\n";
echo "============================================\n";
echo "Input callback: '{$masterDutiesCallback}'\n";

// Simulate the actual mapping from TabelHandler
$shortFormats = [
    'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
    'cat_ТабИсполнОбяз_yes' => 'Табель_ИсполнениеОбязМастера_yes',
];

if (strpos($masterDutiesCallback, 'cat_Таб') === 0) {
    if (isset($shortFormats[$masterDutiesCallback])) {
        $mappedCategory = $shortFormats[$masterDutiesCallback];
        echo "✅ Mapping found: '{$mappedCategory}'\n";
    } else {
        $mappedCategory = substr($masterDutiesCallback, strlen('cat_'));
        echo "⚠️ Fallback mapping: '{$mappedCategory}'\n";
    }
} else {
    $mappedCategory = $masterDutiesCallback;
    echo "No mapping needed: '{$mappedCategory}'\n";
}

echo "\n5. HANDLEOTHERWORKTYPES CHECK:\n";
echo "==============================\n";
echo "Category passed to handleOtherWorkTypes: '{$mappedCategory}'\n";

$handlerConditions = [
    "category === 'Табель_ИсполнениеОбязМастера'" => $mappedCategory === 'Табель_ИсполнениеОбязМастера',
    "category === 'ИсполнениеОбязМастера'" => $mappedCategory === 'ИсполнениеОбязМастера'
];

$handlerMatch = false;
foreach ($handlerConditions as $condition => $result) {
    echo "  {$condition}: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    if ($result) {
        $handlerMatch = true;
    }
}

echo "\nHandler match result: " . ($handlerMatch ? '✅ TRUE' : '❌ FALSE') . "\n";

echo "\n6. OVERALL FLOW ANALYSIS:\n";
echo "=========================\n";

if ($canHandle && $handlerMatch) {
    echo "🎉 SUCCESS: All routing should work correctly!\n";
    echo "\nExpected flow:\n";
    echo "1. Button sends: 'cat_ТабИсполнОбяз'\n";
    echo "2. CallbackHandlerFactory routes to TabelHandler\n";
    echo "3. TabelHandler maps to: 'Табель_ИсполнениеОбязМастера'\n";
    echo "4. handleOtherWorkTypes matches Master Duties handler\n";
    echo "5. Confirmation dialog appears\n\n";
    
    echo "❓ If button still doesn't work with super user access:\n";
    echo "• Check if server needs restart\n";
    echo "• Check if conversation state is corrupted\n";
    echo "• Check if there are any PHP errors\n";
    echo "• Try clearing conversation with /cancel\n";
} else {
    echo "❌ PROBLEM: Flow has issues!\n";
    if (!$canHandle) {
        echo "• TabelHandler won't receive the callback\n";
    }
    if (!$handlerMatch) {
        echo "• Handler won't match in handleOtherWorkTypes\n";
    }
}

echo "\nDiagnostic completed: " . date('Y-m-d H:i:s') . "\n";
?>
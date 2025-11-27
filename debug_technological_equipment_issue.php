<?php

echo "=== Debugging ШМР Technological Equipment Issue ===\n";

// Simulate the exact workflow
$originalCallback = 'cat_ШМР_ТехОб';
echo "1. Original callback: {$originalCallback}\n";

// Check canHandle
$canHandleCheck = str_starts_with($originalCallback, 'cat_ШМР_');
echo "2. TabelHandler canHandle: " . ($canHandleCheck ? "✅ TRUE" : "❌ FALSE") . "\n";

if (!$canHandleCheck) {
    echo "❌ CRITICAL: TabelHandler won't handle this callback!\n";
    exit;
}

// Check extractCategoryFromCallback mapping
$shortFormats = [
    'cat_ШМР_ТехОб' => 'Табель_ШМР_ТехнологическоеОборудование'
];

$extractedCategory = '';
if (isset($shortFormats[$originalCallback])) {
    $extractedCategory = $shortFormats[$originalCallback];
    echo "3. Category extracted: {$extractedCategory}\n";
} else {
    echo "3. ❌ Category extraction FAILED!\n";
    echo "   Available mappings:\n";
    foreach ($shortFormats as $key => $value) {
        echo "   - {$key} => {$value}\n";
    }
    exit;
}

// Check all the early routing conditions that might interfere

echo "\n4. Early routing interference checks:\n";

// Check electrical equipment early routing
$electricalEarlyRouting = ($extractedCategory === 'cat_ТабЭлОб' || $extractedCategory === 'Табель_Электрооборудование') && 
    $extractedCategory !== 'cat_ШМР_ЭлектрОб' && $extractedCategory !== 'Табель_ШМР_Электрооборудование';
echo "   - Electrical equipment early routing: " . ($electricalEarlyRouting ? "❌ WILL INTERFERE" : "✅ OK") . "\n";

// Check automation equipment early routing  
$automationEarlyRouting = ($extractedCategory === 'cat_ТабАвтом' || $extractedCategory === 'Табель_Автоматизация') && $extractedCategory !== 'cat_ШМР_Автом';
echo "   - Automation equipment early routing: " . ($automationEarlyRouting ? "❌ WILL INTERFERE" : "✅ OK") . "\n";

// Check heating/ventilation equipment early routing
$heatingEarlyRouting = ($extractedCategory === 'cat_ТабВентОтопл' || $extractedCategory === 'Табель_ОтоплениеИВентиляция' || $extractedCategory === 'cat_ТабОтоплВент') && $extractedCategory !== 'cat_ШМР_ОтоплВент';
echo "   - Heating/Ventilation equipment early routing: " . ($heatingEarlyRouting ? "❌ WILL INTERFERE" : "✅ OK") . "\n";

// Check fire alarm equipment early routing
$fireAlarmEarlyRouting = ($extractedCategory === 'cat_ТабПожСигн' || $extractedCategory === 'Табель_ПожарнаяСигнализация') && $extractedCategory !== 'cat_ШМР_ПожСигн';
echo "   - Fire alarm equipment early routing: " . ($fireAlarmEarlyRouting ? "❌ WILL INTERFERE" : "✅ OK") . "\n";

// Check security alarm equipment early routing
$securityAlarmEarlyRouting = ($extractedCategory === 'cat_ТабОхрСигн' || $extractedCategory === 'Табель_ОхраннаяСигнализация') && $extractedCategory !== 'cat_ШМР_ОхрСигн';
echo "   - Security alarm equipment early routing: " . ($securityAlarmEarlyRouting ? "❌ WILL INTERFERE" : "✅ OK") . "\n";

// Check technological equipment early routing (THE MOST IMPORTANT ONE)
$technologicalEarlyRouting = ($extractedCategory === 'cat_ТабТехОб' || $extractedCategory === 'Табель_ТехнологическоеОборудование') && 
    $extractedCategory !== 'cat_ШМР_ТехОб' && $extractedCategory !== 'Табель_ШМР_ТехнологическоеОборудование';
echo "   - Technological equipment early routing: " . ($technologicalEarlyRouting ? "❌ WILL INTERFERE" : "✅ OK") . "\n";

// Check metrology early routing
$metrologyEarlyRouting = ($extractedCategory === 'cat_ТабМетрол' || $extractedCategory === 'Табель_Метрология') && $extractedCategory !== 'cat_ШМР_Метрол';
echo "   - Metrology equipment early routing: " . ($metrologyEarlyRouting ? "❌ WILL INTERFERE" : "✅ OK") . "\n";

echo "\n5. Final handler check:\n";
$shmrTechnologicalHandler = ($extractedCategory === 'Табель_ШМР_ТехнологическоеОборудование');
echo "   - Will reach ШМР technological equipment handler: " . ($shmrTechnologicalHandler ? "✅ YES" : "❌ NO") . "\n";

echo "\n=== SUMMARY ===\n";

$anyInterference = $electricalEarlyRouting || $automationEarlyRouting || $heatingEarlyRouting || $fireAlarmEarlyRouting || $securityAlarmEarlyRouting || $technologicalEarlyRouting || $metrologyEarlyRouting;

if ($anyInterference) {
    echo "❌ PROBLEM FOUND: Early routing will interfere!\n";
    if ($technologicalEarlyRouting) {
        echo "🚨 CRITICAL: Technological equipment early routing is the culprit!\n";
        echo "   The condition is matching but should exclude ШМР callbacks.\n";
    }
} else if ($shmrTechnologicalHandler) {
    echo "✅ ROUTING SHOULD WORK: No interference detected, callback should reach ШМР handler.\n";
} else {
    echo "❌ UNKNOWN ISSUE: No early routing interference but won't reach ШМР handler.\n";
}

echo "\nDiagnostic completed.\n";
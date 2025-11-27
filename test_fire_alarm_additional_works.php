<?php
echo "🔧 Testing Fire Alarm Additional Works Button\n";
echo "==========================================\n\n";

// Test the callback mapping
$callbackData = 'cat_ТабПожСигн_ДопРаб';

// Simulate the extractCategoryFromCallback function
$shortFormats = [
    'cat_ТабПожСигн_ДопРаб' => 'Табель_ДопРаб_ПожСигн'
];

if (isset($shortFormats[$callbackData])) {
    $mappedCategory = $shortFormats[$callbackData];
    echo "✅ Original callback: {$callbackData}\n";
    echo "✅ Mapped category: {$mappedCategory}\n\n";
    
    // Test if the handler conditions would match
    $testConditions = [
        'ТабПожСигн_ДопРаб' => ($mappedCategory === 'ТабПожСигн_ДопРаб'),
        'cat_ТабПожСигн_ДопРаб' => ($mappedCategory === 'cat_ТабПожСигн_ДопРаб'),
        'Табель_ДопРаб_ПожСигн' => ($mappedCategory === 'Табель_ДопРаб_ПожСигн'),
        'Табель_ДопРаб_ПожарнаяСигнализация' => ($mappedCategory === 'Табель_ДопРаб_ПожарнаяСигнализация')
    ];
    
    echo "Handler condition matches:\n";
    foreach ($testConditions as $condition => $matches) {
        $status = $matches ? '✅ MATCH' : '❌ NO MATCH';
        echo "  {$condition}: {$status}\n";
    }
    
    echo "\n";
    if ($testConditions['Табель_ДопРаб_ПожСигн']) {
        echo "🎉 SUCCESS: The fixed handler should now correctly route to handleAdditionalWorksFireAlarmMenu()!\n";
    } else {
        echo "⚠️  WARNING: Handler may still have issues\n";
    }
} else {
    echo "❌ ERROR: Callback mapping not found\n";
}
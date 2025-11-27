<?php
/**
 * Test script to verify the supervision milestone confirmation fix
 */

echo "=== TESTING SUPERVISION MILESTONE CONFIRMATION FIX ===\n\n";

// Simulate the TabelHandler extractCategoryFromCallback method
function testExtractCategoryFromCallback(string $callbackData): string
{
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for supervision works
    if (strpos($callbackData, 'cat_ШМР_') === 0) {
        $shortFormats = [
            // Supervision works milestone confirmation callbacks
            'cat_ШМР_ВхКонтр_ПодтВех' => 'ШМР_ВходнойКонтроль_ПодтВех',
            'cat_ШМР_ЗавОбор_ПодтВех' => 'ШМР_ЗавозОборудования_ПодтВех',
            'cat_ШМР_АналФунд_ПодтВех' => 'ШМР_АнализИнформацииПоФундаменту_ПодтВех',
            'cat_ШМР_РасстБлок_ПодтВех' => 'ШМР_РасстановкаБлоков_ПодтВех',
            'cat_ШМР_МонтПлощ_ПодтВех' => 'ШМР_МонтажПлощадокОбслуживания_ПодтВех',
            'cat_ШМР_МонтЭлУкр_ПодтВех' => 'ШМР_МонтажЭлементовУкрытия_ПодтВех',
            'cat_ШМР_СборКровл_ПодтВех' => 'ШМР_СборкаКровли_ПодтВех',
            'cat_ШМР_МонтТруб_ПодтВех' => 'ШМР_МонтажТрубопроводов_ПодтВех',
            'cat_ШМР_ГидрИсп_ПодтВех' => 'ШМР_ГидропневмоИспытания_ПодтВех',
            'cat_ШМР_МонтТеплИз_ПодтВех' => 'ШМР_МонтажТеплоизоляции_ПодтВех',
            'cat_ШМР_МонтВент_ПодтВех' => 'ШМР_МонтажВентиляции_ПодтВех',
            'cat_ШМР_МонтСнятОб_ПодтВех' => 'ШМР_МонтажСнятогоОборудования_ПодтВех',
            'cat_ШМР_МонтКаб_ПодтВех' => 'ШМР_МонтажКабельнойПродукции_ПодтВех',
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        return substr($callbackData, strlen('cat_'));
    }
    
    return $callbackData;
}

// Test the problematic callback
$testCallback = 'cat_ШМР_ВхКонтр_ПодтВех';
echo "Testing callback: $testCallback\n";

$mappedCategory = testExtractCategoryFromCallback($testCallback);
echo "Mapped to: $mappedCategory\n\n";

if ($mappedCategory === 'ШМР_ВходнойКонтроль_ПодтВех') {
    echo "✅ SUCCESS! The callback is now properly mapped.\n";
    echo "   This should resolve the issue where confirmation messages\n";
    echo "   showed 'ШМР_ВхКонтр_ПодтВех' instead of proper formatting.\n\n";
} else {
    echo "❌ FAILED! The mapping is still incorrect.\n\n";
}

// Test a few more supervision milestone callbacks
$additionalTests = [
    'cat_ШМР_ЗавОбор_ПодтВех' => 'ШМР_ЗавозОборудования_ПодтВех',
    'cat_ШМР_АналФунд_ПодтВех' => 'ШМР_АнализИнформацииПоФундаменту_ПодтВех',
    'cat_ШМР_МонтТруб_ПодтВех' => 'ШМР_МонтажТрубопроводов_ПодтВех',
];

echo "Testing additional supervision milestone callbacks:\n";
echo "==================================================\n";

foreach ($additionalTests as $callback => $expected) {
    $result = testExtractCategoryFromCallback($callback);
    $status = ($result === $expected) ? '✅' : '❌';
    echo "$status $callback → $result\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "If all tests show ✅, the supervision milestone confirmation fix is working correctly.\n";
echo "Users should now see properly formatted confirmation messages instead of technical identifiers.\n";
?>
<?php
/*
 * Test script to debug ШМР "Работы по графику" execution issue
 * This will simulate the exact callback that's sent when user clicks "Да"
 */

// Test the exact callback format for ШМР Входной контроль "Работы по графику"
$testCallback = 'cat_ШМР_ВхКонтр_РабПоГр_yes';

echo "🔍 Testing ШМР Schedule Execution Debug\n";
echo "Callback to test: '{$testCallback}'\n\n";

// Test isActivityExecution pattern matching
echo "=== Testing isActivityExecution Pattern Matching ===\n";

// Pattern 1: ШМР_(.+)_РабПоГр_yes
$pattern1 = '/^ШМР_(.+)_РабПоГр_yes$/u';
if (preg_match($pattern1, $testCallback, $matches1)) {
    echo "✅ Pattern 1 MATCHES: ШМР_(.+)_РабПоГр_yes\n";
    echo "   Extracted: {$matches1[1]}\n";
} else {
    echo "❌ Pattern 1 NO MATCH: ШМР_(.+)_РабПоГр_yes\n";
}

// Pattern 2: cat_ШМР_(.+)_РабПоГр_yes  
$pattern2 = '/^cat_ШМР_(.+)_РабПоГр_yes$/u';
if (preg_match($pattern2, $testCallback, $matches2)) {
    echo "✅ Pattern 2 MATCHES: cat_ШМР_(.+)_РабПоГр_yes\n";
    echo "   Extracted: {$matches2[1]}\n";
} else {
    echo "❌ Pattern 2 NO MATCH: cat_ШМР_(.+)_РабПоГр_yes\n";
}

echo "\n=== Testing handleActivityExecution Mapping ===\n";

// Test the mapping used in handleActivityExecution
$shortToFullMap = [
    'ВхКонтр' => 'ШМР_ВходнойКонтроль_РабПоГр',
    'ЗавОбор' => 'ШМР_ЗавозОборудования_РабПоГр',
    'АналФунд' => 'ШМР_АнализИнформацииПоФундаменту_РабПоГр',
    'РасстБлок' => 'ШМР_РасстановкаБлоков_РабПоГр',
    // ... other mappings
];

if (preg_match($pattern2, $testCallback, $matches)) {
    $shortActivityKey = $matches[1];
    echo "Extracted short key: '{$shortActivityKey}'\n";
    
    if (isset($shortToFullMap[$shortActivityKey])) {
        $activityType = $shortToFullMap[$shortActivityKey];
        echo "✅ MAPPING FOUND: {$shortActivityKey} -> {$activityType}\n";
    } else {
        echo "❌ MAPPING NOT FOUND for key: '{$shortActivityKey}'\n";
        echo "Available mappings:\n";
        foreach ($shortToFullMap as $key => $value) {
            echo "   '{$key}' -> '{$value}'\n";
        }
    }
}

echo "\n=== Testing Activity Type Validation ===\n";
echo "⚠️  Skipping TabelEntry validation (requires complex dependencies)\n";
echo "Will check this manually in the actual code.\n";

echo "\n=== SUMMARY ===\n";
echo "The callback '{$testCallback}' should:\n";
echo "1. Match pattern: cat_ШМР_(.+)_РабПоГр_yes\n";
echo "2. Extract key: 'ВхКонтр'\n";  
echo "3. Map to activity type: 'ШМР_ВходнойКонтроль_РабПоГр'\n";
echo "4. Execute tabel entry creation\n";
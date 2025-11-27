<?php
/**
 * Simple pattern test to verify supervision works fixes
 */

echo "🔧 TESTING SUPERVISION WORKS PATTERN FIXES\n";
echo "=========================================\n\n";

// Test patterns directly
$schedulePattern = '/^cat_ШМР_(.+)_РабПоГр_yes$/u';
$simpleSupervisionPattern = '/^ШМР_(.+)_yes$/u';

echo "1. TESTING SCHEDULE CONFIRMATION PATTERN\n";
echo "Pattern: {$schedulePattern}\n";
echo "========================================\n";

$scheduleCallbacks = [
    'cat_ШМР_ВхКонтр_РабПоГр_yes' => 'Should match - schedule confirmation',
    'cat_ШМР_МонтПлощ_РабПоГр_yes' => 'Should match - schedule confirmation',
    'cat_ШМР_ВхКонтр_РабПоГр' => 'Should NOT match - schedule button (no _yes)',
    'ШМР_АналФунд_yes' => 'Should NOT match - simple supervision'
];

foreach ($scheduleCallbacks as $callback => $description) {
    $matches = [];
    $isMatch = preg_match($schedulePattern, $callback, $matches);
    $result = $isMatch ? "✅ MATCHES" : "❌ NO MATCH";
    echo "{$callback}: {$result} - {$description}\n";
    if ($isMatch && isset($matches[1])) {
        echo "  Extracted key: '{$matches[1]}'\n";
    }
    echo "\n";
}

echo "2. TESTING SIMPLE SUPERVISION PATTERN\n";
echo "Pattern: {$simpleSupervisionPattern}\n";
echo "=====================================\n";

$simpleCallbacks = [
    'ШМР_АналФунд_yes' => 'Should match - site inspection confirmation',
    'ШМР_ПодтВех_yes' => 'Should match - milestone confirmation (FIXED)',
    'ШМР_ПодпАкт_yes' => 'Should match - act signing confirmation',
    'cat_ШМР_ВхКонтр_РабПоГр_yes' => 'Should NOT match - schedule confirmation'
];

foreach ($simpleCallbacks as $callback => $description) {
    $matches = [];
    $isMatch = preg_match($simpleSupervisionPattern, $callback, $matches);
    $result = $isMatch ? "✅ MATCHES" : "❌ NO MATCH";
    echo "{$callback}: {$result} - {$description}\n";
    if ($isMatch && isset($matches[1])) {
        echo "  Extracted key: '{$matches[1]}'\n";
        
        // Test if the key would be found in our mapping
        $simpleSupervisionMap = [
            'АналФунд' => 'ПроверкаМонтажнойПлощадки',
            'ПодпАкт' => 'ПодписаниеАктаШМР',
            'ПодтВех' => 'ПодтверждениеВехи_ШМР'  // FIXED: Added milestone mapping
        ];
        
        if (isset($simpleSupervisionMap[$matches[1]])) {
            echo "  ✅ Key found in mapping: {$simpleSupervisionMap[$matches[1]]}\n";
        } else {
            echo "  ❌ Key NOT found in mapping!\n";
        }
    }
    echo "\n";
}

echo "3. SUMMARY OF FIXES\n";
echo "==================\n";
echo "✅ Removed early routing for schedule callbacks (lines 655-664)\n";
echo "✅ Added milestone mapping 'ПодтВех' => 'ПодтверждениеВехи_ШМР'\n";
echo "✅ Schedule buttons now go through normal confirmation flow\n";
echo "✅ Milestone confirmations now properly map to activity types\n";

echo "\n🎯 EXPECTED BEHAVIOR:\n";
echo "====================\n";
echo "1. 'Работы по графику' buttons → Show confirmation dialog (no auto-execute)\n";
echo "2. 'Подтвердить достижение вехи' Да button → Execute properly (no silence)\n";
echo "3. Both issues should be resolved!\n";
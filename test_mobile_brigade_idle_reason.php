<?php
/**
 * Test Mobile Brigade idle reason inclusion
 */

echo "=== TESTING MOBILE BRIGADE IDLE REASON FIX ===\n\n";

// Simulate activityTypeMappings
$activityTypeMappings = [
    'МобБриг_Простой' => '⏸️ Простой',
];

// Test data
$testCases = [
    [
        'name' => 'Mobile Brigade - Transport breakdown',
        'activityType' => 'МобБриг_Простой',
        'idleReason' => 'Транспорт (Поломка)',
        'expectedActivityText' => '⏸️ Простой (Транспорт (Поломка))',
    ],
    [
        'name' => 'Mobile Brigade - Weather conditions',
        'activityType' => 'МобБриг_Простой',
        'idleReason' => 'Погодные условия (Распутица)',
        'expectedActivityText' => '⏸️ Простой (Погодные условия (Распутица))',
    ],
    [
        'name' => 'Mobile Brigade - No reason',
        'activityType' => 'МобБриг_Простой',
        'idleReason' => '',
        'expectedActivityText' => '⏸️ Простой',
    ],
];

$passedTests = 0;
$failedTests = 0;

foreach ($testCases as $index => $test) {
    echo "Test " . ($index + 1) . ": {$test['name']}\n";
    echo str_repeat("=", 70) . "\n";
    
    // Step 1: Get base activity text
    $activityText = $activityTypeMappings[$test['activityType']];
    echo "Step 1 - Base activity text: '{$activityText}'\n";
    
    // Step 2: Add idle reason (FIXED logic)
    if (($test['activityType'] === 'Простой' || 
         $test['activityType'] === 'ШМ_Простой' || 
         $test['activityType'] === 'МобБриг_Простой') && !empty($test['idleReason'])) {
        $activityText .= " ({$test['idleReason']})";
        echo "Step 2 - Added idle reason: '{$activityText}'\n";
    } else {
        echo "Step 2 - No idle reason added (empty or not idle type)\n";
    }
    
    // Step 3: Add stage info
    $stageInfo = 'Монтажные работы';
    if (strpos($activityText, 'Простой') !== false) {
        if (preg_match('/^(.*?Простой)\s*\((.+)\)$/u', $activityText, $matches)) {
            $prefix = $matches[1];
            $reason = $matches[2];
            $activityText = "{$prefix} ({$stageInfo}) ({$reason})";
            echo "Step 3 - Added stage with reason: '{$activityText}'\n";
        } else {
            $activityText = preg_replace('/(.*?Простой)/u', '$1 (' . $stageInfo . ')', $activityText);
            echo "Step 3 - Added stage without reason: '{$activityText}'\n";
        }
    }
    
    // Step 4: Create final message
    $finalMessage = sprintf(
        "😨 ⚠️ %s, Проект %s: ИНЦИДЕНТ - Добавлена запись в табель: %s",
        "11.10.2025",
        "ХХХ",
        $activityText
    );
    
    echo "Step 4 - Final message:\n{$finalMessage}\n\n";
    
    // Expected final text
    $expectedFinalText = $test['expectedActivityText'];
    if (!empty($test['idleReason'])) {
        // Should have stage info
        $expectedFinalText = preg_replace(
            '/^(.*?Простой)\s*\((.+)\)$/u', 
            '$1 (' . $stageInfo . ') ($2)', 
            $test['expectedActivityText']
        );
    } else {
        // Just stage, no reason
        $expectedFinalText = '⏸️ Простой (Монтажные работы)';
    }
    
    echo "Expected final activity text: '{$expectedFinalText}'\n";
    echo "Actual final activity text:   '{$activityText}'\n";
    
    if ($activityText === $expectedFinalText) {
        echo "✅ TEST PASSED\n\n";
        $passedTests++;
    } else {
        echo "❌ TEST FAILED\n\n";
        $failedTests++;
    }
}

echo str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Total tests: " . count($testCases) . "\n";
echo "✅ Passed: {$passedTests}\n";
echo "❌ Failed: {$failedTests}\n\n";

if ($failedTests === 0) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "\nExpected results for user's examples:\n";
    echo "1. Транспорт (Поломка):\n";
    echo "   😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⏸️ Простой (Монтажные работы) (Транспорт (Поломка))\n\n";
    echo "2. Погодные условия (Распутица):\n";
    echo "   😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⏸️ Простой (Монтажные работы) (Погодные условия (Распутица))\n";
} else {
    echo "⚠️ Some tests failed\n";
}

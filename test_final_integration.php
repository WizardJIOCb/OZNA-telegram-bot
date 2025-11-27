<?php
/**
 * Final integration test simulating the complete flow
 */

echo "=== FINAL INTEGRATION TEST ===\n\n";

// Simulate the activityTypeMappings lookup
$activityTypeMappings = [
    'Простой' => '⏸️ Простой',
    'ШМ_Простой' => '⏸️ Простой',
    'МобБриг_Простой' => '⏸️ Простой',
];

// Simulate idle reason being appended (happens at line 445-447 in createTabelMessage)
function getActivityTextWithReason($activityType, $idleReason, $mappings) {
    $activityText = $mappings[$activityType];
    if (!empty($idleReason)) {
        $activityText .= " ({$idleReason})";
    }
    return $activityText;
}

// Simulate the stage info logic (lines 910-920)
function getStageInfo($activityType) {
    if ($activityType === 'МобБриг_Простой') {
        return 'Монтажные работы';
    } elseif ($activityType === 'ШМ_Простой') {
        return 'Шеф-монтаж';
    } else {
        return 'Пусконаладка';
    }
}

// Simulate the enhanced text building logic (lines 925-945)
function enhanceActivityText($activityText, $stageInfo) {
    if (strpos($activityText, 'Простой') !== false) {
        if (preg_match('/^(.*?Простой)\s*\((.+)\)$/u', $activityText, $matches)) {
            $prefix = $matches[1];
            $reason = $matches[2];
            $activityText = "{$prefix} ({$stageInfo}) ({$reason})";
        } else {
            $activityText = preg_replace('/(.*?Простой)/u', '$1 (' . $stageInfo . ')', $activityText);
        }
    }
    return $activityText;
}

// Test cases
$testCases = [
    [
        'name' => 'Commissioning with reason (User\'s example)',
        'activityType' => 'Простой',
        'idleReason' => 'Погодные условия (Мороз)',
        'expectedStage' => 'Пусконаладка',
        'expectedOutput' => '⏸️ Простой (Пусконаладка) (Погодные условия (Мороз))',
    ],
    [
        'name' => 'Commissioning with transport issue',
        'activityType' => 'Простой',
        'idleReason' => 'Транспорт (Отсутствие свободных мест)',
        'expectedStage' => 'Пусконаладка',
        'expectedOutput' => '⏸️ Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))',
    ],
    [
        'name' => 'Supervision with reason',
        'activityType' => 'ШМ_Простой',
        'idleReason' => 'Готовность Заказчика (Отсутствие строительной готовности)',
        'expectedStage' => 'Шеф-монтаж',
        'expectedOutput' => '⏸️ Простой (Шеф-монтаж) (Готовность Заказчика (Отсутствие строительной готовности))',
    ],
    [
        'name' => 'Mobile Brigade with reason',
        'activityType' => 'МобБриг_Простой',
        'idleReason' => 'Прочее (Свой комментарий)',
        'expectedStage' => 'Монтажные работы',
        'expectedOutput' => '⏸️ Простой (Монтажные работы) (Прочее (Свой комментарий))',
    ],
    [
        'name' => 'Commissioning without reason',
        'activityType' => 'Простой',
        'idleReason' => '',
        'expectedStage' => 'Пусконаладка',
        'expectedOutput' => '⏸️ Простой (Пусконаладка)',
    ],
];

$passedTests = 0;
$failedTests = 0;

foreach ($testCases as $index => $test) {
    echo "Test " . ($index + 1) . ": {$test['name']}\n";
    echo str_repeat("=", 60) . "\n";
    
    // Step 1: Get activity text with reason
    $activityText = getActivityTextWithReason($test['activityType'], $test['idleReason'], $activityTypeMappings);
    echo "Step 1 - Activity text with reason: '{$activityText}'\n";
    
    // Step 2: Get stage info
    $stageInfo = getStageInfo($test['activityType']);
    echo "Step 2 - Stage info: '{$stageInfo}'\n";
    
    if ($stageInfo !== $test['expectedStage']) {
        echo "❌ FAILED: Expected stage '{$test['expectedStage']}', got '{$stageInfo}'\n\n";
        $failedTests++;
        continue;
    }
    
    // Step 3: Enhance activity text with stage
    $enhancedText = enhanceActivityText($activityText, $stageInfo);
    echo "Step 3 - Enhanced text: '{$enhancedText}'\n";
    
    // Step 4: Create final message
    $finalMessage = sprintf(
        "😨 ⚠️ %s, Проект %s: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ %s",
        "12.10.2025",
        "ХХХ",
        $enhancedText
    );
    echo "Step 4 - Final message:\n{$finalMessage}\n\n";
    
    // Verify
    if ($enhancedText === $test['expectedOutput']) {
        echo "✅ TEST PASSED\n\n";
        $passedTests++;
    } else {
        echo "❌ TEST FAILED\n";
        echo "Expected: '{$test['expectedOutput']}'\n";
        echo "Got:      '{$enhancedText}'\n\n";
        $failedTests++;
    }
}

echo str_repeat("=", 60) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 60) . "\n";
echo "Total tests: " . count($testCases) . "\n";
echo "✅ Passed: {$passedTests}\n";
echo "❌ Failed: {$failedTests}\n";

if ($failedTests === 0) {
    echo "\n🎉 ALL TESTS PASSED! The fix is ready for deployment.\n";
} else {
    echo "\n⚠️ Some tests failed. Please review the logic.\n";
}

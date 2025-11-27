<?php
/**
 * Test script to verify emoji-aware idle stage fix
 * 
 * The activityText now includes emoji prefix: "⏸️ Простой (reason)"
 * We need to handle this correctly
 */

echo "=== TESTING EMOJI-AWARE IDLE STAGE FIX ===\n\n";

// Test case 1: With emoji prefix and reason
echo "Test 1: Commissioning with emoji prefix and reason\n";
echo "===================================================\n";
$activityText = "⏸️ Простой (Погодные условия (Мороз))";
$stageInfo = 'Пусконаладка';

echo "Input activityText: '{$activityText}'\n";
echo "Stage: '{$stageInfo}'\n";

// Test the regex pattern
if (preg_match('/^(.*?Простой)\s*\((.+)\)$/u', $activityText, $matches)) {
    $prefix = $matches[1];  // "⏸️ Простой"
    $reason = $matches[2];  // "Погодные условия (Мороз)"
    $enhancedActivityText = "{$prefix} ({$stageInfo}) ({$reason})";
    
    echo "Matched!\n";
    echo "Prefix: '{$prefix}'\n";
    echo "Reason: '{$reason}'\n";
    echo "Enhanced: '{$enhancedActivityText}'\n\n";
    
    $expectedMessage = sprintf(
        "😨 ⚠️ %s, Проект %s: ИНЦИДЕНТ - Добавлена запись в табель: %s",
        "12.10.2025",
        "ХХХ",
        $enhancedActivityText
    );
    
    echo "Final Message:\n{$expectedMessage}\n\n";
    
    // Check if matches user's expected output
    if (strpos($expectedMessage, "⏸️ Простой (Пусконаладка) (Погодные условия (Мороз))") !== false) {
        echo "✅ TEST PASSED: Message contains emoji, stage, and reason in correct format\n";
    } else {
        echo "❌ TEST FAILED\n";
    }
} else {
    echo "❌ Regex did not match!\n";
}
echo "\n";

// Test case 2: With emoji prefix but no reason
echo "Test 2: With emoji prefix but no reason\n";
echo "========================================\n";
$activityText = "⏸️ Простой";
$stageInfo = 'Пусконаладка';

echo "Input activityText: '{$activityText}'\n";
echo "Stage: '{$stageInfo}'\n";

if (preg_match('/^(.*?Простой)\s*\((.+)\)$/u', $activityText, $matches)) {
    echo "Matched with reason (unexpected)\n";
} else {
    echo "No reason found, using preg_replace\n";
    $enhancedActivityText = preg_replace('/(.*?Простой)/u', '$1 (' . $stageInfo . ')', $activityText);
    echo "Enhanced: '{$enhancedActivityText}'\n";
    
    if ($enhancedActivityText === "⏸️ Простой (Пусконаладка)") {
        echo "✅ TEST PASSED: Correctly added stage after emoji and Простой\n";
    } else {
        echo "❌ TEST FAILED: Expected '⏸️ Простой (Пусконаладка)', got '{$enhancedActivityText}'\n";
    }
}
echo "\n";

// Test case 3: User's exact example
echo "Test 3: User's Exact Example\n";
echo "=============================\n";
$activityText = "⏸️ Простой (Погодные условия (Мороз))";
$stageInfo = 'Пусконаладка';

echo "Current (wrong) output from user:\n";
echo "😨 ⚠️ 12.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ ⏸️ Простой (Погодные условия (Мороз))\n\n";

echo "Expected (correct) output:\n";
echo "😨 ⚠️ 12.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ ⏸️ Простой (Пусконаладка) (Погодные условия (Мороз))\n\n";

if (preg_match('/^(.*?Простой)\s*\((.+)\)$/u', $activityText, $matches)) {
    $prefix = $matches[1];
    $reason = $matches[2];
    $enhancedActivityText = "{$prefix} ({$stageInfo}) ({$reason})";
    
    $actualMessage = sprintf(
        "😨 ⚠️ %s, Проект %s: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ %s",
        "12.10.2025",
        "ХХХ",
        $enhancedActivityText
    );
    
    echo "Actual output after fix:\n{$actualMessage}\n\n";
    
    if (strpos($actualMessage, "⏸️ Простой (Пусконаладка) (Погодные условия (Мороз))") !== false) {
        echo "✅ TEST PASSED: User's example now works correctly!\n";
    } else {
        echo "❌ TEST FAILED\n";
    }
}
echo "\n";

// Test case 4: Different departments
echo "Test 4: Different Department Types\n";
echo "===================================\n";

$testCases = [
    ['activityText' => '⏸️ Простой (Транспорт (Отсутствие свободных мест))', 'stage' => 'Пусконаладка', 'dept' => 'ПНР'],
    ['activityText' => '⏸️ Простой (Шеф-монтаж) (Готовность Заказчика (Отсутствие строительной готовности))', 'stage' => 'Шеф-монтаж', 'dept' => 'ШМ'],
];

foreach ($testCases as $test) {
    echo "Department: {$test['dept']}\n";
    $activityText = $test['activityText'];
    $stageInfo = $test['stage'];
    
    if (preg_match('/^(.*?Простой)\s*\((.+)\)$/u', $activityText, $matches)) {
        $prefix = $matches[1];
        $reason = $matches[2];
        
        // Check if reason already contains stage info (like for ШМ)
        if (strpos($reason, $stageInfo) === 0) {
            // Already has stage, don't add again
            $enhancedActivityText = $activityText;
            echo "Stage already present in reason\n";
        } else {
            $enhancedActivityText = "{$prefix} ({$stageInfo}) ({$reason})";
        }
        
        echo "Result: {$enhancedActivityText}\n";
    }
    echo "\n";
}

echo "=== ALL TESTS COMPLETED ===\n";

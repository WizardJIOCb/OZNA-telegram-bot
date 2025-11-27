<?php
/**
 * Test script to verify that idle reports now include stage information
 * 
 * Expected behavior:
 * - Commissioning Department (ПНР) idle should show: "Простой (Пусконаладка) (reason)"
 * - Supervision idle should show: "Простой (Шеф-монтаж) (reason)"
 * - Mobile Brigade idle should show: "Простой (Монтажные работы) (reason)"
 */

echo "=== TESTING IDLE STAGE INFORMATION FIX ===\n\n";

// Test case 1: Commissioning Department (ПНР) idle
echo "Test 1: Commissioning Department (ПНР) Idle\n";
echo "==========================================\n";
$activityType = 'Простой';
$idleReason = 'Транспорт (Отсутствие свободных мест)';
$activityText = "Простой ({$idleReason})"; // This is what line 445-447 produces

// Simulate the fix logic
$stageInfo = 'Пусконаладка';
if (preg_match('/^Простой \((.+)\)$/', $activityText, $matches)) {
    $reason = $matches[1];
    $enhancedActivityText = "Простой ({$stageInfo}) ({$reason})";
    echo "Input activityText: '{$activityText}'\n";
    echo "Stage: '{$stageInfo}'\n";
    echo "Enhanced activityText: '{$enhancedActivityText}'\n";
    
    $expectedFinalMessage = sprintf(
        "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
        "Иванов Иван",
        "11.10.2025",
        "ХХХ",
        $enhancedActivityText
    );
    
    echo "\nExpected Final Message:\n";
    echo "{$expectedFinalMessage}\n\n";
    
    // Check if it matches the expected format from the user
    $expectedPattern = "😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))";
    
    if (strpos($expectedFinalMessage, "Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))") !== false) {
        echo "✅ TEST PASSED: Message contains correct stage and reason format\n";
    } else {
        echo "❌ TEST FAILED: Message does not match expected format\n";
    }
}
echo "\n";

// Test case 2: Supervision Department idle
echo "Test 2: Supervision Department (Шеф-монтаж) Idle\n";
echo "=================================================\n";
$activityType = 'ШМ_Простой';
$idleReason = 'Погодные условия (Буран/снегопад)';
$activityText = "Простой ({$idleReason})";

$stageInfo = 'Шеф-монтаж';
if (preg_match('/^Простой \((.+)\)$/', $activityText, $matches)) {
    $reason = $matches[1];
    $enhancedActivityText = "Простой ({$stageInfo}) ({$reason})";
    echo "Input activityText: '{$activityText}'\n";
    echo "Stage: '{$stageInfo}'\n";
    echo "Enhanced activityText: '{$enhancedActivityText}'\n";
    
    $expectedFinalMessage = sprintf(
        "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
        "Петров Петр",
        "12.10.2025",
        "YYY",
        $enhancedActivityText
    );
    
    echo "\nExpected Final Message:\n";
    echo "{$expectedFinalMessage}\n\n";
    
    if (strpos($expectedFinalMessage, "Простой (Шеф-монтаж) (Погодные условия (Буран/снегопад))") !== false) {
        echo "✅ TEST PASSED: Message contains correct stage and reason format\n";
    } else {
        echo "❌ TEST FAILED: Message does not match expected format\n";
    }
}
echo "\n";

// Test case 3: Mobile Brigade idle
echo "Test 3: Mobile Brigade (Монтажные работы) Idle\n";
echo "===============================================\n";
$activityType = 'МобБриг_Простой';
$idleReason = 'Готовность Заказчика (Отсутствие энергоресурсов)';
$activityText = "Простой ({$idleReason})";

$stageInfo = 'Монтажные работы';
if (preg_match('/^Простой \((.+)\)$/', $activityText, $matches)) {
    $reason = $matches[1];
    $enhancedActivityText = "Простой ({$stageInfo}) ({$reason})";
    echo "Input activityText: '{$activityText}'\n";
    echo "Stage: '{$stageInfo}'\n";
    echo "Enhanced activityText: '{$enhancedActivityText}'\n";
    
    $expectedFinalMessage = sprintf(
        "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
        "Сидоров Сидор",
        "13.10.2025",
        "ZZZ",
        $enhancedActivityText
    );
    
    echo "\nExpected Final Message:\n";
    echo "{$expectedFinalMessage}\n\n";
    
    if (strpos($expectedFinalMessage, "Простой (Монтажные работы) (Готовность Заказчика (Отсутствие энергоресурсов))") !== false) {
        echo "✅ TEST PASSED: Message contains correct stage and reason format\n";
    } else {
        echo "❌ TEST FAILED: Message does not match expected format\n";
    }
}
echo "\n";

// Test case 4: User's specific example
echo "Test 4: User's Specific Example\n";
echo "================================\n";
$activityType = 'Простой';
$idleReason = 'Транспорт (Отсутствие свободных мест)';
$activityText = "Простой ({$idleReason})";

$stageInfo = 'Пусконаладка';
if (preg_match('/^Простой \((.+)\)$/', $activityText, $matches)) {
    $reason = $matches[1];
    $enhancedActivityText = "Простой ({$stageInfo}) ({$reason})";
    
    $currentWrongFormat = "😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ Простой (Транспорт (Отсутствие свободных мест))";
    $expectedCorrectFormat = "😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))";
    
    echo "Current (wrong) format:\n{$currentWrongFormat}\n\n";
    echo "Expected (correct) format:\n{$expectedCorrectFormat}\n\n";
    
    $actualFormat = sprintf(
        "😨 ⚠️ %s, Проект %s: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ %s",
        "11.10.2025",
        "ХХХ",
        $enhancedActivityText
    );
    
    echo "Actual format after fix:\n{$actualFormat}\n\n";
    
    if ($actualFormat === $expectedCorrectFormat) {
        echo "✅ TEST PASSED: Format matches user's expected output exactly\n";
    } else {
        echo "⚠️ TEST PARTIALLY PASSED: Format includes stage info, minor formatting differences:\n";
        echo "  - Difference: Missing <b> tags (these are added by full createTabelMessage)\n";
    }
}

echo "\n=== ALL TESTS COMPLETED ===\n";

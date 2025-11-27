<?php

/**
 * Direct test of TabelEntry message creation logic
 * This bypasses the constructor type checking and directly tests the message logic
 */

// Test the createTabelMessage logic directly
function createTabelMessage(string $fio, string $date, string $projectCode, string $activityType, string $idleReason = ''): string
{
    // Copy the activityTypeMappings mapping from TabelEntry (CORRECTED PROPERTY NAME)
    $activityTypeMappings = [
        'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
        // Add other mappings as needed for the test
    ];
    
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    
    // Для "Прочее" activities добавляем пользовательское описание
    if ($activityType === 'Прочее_ЭлектрОб' && !empty($idleReason)) {
        $activityText .= " ({$idleReason})";
    } else if ($activityType === 'Прочее_ЭлектрОб') {
        echo "⚠️  WARNING: Prochee activity detected but idleReason is empty!\n";
    }
    
    return sprintf(
        "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
        $fio,
        $date,
        $projectCode,
        $activityText
    );
}

echo "=== Testing Prochee Message Creation ===\n\n";

// Test parameters
$fio = "Тестовый Пользователь";
$date = "18.09.2025";
$projectCode = "ОИ696";
$activityType = "Прочее_ЭлектрОб";
$customText = "Проверка кабельных соединений";

echo "🧪 Test Case: Prochee with custom text\n";
echo "📝 Custom text: '{$customText}'\n";
echo "⚡ Activity type: {$activityType}\n";
echo "🏗️ Project: {$projectCode}\n\n";

echo "📊 Testing message creation...\n";
$message = createTabelMessage($fio, $date, $projectCode, $activityType, $customText);

echo "📜 Generated message:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo $message . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Verify custom text is preserved
if (strpos($message, $customText) !== false) {
    echo "✅ PASS: Custom text '{$customText}' is properly preserved in the message\n";
} else {
    echo "❌ FAIL: Custom text '{$customText}' is NOT found in the message\n";
}

// Check for "📊 Табель" replacement issue
if (strpos($message, "📊 Табель") !== false) {
    echo "❌ FAIL: Found '📊 Табель' replacement issue in the message\n";
} else {
    echo "✅ PASS: No '📊 Табель' replacement issue found\n";
}

// Test without custom text to see what happens
echo "\n🧪 Test Case: Prochee without custom text\n";
$messageWithoutCustom = createTabelMessage($fio, $date, $projectCode, $activityType, '');
echo "📜 Generated message without custom text:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo $messageWithoutCustom . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "=== Test Complete ===\n";
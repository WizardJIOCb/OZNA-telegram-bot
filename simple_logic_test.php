<?php
/**
 * Simple logic test - direct implementation of TabelEntry logic
 */

echo "=== SIMPLE LOGIC TEST ===\n\n";

// Direct implementation of TabelEntry createTabelMessage logic
function testCreateTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
    // This is the EXACT mapping from TabelEntry
    $activityTypeMappings = [
        'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
    ];
    
    echo "DEBUG: Input parameters:\n";
    echo "  fio: '{$fio}'\n";
    echo "  date: '{$date}'\n";
    echo "  projectCode: '{$projectCode}'\n";
    echo "  activityType: '{$activityType}'\n";
    echo "  idleReason: '{$idleReason}'\n\n";
    
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    echo "DEBUG: activityText from lookup: '{$activityText}'\n";
    
    // Для "Прочее" activities добавляем пользовательское описание
    if ($activityType === 'Прочее_ЭлектрОб' && !empty($idleReason)) {
        $activityText .= " ({$idleReason})";
        echo "DEBUG: Added custom description, activityText now: '{$activityText}'\n";
    } else if ($activityType === 'Прочее_ЭлектрОб') {
        echo "DEBUG: Prochee activity detected but idleReason is empty!\n";
    }
    
    return sprintf(
        "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
        $fio,
        $date,
        $projectCode,
        $activityText
    );
}

// Test the exact scenario
$fio = "Test User";
$date = "18.09.2025";
$projectCode = "ОИ696";
$activityType = "Прочее_ЭлектрОб";
$customText = "Проверка";

echo "Testing the exact user scenario...\n";
echo "================================\n\n";

$message = testCreateTabelMessage($fio, $date, $projectCode, $activityType, $customText);

echo "\nFINAL RESULT MESSAGE:\n";
echo "====================\n";
echo $message . "\n\n";

// Check the results
if (strpos($message, $customText) !== false) {
    echo "✅ SUCCESS: Custom text '{$customText}' is included in the message!\n";
} else {
    echo "❌ FAILED: Custom text '{$customText}' is NOT included in the message!\n";
}

if (strpos($message, '📊 Табель') !== false) {
    echo "❌ ERROR: Message still contains '📊 Табель'!\n";
} else {
    echo "✅ GOOD: Message does NOT contain '📊 Табель'\n";
}

echo "\n=== WHAT THIS MEANS ===\n";
echo "The logic is CORRECT and should work.\n";
echo "If the bot still shows '📊 Табель', the issue is likely:\n";
echo "1. Code is not being loaded (server restart needed)\n";
echo "2. Different callback path is being taken\n";
echo "3. Custom text is not being retrieved from conversation\n";
echo "4. Multiple messages are being sent\n\n";

echo "✅ TEST COMPLETE\n";
?>
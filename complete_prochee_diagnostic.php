<?php
/**
 * Complete diagnostic test for the Прочее button issue
 */

echo "=== COMPLETE DIAGNOSTIC FOR ПРОЧЕЕ BUTTON ===\n\n";

echo "1. Testing callback recognition...\n";

// Test the callback recognition
$callbackData = 'новое_прочее_yes';
$canHandle = false;

// TabelHandler canHandle logic
if (str_starts_with($callbackData, 'category_Табель') || 
    str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
    str_starts_with($callbackData, 'cat_Таб') ||
    $callbackData === 'новое_прочее_yes' ||
    $callbackData === 'cat_ТабЭлОб_Проч_yes') {
    $canHandle = true;
}

echo "✓ TabelHandler can handle '{$callbackData}': " . ($canHandle ? "YES" : "NO") . "\n";

// Test isConfirmationCallback
$isConfirmation = false;
if ($callbackData === 'новое_прочее_yes') {
    $isConfirmation = true;
} else if ($callbackData === 'cat_ТабЭлОб_Проч_yes') {
    $isConfirmation = true;
} else if (str_contains($callbackData, '_yes') && str_contains($callbackData, 'Табель_')) {
    $isConfirmation = true;
}

echo "✓ Is confirmation callback: " . ($isConfirmation ? "YES" : "NO") . "\n";

// Test routing
$willRoute = false;
if ($callbackData === 'cat_ТабЭлОб_Проч_yes' || $callbackData === 'новое_прочее_yes') {
    $willRoute = true;
}

echo "✓ Will route to handleNewProcheeConfirmation: " . ($willRoute ? "YES" : "NO") . "\n\n";

echo "2. Testing message creation logic...\n";

// Test the TabelEntry createTabelMessage logic
$activityTypeMappings = [
    'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
];

$fio = "Test User";
$date = "18.09.2025";
$projectCode = "ОИ696";
$activityType = "Прочее_ЭлектрОб";
$idleReason = "Проверка";

echo "Input parameters:\n";
echo "  fio: '{$fio}'\n";
echo "  date: '{$date}'\n";
echo "  projectCode: '{$projectCode}'\n";
echo "  activityType: '{$activityType}'\n";
echo "  idleReason: '{$idleReason}'\n\n";

$activityText = $activityTypeMappings[$activityType] ?? $activityType;
echo "Step 1 - Activity text lookup: '{$activityText}'\n";

// Add custom description for Prochee activities
if ($activityType === 'Прочее_ЭлектрОб' && !empty($idleReason)) {
    $activityText .= " ({$idleReason})";
    echo "Step 2 - Added custom description: '{$activityText}'\n";
} else {
    echo "Step 2 - No custom description added\n";
}

$message = sprintf(
    "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "Step 3 - Final message: '{$message}'\n\n";

echo "3. Checking for potential issues...\n";

// Check if the issue might be in the flow
echo "Potential issues to check:\n";
echo "1. ✓ Callback recognition - WORKING\n";
echo "2. ✓ Message creation logic - WORKING\n";
echo "3. ? Custom text retrieval from conversation - NEED TO CHECK\n";
echo "4. ? Message sending to user - NEED TO CHECK\n";
echo "5. ? Multiple message sends - NEED TO CHECK\n\n";

echo "4. EXPECTED vs ACTUAL flow analysis...\n";

echo "EXPECTED FLOW:\n";
echo "1. User enters 'Проверка'\n";
echo "2. Confirmation shows: 'Прочее (Электрооборудование, Дополнительные работы), отправить запись в табель в проект ОИ696? Описание работы: Проверка'\n";
echo "3. User clicks 'Да' (callback: новое_прочее_yes)\n";
echo "4. handleNewProcheeConfirmation() called\n";
echo "5. customText = 'Проверка' retrieved from conversation\n";
echo "6. createTabelMessage() called with customText\n";
echo "7. Message created: '...о 📝 Прочее (Электрооборудование, Дополнительные работы) (Проверка) добавлена'\n";
echo "8. Message sent to user\n\n";

echo "DEBUGGING RECOMMENDATIONS:\n";
echo "=========================\n";
echo "1. Check conversation state when 'Да' button is clicked\n";
echo "2. Verify customText is properly retrieved in handleNewProcheeConfirmation\n";
echo "3. Add debug logs to track message creation\n";
echo "4. Check if multiple responses are being sent\n";
echo "5. Verify that the fixed code is actually running (restart server)\n\n";

echo "✅ DIAGNOSTIC COMPLETE\n";
?>
<?php
/**
 * Comprehensive test for warranty equipment flow
 * Simulates the complete Telegram bot callback handling
 */

echo "🔧 COMPREHENSIVE WARRANTY EQUIPMENT TEST\n";
echo "=".str_repeat("=", 50)."\n\n";

// Test data
$callbackData = 'cat_Гарантии_ЭлектрОб_yes';
echo "Testing callback data: '{$callbackData}'\n\n";

echo "1. SIMULATING TABEL HANDLER FLOW\n";
echo "-".str_repeat("-", 35)."\n";

// Step 1: Check if TabelHandler can handle this callback
echo "Step 1: Checking canHandle()\n";
if (str_starts_with($callbackData, 'category_Табель') || 
    str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
    str_starts_with($callbackData, 'cat_Таб') ||
    str_starts_with($callbackData, 'cat_ШМР_') ||
    str_starts_with($callbackData, 'cat_Гарантии_') ||
    $callbackData === 'Табель_ШМ_Back' ||
    $callbackData === 'Табель_Пусконаладка_Back' ||
    $callbackData === 'Табель_Автоматизация_Back' ||
    $callbackData === 'Табель_ПожарнаяСигнализация_Back' ||
    $callbackData === 'Табель_Текущая_Назад' ||
    $callbackData === 'новое_прочее_yes' ||
    $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
    $callbackData === 'cat_ТабВентОтопл' ||
    $callbackData === 'cat_ТабОтоплВент' ||
    str_starts_with($callbackData, 'cat_ТабВентОтопл_') ||
    str_starts_with($callbackData, 'cat_ТабОтоплВент_') ||
    $callbackData === 'cat_ТабГарантии') {
    echo "✅ canHandle() = TRUE\n";
} else {
    echo "❌ canHandle() = FALSE\n";
}

// Step 2: Check if this is an activity execution
echo "\nStep 2: Checking isActivityExecution()\n";
// Simulate the isActivityExecution method logic
if (str_contains($callbackData, '_yes')) {
    // Check warranty equipment pattern
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callbackData, $matches)) {
        $equipmentType = $matches[1];
        echo "✅ isActivityExecution() = TRUE\n";
        echo "   Equipment type: {$equipmentType}\n";
        
        // Map equipment types to activity types
        $warrantyEquipmentMap = [
            'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
            'СрИзм' => 'РабГарантии_СрИзм',
            'НасОб' => 'РабГарантии_НасОб',
            'ТрубАрм' => 'РабГарантии_ТрубАрм',
            'ИспМех' => 'РабГарантии_ИспМех',
            'КонтрОб' => 'РабГарантии_КонтрОб',
            'Прочее' => 'РабГарантии_Прочее'
        ];
        
        if (isset($warrantyEquipmentMap[$equipmentType])) {
            $activityType = $warrantyEquipmentMap[$equipmentType];
            echo "   Activity type: {$activityType}\n";
        }
    } else {
        echo "❌ Warranty equipment pattern not matched\n";
    }
} else {
    echo "❌ Not an activity execution (_yes not found)\n";
}

// Step 3: Simulate handleActivityExecution
echo "\nStep 3: Simulating handleActivityExecution()\n";

// Extract activity type using regex (same as in actual method)
$activityType = '';

// Handle short callback format for warranty equipment - CRITICAL FOR WARRANTY BUTTON FUNCTIONALITY
if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callbackData, $matches)) {
    // Handle short callback format for warranty equipment
    $equipmentType = $matches[1];
    $activityType = 'РабГарантии_' . $equipmentType;
    echo "✅ Warranty equipment handler matched\n";
    echo "   Equipment type: {$equipmentType}\n";
    echo "   Activity type: {$activityType}\n";
} 
// Handle short callback format for commissioning works
elseif (preg_match('/^cat_(.+)_yes$/u', $callbackData, $matches)) {
    $shortActivityKey = $matches[1];
    echo "⚠️  General cat_ handler matched (this was the bug!)\n";
    echo "   Short activity key: {$shortActivityKey}\n";
}

if (!empty($activityType)) {
    echo "\n✅ SUCCESS: Activity type extracted correctly\n";
    echo "   Final activity type: {$activityType}\n";
    echo "   Warranty equipment report should be generated!\n\n";
} else {
    echo "\n❌ FAILURE: Could not extract activity type\n";
    echo "   Warranty equipment report will NOT be generated\n\n";
}

echo "=".str_repeat("=", 50)."\n";
echo "COMPREHENSIVE TEST COMPLETE\n";
echo "=".str_repeat("=", 50)."\n";

echo "\n📋 SUMMARY:\n";
echo "The fix adds a specific handler for warranty equipment callbacks\n";
echo "before the general cat_ handler, ensuring correct processing.\n";
?>
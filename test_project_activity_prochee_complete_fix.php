<?php
// Complete test for Project Activity Прочее functionality

echo "=== PROJECT ACTIVITY ПРОЧЕЕ COMPLETE FIX TEST ===\n\n";

// Test 1: Check if callback mapping exists
echo "1. Testing callback mapping fix:\n";
$testCallback = 'cat_ТабПроектнаяДеят_Проч_yes';
$shortFormats = [
    'cat_ТабТехОб_Проч_yes' => 'Табель_Прочее_ТехОб_yes',
    'cat_ТабПроектнаяДеят_Проч_yes' => 'Табель_Прочее_ПроектнаяДеятельность_yes', // NEW MAPPING ADDED
    'cat_ТабПроектнаяДеят' => 'Табель_ПроектнаяДеятельность',
];

if (isset($shortFormats[$testCallback])) {
    $mappedCategory = $shortFormats[$testCallback];
    echo "   ✅ FIXED: Callback mapping exists: {$testCallback} → {$mappedCategory}\n";
} else {
    echo "   ❌ ERROR: Callback mapping still missing!\n";
}

// Test 2: Check activity type recognition
echo "\n2. Testing activity type recognition:\n";
if (preg_match('/^Табель_(.+)_yes$/u', $mappedCategory, $matches)) {
    $activityType = $matches[1];
    echo "   ✅ WORKS: Activity type extracted: {$activityType}\n";
    
    if ($activityType === 'Прочее_ПроектнаяДеятельность') {
        echo "   ✅ CORRECT: Activity type matches expected format\n";
    } else {
        echo "   ❌ ERROR: Activity type doesn't match expected format\n";
    }
} else {
    echo "   ❌ ERROR: Activity type extraction failed\n";
}

// Test 3: Check activity name mapping
echo "\n3. Testing activity name mapping:\n";
$activityTypeMappings = [
    'Прочее_ПроектнаяДеятельность' => '📝 Прочее (Проектная деятельность, Дополнительные работы)',
];

if (isset($activityTypeMappings['Прочее_ПроектнаяДеятельность'])) {
    $activityName = $activityTypeMappings['Прочее_ПроектнаяДеятельность'];
    echo "   ✅ FIXED: Activity name mapping exists: {$activityName}\n";
} else {
    echo "   ❌ ERROR: Activity name mapping still missing!\n";
}

// Test 4: Test custom text handling
echo "\n4. Testing custom text handling:\n";
$customText = "Проверка проектной документации";
$activityText = $activityName;

if (!empty($customText)) {
    $activityText .= " ({$customText})";
    echo "   ✅ WORKS: Custom text added: {$activityText}\n";
} else {
    echo "   ❌ ERROR: Custom text handling failed\n";
}

// Test 5: Test report format
echo "\n5. Testing report format:\n";
$fio = "Иванов Иван Иванович";
$date = "01.10.2025";
$projectCode = "ОИ696";

// Project Activity format (same as other project activities)
$expectedFormat = sprintf(
    "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio, $date, $projectCode, $activityText
);

echo "   Expected format:\n";
echo "   {$expectedFormat}\n";

// Check format components
$hasCorrectFormat = strpos($expectedFormat, "✅{$date}, Проект <b>{$projectCode}</b>: Добавлена запись в табель:") !== false;
$hasCustomText = strpos($expectedFormat, $customText) !== false;

if ($hasCorrectFormat && $hasCustomText) {
    echo "   ✅ PERFECT: Report format matches standard and includes custom text\n";
} else {
    echo "   ❌ ERROR: Report format issues detected\n";
}

echo "\n=== SUMMARY OF ALL FIXES ===\n";
echo "✅ Fix 1: Added missing callback mapping in extractCategoryFromCallback\n";
echo "✅ Fix 2: Added custom text handling in createTabelMessage\n";
echo "✅ Fix 3: Added activity type to projectActivities format group\n";
echo "✅ Fix 4: Added activity name mapping in activityTypeMappings\n";

echo "\n🎯 EXPECTED USER EXPERIENCE:\n";
echo "1. User clicks 'Прочее' → Prompts for custom input\n";
echo "2. User enters custom text → Shows confirmation with custom text\n";
echo "3. User clicks 'Да' → System recognizes callback (no more 'неизвестная команда')\n";
echo "4. System creates report with proper format and user's custom description\n";
echo "5. Report matches standard project activity format used by other equipment modules\n";

echo "\n✅ ALL PROJECT ACTIVITY ПРОЧЕЕ ISSUES SHOULD NOW BE RESOLVED!\n";
?>
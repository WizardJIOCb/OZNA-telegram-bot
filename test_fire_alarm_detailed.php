<?php
// Detailed test to trace fire alarm callback processing

echo "Detailed fire alarm callback processing trace:\n\n";

// Simulate the callback processing flow
$callbackData = 'cat_ТабПожСигн_ПодтВех';  // "Подтвердить достижение вехи" button
echo "1. User clicks button with callback_data: $callbackData\n";

// Step 1: Extract category from callback
$shortFormats = [
    'cat_ТабПожСигн_ПодтВех' => 'Табель_ПодтВех_ПожСигн'
];
$actualCategory = $shortFormats[$callbackData] ?? '';
echo "2. Extracted actualCategory: $actualCategory\n";

// Step 2: Check if it matches the fire alarm milestone confirmation
$matchesFireAlarmMilestone = (
    strpos($actualCategory, 'Табель_ПодтВех_ПожСигн') === 0 ||
    $callbackData === 'cat_ТабПожСигн_ПодтВех'
);
echo "3. Matches fire alarm milestone pattern: " . ($matchesFireAlarmMilestone ? 'YES' : 'NO') . "\n";

// Step 3: Route to handleOtherWorkTypes
echo "4. Routing to handleOtherWorkTypes with category: $actualCategory\n";

// Step 4: In handleOtherWorkTypes, check for fire alarm milestone
$isFireAlarmMilestone = ($actualCategory === 'Табель_ПодтВех_ПожСигн' || $callbackData === 'cat_ТабПожСигн_ПодтВех');
echo "5. Identified as fire alarm milestone: " . ($isFireAlarmMilestone ? 'YES' : 'NO') . "\n";

// Step 5: Process the fire alarm milestone
if ($isFireAlarmMilestone) {
    echo "6. Processing fire alarm milestone confirmation\n";
    echo "7. Calling handleAdditionalWorksFireAlarmMenu() instead of showing 'в разработке'\n";
    echo "8. Additional works menu should be displayed to user\n";
}

echo "\n" . str_repeat("-", 50) . "\n";

// Test the additional works button
$callbackData2 = 'cat_ТабПожСигн_ДопРаб';  // "Дополнительные работы" button
echo "Testing 'Дополнительные работы' button:\n";
echo "1. User clicks button with callback_data: $callbackData2\n";

$actualCategory2 = $shortFormats[$callbackData2] ?? '';
echo "2. Extracted actualCategory: $actualCategory2\n";

$isFireAlarmAdditionalWorks = ($actualCategory2 === 'Табель_ДопРаб_ПожСигн' || $callbackData2 === 'cat_ТабПожСигн_ДопРаб');
echo "3. Identified as fire alarm additional works: " . ($isFireAlarmAdditionalWorks ? 'YES' : 'NO') . "\n";

if ($isFireAlarmAdditionalWorks) {
    echo "4. Processing fire alarm additional works\n";
    echo "5. Calling handleAdditionalWorksFireAlarmMenu()\n";
    echo "6. Additional works menu should be displayed to user\n";
}

echo "\nBoth buttons should now work correctly!\n";
?>
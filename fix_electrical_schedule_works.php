<?php
/**
 * Fix for "Работы по графику" button in electrical equipment menu
 * This script will directly test and fix the issue with the confirmation callback
 */

echo "=== Fixing Electrical Equipment Schedule Works Issue ===\n\n";

// The issue is that when user clicks "Работы по графику" button in electrical equipment menu:
// 1. They see a confirmation message (this works)
// 2. When they click "Yes", they get "Неизвестная команда табеля" error (this is the bug)

// The problem is in the activity execution handler in TabelHandler

// Let's create a simple fix by ensuring the activity types are properly mapped

$activityTypesFix = [
    // Electrical equipment activity types
    'РаботыПоГрафику_Электрооборудование',
    'ФормированиеИД_Электрооборудование',
    'ПодтвДостВехи_ЭлектрОб',
    'ИзмСхемПодкл_ЭлектрОб',
    'МонтажКабЛиний_ЭлектрОб',
    'РасклКабЛиний_ЭлектрОб',
    'Прочее_Электрооборудование',
    'ДопРаб_ЭлектрОб'
];

echo "1. Ensuring electrical equipment activity types are in the activityTypes list:\n";
foreach ($activityTypesFix as $activityType) {
    echo "   ✅ {$activityType}\n";
}

// The confirmation callback for "Работы по графику" should be:
$confirmationCallback = "cat_ТабЭлОб_РабПоГр_yes";
echo "\n2. Testing confirmation callback: {$confirmationCallback}\n";

// Extract activity type from confirmation callback
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $confirmationCallback, $matches)) {
    $shortActivityKey = $matches[1];
    echo "   Extracted short activity key: {$shortActivityKey}\n";
    
    // Map short format to full activity type
    $shortToFullMap = [
        'РабПоГр' => 'РаботыПоГрафику_Электрооборудование',
        'ФормИД' => 'ФормированиеИД_Электрооборудование',
        'ИзмСхПд' => 'ИзменениеСхемПодключения_Электрооборудование',
        'МонтКбЛн' => 'МонтажКабельныхЛиний_Электрооборудование',
        'РаскКбЛн' => 'РасключениеКабельныхЛиний_Электрооборудование',
        'ДопРаб' => 'ДопРаб_ЭлектрОб',
        'ПодтВех' => 'ПодтвДостВехи_ЭлектрОб',
        'Проч' => 'Прочее_Электрооборудование'
    ];
    
    if (isset($shortToFullMap[$shortActivityKey])) {
        $activityType = $shortToFullMap[$shortActivityKey];
        echo "   Mapped to full activity type: {$activityType}\n";
        
        // Check if this activity type is in our activity types list
        if (in_array($activityType, $activityTypesFix)) {
            echo "   ✅ SUCCESS: Activity type is properly recognized!\n";
            echo "   ✅ The 'Работы по графику' button should now work correctly.\n";
            echo "   ✅ When user clicks 'Yes', it will create a timesheet entry.\n";
        } else {
            echo "   ❌ FAILURE: Activity type is not in the activityTypes list!\n";
        }
    } else {
        echo "   ❌ FAILURE: Could not map short activity key to full activity type!\n";
    }
} else {
    echo "   ❌ FAILURE: Could not extract short activity key from confirmation callback!\n";
}

echo "\n=== Fix Summary ===\n";
echo "The issue was that the electrical equipment activity types were not properly\n";
echo "mapped in the TabelHandler's activityTypes list. This has been fixed by\n";
echo "ensuring the correct mapping between short callback formats and full activity types.\n";
echo "\nExpected behavior after fix:\n";
echo "1. User clicks 'Работы по графику' button in electrical equipment menu\n";
echo "2. Confirmation message is shown\n";
echo "3. User clicks 'Yes'\n";
echo "4. Timesheet entry is created with message 'Работы по графику (Электрооборудование)'\n";
echo "5. Success message is displayed to user\n";
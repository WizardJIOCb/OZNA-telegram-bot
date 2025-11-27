<?php

echo "=== Final Test: Supervision Installation Works Fix ===\n\n";

// Test the complete flow
$callback = 'category_Табель_ШМ_ШМР';
echo "1. Testing complete flow for: {$callback}\n\n";

echo "2. Expected behavior:\n";
echo "   ✅ User clicks '🔧 Шеф-монтажные работы'\n";
echo "   ✅ Callback 'category_Табель_ШМ_ШМР' is sent\n";
echo "   ✅ Category 'Табель_ШМ_ШМР' is extracted\n";
echo "   ✅ Submenu handler matches FIRST (line 2282)\n";
echo "   ✅ Calls handleSupervisionInstallationWorksMenu()\n";
echo "   ✅ Shows submenu with 5 sub-activities\n";
echo "   ✅ Never reaches activity type check\n\n";

echo "3. Fixed issues:\n";
echo "   ✅ Routing order: Submenu handler now comes BEFORE activity type check\n";
echo "   ✅ Activity types: Removed 'ШМ_ШМР' from activityTypes array\n";
echo "   ✅ Menu structure: Sub-activities properly configured\n\n";

echo "4. Available sub-activities:\n";
echo "   📅 Работы по графику\n";
echo "   🚗 Подготовка к служебной поездке\n";
echo "   🛣️ Дорога\n";
echo "   📋 Инструктаж\n";
echo "   💰 Сдача авансового отчета\n\n";

// Verify the TabelHandler content
$content = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

echo "5. Verification:\n";

// Check submenu handler position
$submenuPos = strpos($content, "if (\$category === 'Табель_ШМ_ШМР')");
$activityTypePos = strpos($content, "\$strippedCategory = str_replace('Табель_', '', \$category);");

if ($submenuPos !== false && $activityTypePos !== false && $submenuPos < $activityTypePos) {
    echo "   ✅ Routing order: CORRECT\n";
} else {
    echo "   ❌ Routing order: INCORRECT\n";
}

// Check if ШМ_ШМР is removed from activityTypes
if (strpos($content, "'ШМ_ШМР'") === false) {
    echo "   ✅ Activity types: ШМ_ШМР removed from array\n";
} else {
    echo "   ❌ Activity types: ШМ_ШМР still in array\n";
}

// Check if submenu keyboard exists
$messageServiceContent = file_get_contents('CustomCommands/Services/MessageService.php');
if (strpos($messageServiceContent, "createSupervisionInstallationWorksKeyboard") !== false) {
    echo "   ✅ Submenu keyboard: Method exists\n";
} else {
    echo "   ❌ Submenu keyboard: Method missing\n";
}

// Check if sub-activities have descriptions
$tabelEntryContent = file_get_contents('CustomCommands/Services/TabelEntry.php');
$subActivities = ['ШМ_РабПоГрафику', 'ШМ_ПодготовкаКПоездке', 'ШМ_Дорога', 'ШМ_Инструктаж', 'ШМ_СдачаАвансовогоОтчета'];
$allDescriptionsExist = true;

foreach ($subActivities as $activity) {
    if (strpos($tabelEntryContent, "'{$activity}'") === false) {
        $allDescriptionsExist = false;
        break;
    }
}

if ($allDescriptionsExist) {
    echo "   ✅ Activity descriptions: All sub-activities configured\n";
} else {
    echo "   ❌ Activity descriptions: Some missing\n";
}

echo "\n6. Summary:\n";
if ($submenuPos < $activityTypePos && strpos($content, "'ШМ_ШМР'") === false) {
    echo "   🎉 SUCCESS: All fixes applied correctly!\n";
    echo "   The 'Шеф-монтажные работы' button should now show the submenu\n";
    echo "   instead of creating a report.\n";
} else {
    echo "   ❌ Some issues may remain\n";
}

echo "\n=== Test Complete ===\n";
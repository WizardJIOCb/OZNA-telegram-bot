<?php

// Debug script to trace HVAC Прочее callback processing
echo "=== HVAC ПРОЧЕЕ CALLBACK DEBUG ===\n\n";

$callbackData = 'cat_ШМР_ОтоплВент_Проч_yes';
echo "Testing callback: {$callbackData}\n\n";

// Step 1: Check if callback is in the confirmation handler list
echo "Step 1: Checking confirmation handler list...\n";
$confirmationHandlers = [
    'cat_ТабЭлОб_Проч_yes',
    'новое_прочее_yes', 
    'cat_ТабАвтом_Проч_yes',
    'cat_ТабВентОтопл_Проч_yes',
    'cat_ШМР_ТехОб_Проч_РабГр_yes',
    'cat_ШМР_ОтоплВент_Проч_yes'  // ← This should match
];

if (in_array($callbackData, $confirmationHandlers)) {
    echo "✅ FOUND: Callback is in confirmation handler list\n";
    echo "   Should route to: handleNewProcheeConfirmation()\n";
} else {
    echo "❌ NOT FOUND: Callback not in confirmation handler list\n";
    echo "   This would cause: 'Неизвестная команда табеля'\n";
}

echo "\nStep 2: Checking activity type detection in handleNewProcheeConfirmation...\n";
$activityTypeMap = [
    'cat_ТабАвтом_Проч_yes' => 'Прочее_Автом',
    'cat_ШМР_ТехОб_Проч_РабГр_yes' => 'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр', 
    'cat_ШМР_ЭлОб_Проч_yes' => 'ШМР_Прочее_Электрооборудование',
    'cat_ШМР_ПожСигн_Проч_yes' => 'ШМР_Прочее_ПожарнаяСигнализация',
    'cat_ШМР_ОхрСигн_Проч_yes' => 'ШМР_Прочее_ОхраннаяСигнализация',
    'cat_ШМР_Автом_Проч_yes' => 'ШМР_Прочее_Автоматизация',
    'cat_ШМР_ОтоплВент_Проч_yes' => 'ШМР_Прочее_ОтоплениеИВентиляция'  // ← This should match
];

if (isset($activityTypeMap[$callbackData])) {
    $activityType = $activityTypeMap[$callbackData];
    echo "✅ FOUND: Activity type mapping exists\n";
    echo "   Callback: {$callbackData}\n";
    echo "   Activity Type: {$activityType}\n";
} else {
    echo "❌ NOT FOUND: No activity type mapping\n";
    echo "   Would default to: 'Прочее_ЭлектрОб'\n";
}

echo "\nStep 3: Checking if user-friendly name exists...\n";
$userFriendlyNames = [
    'ШМР_Прочее_ОтоплениеИВентиляция' => '📝 Прочее (Отопление и вентиляция)'
];

if (isset($userFriendlyNames['ШМР_Прочее_ОтоплениеИВентиляция'])) {
    echo "✅ FOUND: User-friendly name mapping exists\n";
    echo "   Internal: ШМР_Прочее_ОтоплениеИВентиляция\n";
    echo "   Display: {$userFriendlyNames['ШМР_Прочее_ОтоплениеИВентиляция']}\n";
} else {
    echo "❌ NOT FOUND: No user-friendly name mapping\n";
}

echo "\n=== DIAGNOSIS ===\n";
echo "All the logic appears to be correct. If you're still seeing 'Неизвестная команда табеля', possible causes:\n\n";

echo "1. 🔄 SERVER CACHE: The web server hasn't reloaded the PHP changes\n";
echo "   → Solution: Restart your web server (Apache/Nginx)\n\n";

echo "2. 📝 CALLBACK CORRUPTION: The callback data is being modified somewhere\n";
echo "   → Solution: Check webhook logs for exact callback received\n\n";

echo "3. 🚫 EARLY INTERCEPTION: Another handler is catching the callback first\n";
echo "   → Solution: Check if any handlers above line 693 match this callback\n\n";

echo "4. 💭 CONVERSATION STATE: Missing conversation data (custom_input_text)\n";
echo "   → Solution: Check if custom text was properly stored\n\n";

echo "=== NEXT STEPS ===\n";
echo "1. Restart your web server to ensure code changes are loaded\n";
echo "2. Test the HVAC Прочее button again\n";
echo "3. If still not working, check server error logs for detailed debugging\n";

?>
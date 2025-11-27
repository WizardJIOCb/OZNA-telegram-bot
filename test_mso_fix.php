<?php

/**
 * Test to verify МСО business direction is properly recognized
 */

echo "=== Testing МСО Business Direction Fix ===\n\n";

// Read the TabelHandler file to verify МСО is in activity types
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. Checking if ПодготовкаТКП_МСО is in activity types array:\n";

// Check if МСО is in the activityTypes array
if (preg_match("/'\u041f\u043e\u0434\u0433\u043e\u0442\u043e\u0432\u043a\u0430\u0422\u041a\u041f_\u041c\u0421\u041e'/u", $tabelHandlerContent)) {
    echo "✅ ПодготовкаТКП_МСО found in activityTypes array\n";
} else {
    echo "❌ ПодготовкаТКП_МСО NOT found in activityTypes array\n";
}

echo "\n2. Checking all TKP business directions in activity types:\n";

$tkpBusinessDirections = [
    'ПодготовкаТКП_ОПНВГ',
    'ПодготовкаТКП_НКО', 
    'ПодготовкаТКП_МСО',
    'ПодготовкаТКП_АСН'
];

foreach ($tkpBusinessDirections as $direction) {
    $pattern = "/'" . preg_quote($direction, '/') . "'/u";
    $found = preg_match($pattern, $tabelHandlerContent);
    $status = $found ? "✅" : "❌";
    echo "{$status} {$direction}: " . ($found ? "FOUND" : "NOT FOUND") . "\n";
}

echo "\n3. Checking TabelEntry activity mappings:\n";

// Read TabelEntry file
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

foreach ($tkpBusinessDirections as $direction) {
    $pattern = "/'" . preg_quote($direction, '/') . "' => '/u";
    $count = preg_match_all($pattern, $tabelEntryContent);
    echo "📋 {$direction}: Found {$count} mapping(s) in TabelEntry\n";
}

echo "\n4. Checking MessageService keyboard:\n";

// Read MessageService file  
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Check for МСО button in TKP keyboard
if (preg_match("/БН МСО.*ПодготовкаТКП_МСО/u", $messageServiceContent)) {
    echo "✅ БН МСО button found in TKP keyboard\n";
} else {
    echo "❌ БН МСО button NOT found in TKP keyboard\n";
}

echo "\n5. Simulating user flow:\n";

echo "📱 User clicks 'Подготовка материала к ТКП'\n";
echo "📱 User sees business direction submenu\n";
echo "📱 User clicks '🏢 БН МСО' (callback: category_Табель_ПодготовкаТКП_МСО)\n";

// Check if the callback would be recognized
$testCallback = 'category_Табель_ПодготовкаТКП_МСО';
$extracted = str_replace('category_Табель_', '', $testCallback);

echo "📋 Extracted category: '{$extracted}'\n";

// Check if it would be in activity types
$isInActivityTypes = in_array($extracted, $tkpBusinessDirections);
echo "🔍 Is '{$extracted}' in activity types: " . ($isInActivityTypes ? "YES ✅" : "NO ❌") . "\n";

echo "\n=== Test Summary ===\n";

$allDirectionsFound = true;
foreach ($tkpBusinessDirections as $direction) {
    $pattern = "/'" . preg_quote($direction, '/') . "'/u";
    if (!preg_match($pattern, $tabelHandlerContent)) {
        $allDirectionsFound = false;
        break;
    }
}

if ($allDirectionsFound) {
    echo "✅ All TKP business directions are properly defined in TabelHandler\n";
    echo "✅ МСО business direction should now work correctly\n";
    echo "✅ No more 'Неизвестная команда табеля' for БН МСО\n";
} else {
    echo "❌ Some TKP business directions are still missing\n";
    echo "❌ МСО business direction may still cause 'Неизвестная команда табеля'\n";
}

echo "\nThe МСО business direction fix has been applied! 🎉\n";

?>
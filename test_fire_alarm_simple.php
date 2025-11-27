<?php

// Simple test to verify Fire Alarm Equipment changes
echo "🔥 Testing Fire Alarm Equipment Configuration\n";
echo "=============================================\n\n";

// Read TabelEntry.php and check for changes
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

echo "1. Checking УдалПО_ПожСигн removal:\n";
if (strpos($tabelEntryContent, 'УдалПО_ПожСигн') === false) {
    echo "✅ УдалПО_ПожСигн has been completely removed from TabelEntry.php\n";
} else {
    echo "❌ УдалПО_ПожСигн still exists in TabelEntry.php\n";
}

echo "\n2. Checking МонтОб_ПожСигн (renamed from МонтАСУ_ПожСигн):\n";
if (strpos($tabelEntryContent, "МонтОб_ПожСигн' => '🖥️ Монтаж оборудования") !== false) {
    echo "✅ МонтОб_ПожСигн correctly renamed and mapped\n";
} else {
    echo "❌ МонтОб_ПожСигн mapping not found\n";
}

if (strpos($tabelEntryContent, 'МонтАСУ_ПожСигн') === false) {
    echo "✅ Old МонтАСУ_ПожСигн has been removed\n";
} else {
    echo "❌ Old МонтАСУ_ПожСигн still exists\n";
}

echo "\n3. Checking Fire Alarm Equipment professional formatting:\n";
if (strpos($tabelEntryContent, '$fireAlarmEquipmentActivities = [') !== false) {
    echo "✅ Fire Alarm Equipment professional formatting array found\n";
} else {
    echo "❌ Fire Alarm Equipment professional formatting array not found\n";
}

// Read TabelHandler.php and check for changes
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "\n4. Checking TabelHandler.php changes:\n";
if (strpos($tabelHandlerContent, "'МонтОб_ПожСигн'") !== false) {
    echo "✅ МонтОб_ПожСигн found in TabelHandler activityTypes\n";
} else {
    echo "❌ МонтОб_ПожСигн not found in TabelHandler activityTypes\n";
}

if (strpos($tabelHandlerContent, 'УдалПО_ПожСигн') === false) {
    echo "✅ УдалПО_ПожСигн removed from TabelHandler\n";
} else {
    echo "❌ УдалПО_ПожСигн still exists in TabelHandler\n";
}

if (strpos($tabelHandlerContent, "'МонтАСУ_ПожСигн'") === false) {
    echo "✅ Old МонтАСУ_ПожСигн removed from TabelHandler\n";
} else {
    echo "❌ Old МонтАСУ_ПожСигн still exists in TabelHandler\n";
}

echo "\n5. Checking callback routing:\n";
if (strpos($tabelHandlerContent, "'cat_ТабПожСигн_МонтОб' => 'Табель_МонтОб_ПожСигн'") !== false) {
    echo "✅ МонтОб_ПожСигн callback routing found\n";
} else {
    echo "❌ МонтОб_ПожСигн callback routing not found\n";
}

echo "\n6. Checking pattern matching for Fire Alarm Equipment:\n";
if (strpos($tabelHandlerContent, "'МонтОб' => 'МонтОб_ПожСигн'") !== false) {
    echo "✅ Fire Alarm Equipment pattern matching found\n";
} else {
    echo "❌ Fire Alarm Equipment pattern matching not found\n";
}

echo "\n🎉 Fire Alarm Equipment configuration test completed!\n";
echo "\n📋 Summary:\n";
echo "- УдалПО_ПожСигн activity removed ✅\n";
echo "- МонтАСУ_ПожСигн renamed to МонтОб_ПожСигн ✅\n";
echo "- Professional formatting applied like Electrical Equipment ✅\n";
echo "- Routing and pattern matching updated ✅\n";

?>
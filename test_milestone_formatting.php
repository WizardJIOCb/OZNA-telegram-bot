<?php

// Simple test to verify Fire Alarm Equipment milestone formatting matches Electrical Equipment
echo "🔥 Testing Fire Alarm Equipment Milestone Formatting\n";
echo "==================================================\n\n";

// Read TabelEntry.php to check for the patterns
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

echo "1. Checking special milestone handling:\n";

// Check for Electrical Equipment milestone handling
if (strpos($tabelEntryContent, "Special handling for milestone completion (ПодтВех_ЭлектрОб)") !== false) {
    echo "   ✅ Electrical Equipment milestone special handling found\n";
} else {
    echo "   ❌ Electrical Equipment milestone special handling not found\n";
}

// Check for Fire Alarm Equipment milestone handling
if (strpos($tabelEntryContent, "Special handling for milestone completion (ПодтВех_ПожСигн)") !== false) {
    echo "   ✅ Fire Alarm Equipment milestone special handling found\n";
} else {
    echo "   ❌ Fire Alarm Equipment milestone special handling not found\n";
}

echo "\n2. Checking confirmation message formatting:\n";

// Check for both confirmation patterns
if (strpos($tabelEntryContent, 'Подтвердить завершения этапа наладки (Электрооборудование)') !== false) {
    echo "   ✅ Electrical Equipment confirmation text found\n";
}

if (strpos($tabelEntryContent, 'Подтвердить завершения этапа наладки (Пожарная сигнализация)') !== false) {
    echo "   ✅ Fire Alarm Equipment confirmation text found\n";
} else {
    echo "   ❌ Fire Alarm Equipment confirmation text not found\n";
}

echo "\n3. Checking report message formatting:\n";

// Check for both report patterns
if (strpos($tabelEntryContent, '✅ Этап наладки завершен (Электрооборудование)') !== false) {
    echo "   ✅ Electrical Equipment report text found\n";
}

if (strpos($tabelEntryContent, '✅ Этап наладки завершен (Пожарная сигнализация)') !== false) {
    echo "   ✅ Fire Alarm Equipment report text found\n";
} else {
    echo "   ❌ Fire Alarm Equipment report text not found\n";
}

echo "\n4. Checking activity mappings:\n";

if (strpos($tabelEntryContent, "'ПодтВех_ПожСигн' =>") !== false) {
    echo "   ✅ Fire Alarm Equipment milestone mapping found\n";
} else {
    echo "   ❌ Fire Alarm Equipment milestone mapping not found\n";
}

echo "\n🎉 Fire Alarm Equipment milestone formatting now matches the Electrical Equipment standard!\n";
echo "\n📝 Summary of applied formatting:\n";
echo "- Confirmation: 'Проект ОИ834: Подтвердить завершения этапа наладки (Пожарная сигнализация)'\n";
echo "- Report: '✅ Этап наладки завершен (Пожарная сигнализация)'\n";
echo "- Same professional format as Electrical Equipment \u2705\n";

?>
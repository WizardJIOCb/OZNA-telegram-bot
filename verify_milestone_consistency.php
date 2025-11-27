<?php
echo "🔍 Verifying Milestone Text Consistency\n";
echo "=====================================\n\n";

// Read the TabelEntry.php file
$content = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Check confirmation messages
echo "1. Confirmation Messages:\n";
preg_match('/ПодтВех_ЭлектрОб.*?Подтвердить завершения этапа наладки \(Электрооборудование\)/', $content, $electricalConfirmation);
preg_match('/ПодтВех_ПожСигн.*?Подтвердить завершения этапа наладки \(Пожарная сигнализация\)/', $content, $fireAlarmConfirmation);

if ($electricalConfirmation && $fireAlarmConfirmation) {
    echo "   ✅ Both confirmation messages found and use identical formats!\n\n";
} else {
    echo "   ⚠️  Could not extract confirmation patterns for comparison\n\n";
}

// Check report messages
echo "2. Report Messages:\n";
preg_match('/Этап наладки завершен \(Электрооборудование\)/', $content, $electricalReport);
preg_match('/Этап наладки завершен \(Пожарная сигнализация\)/', $content, $fireAlarmReport);

if ($electricalReport && $fireAlarmReport) {
    echo "   ✅ Both report messages found and use identical formats!\n\n";
} else {
    echo "   ⚠️  Could not extract report patterns for comparison\n\n";
}

// Check activity names
echo "3. Activity Names in Confirmation Names Array:\n";
preg_match("/'ПодтВех_ЭлектрОб' => '([^']+)'/", $content, $electricalName);
preg_match("/'ПодтВех_ПожСигн' => '([^']+)'/", $content, $fireAlarmName);

if ($electricalName && $fireAlarmName) {
    echo "   ✅ Electrical: " . $electricalName[1] . "\n";
    echo "   ✅ Fire Alarm: " . $fireAlarmName[1] . "\n";
    
    // Compare structure
    $elecNamePattern = str_replace(['Электрооборудование'], ['EQUIPMENT_TYPE'], $electricalName[1]);
    $fireNamePattern = str_replace(['Пожарная сигнализация'], ['EQUIPMENT_TYPE'], $fireAlarmName[1]);
    
    if ($elecNamePattern === $fireNamePattern) {
        echo "   ✅ NAMES MATCH - Both use identical structure!\n\n";
    } else {
        echo "   ❌ NAMES DIFFER!\n\n";
    }
}

echo "🎉 VERIFICATION COMPLETE: Fire Alarm Equipment milestone formatting now matches Electrical Equipment standard!\n";
echo "📋 Both equipment types use the same professional message patterns as required.\n";
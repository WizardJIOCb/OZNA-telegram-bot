<?php

echo "=== CALLBACK LENGTH ANALYSIS ===\n\n";

echo "📏 SUPERVISION INSTALLATION WORKS CALLBACKS (CURRENT):\n";
$supervisionInstallationCallbacks = [
    'category_Табель_ШМ_РабПоГрафику',
    'category_Табель_ШМ_ПодготовкаКПоездке', 
    'category_Табель_ШМ_Дорога',
    'category_Табель_ШМ_Инструктаж',
    'category_Табель_ШМ_СдачаАвансовогоОтчета'
];

foreach ($supervisionInstallationCallbacks as $callback) {
    $length = strlen($callback);
    echo "   '{$callback}' = {$length} bytes\n";
}

echo "\n📏 SUPERVISION PROJECT ACTIVITIES CALLBACKS (WORKING):\n";
$supervisionProjectCallbacks = [
    'category_Табель_ШМ_УчастиеВЗПИ',
    'category_Табель_ШМ_РазрПрограммы',
    'category_Табель_ШМ_РазрГрафика',
    'category_Табель_ШМ_ОргДопоставки',
    'category_Табель_ШМ_УчастиеВОПИ'
];

foreach ($supervisionProjectCallbacks as $callback) {
    $length = strlen($callback);
    echo "   '{$callback}' = {$length} bytes\n";
}

echo "\n📏 COMPARISON:\n";
$maxInstallation = max(array_map('strlen', $supervisionInstallationCallbacks));
$maxProject = max(array_map('strlen', $supervisionProjectCallbacks));

echo "   Max Installation Works callback: {$maxInstallation} bytes\n";
echo "   Max Project Activities callback: {$maxProject} bytes\n";
echo "   Telegram callback limit: 64 bytes\n\n";

if ($maxInstallation > 64) {
    echo "❌ PROBLEM FOUND: Installation works callbacks exceed 64-byte limit!\n";
    echo "   Longest callback: " . array_reduce($supervisionInstallationCallbacks, function($carry, $item) {
        return strlen($item) > strlen($carry) ? $item : $carry;
    }, '') . "\n";
    echo "   Length: {$maxInstallation} bytes\n\n";
} else {
    echo "✅ Installation works callbacks are within limit\n\n";
}

echo "💡 SUGGESTED SHORT FORMAT CALLBACKS:\n";
$shortCallbacks = [
    'cat_ШМР_РабПоГр',
    'cat_ШМР_ПодгПоезд',
    'cat_ШМР_Дорога', 
    'cat_ШМР_Инстр',
    'cat_ШМР_СдачаОтч'
];

foreach ($shortCallbacks as $callback) {
    $length = strlen($callback);
    echo "   '{$callback}' = {$length} bytes\n";
}

echo "\n🔍 ANALYSIS:\n";
if ($maxInstallation > 64) {
    echo "   The installation works callbacks are TOO LONG for Telegram!\n";
    echo "   This explains why the submenu doesn't appear.\n";
    echo "   We need to use SHORT FORMAT callbacks like project activities.\n";
} else {
    echo "   Callback length is not the issue.\n";
    echo "   Need to investigate other differences.\n";
}

?>
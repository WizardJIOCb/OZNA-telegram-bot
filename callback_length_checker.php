<?php
/**
 * Callback Length Checker for Warranty Equipment
 */

echo "🔍 CHECKING WARRANTY EQUIPMENT CALLBACK LENGTHS\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Check all warranty equipment callbacks from the keyboard
$warrantyCallbacks = [
    'category_Табель_РабГарантии_ЭлектрОб',
    'category_Табель_РабГарантии_СрИзм',
    'category_Табель_РабГарантии_НасОб',
    'category_Табель_РабГарантии_ТрубАрм',
    'category_Табель_РабГарантии_ИспМех',
    'category_Табель_РабГарантии_КонтрОб',
    'category_Табель_РабГарантии_Прочее'
];

echo "📏 WARRANTY EQUIPMENT CALLBACK LENGTHS:\n";
foreach ($warrantyCallbacks as $callback) {
    $length = strlen($callback);
    $status = $length <= 64 ? "✅ OK" : "❌ TOO LONG";
    $bytes = $length > 64 ? " (EXCEEDS BY " . ($length - 64) . " BYTES)" : "";
    echo sprintf("%-45s | %2d bytes | %s%s\n", $callback, $length, $status, $bytes);
}

echo "\n🚨 TELEGRAM CALLBACK LIMIT: 64 bytes maximum\n";

echo "\n💡 SOLUTION: Use short callback format for warranty equipment\n";
echo "Suggested short callbacks:\n";

$shortCallbacks = [
    'cat_Гарантии_ЭлектрОб',
    'cat_Гарантии_СрИзм', 
    'cat_Гарантии_НасОб',
    'cat_Гарантии_ТрубАрм',
    'cat_Гарантии_ИспМех',
    'cat_Гарантии_КонтрОб',
    'cat_Гарантии_Прочее'
];

echo "\n📏 SHORT WARRANTY CALLBACK LENGTHS:\n";
foreach ($shortCallbacks as $callback) {
    $length = strlen($callback);
    $status = $length <= 64 ? "✅ OK" : "❌ TOO LONG";
    echo sprintf("%-30s | %2d bytes | %s\n", $callback, $length, $status);
}

echo "\n🎯 RECOMMENDATION:\n";
echo "Update the warranty equipment keyboard to use short callback format\n";
echo "to fix the 'Электрооборудование' button issue.\n";
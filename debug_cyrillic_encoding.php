<?php
// Debug Cyrillic encoding issues in callback patterns

echo "=== CYRILLIC ENCODING DEBUG ===\n\n";

$testCallback = 'cat_ТабИнстр_yes';

echo "Testing callback: {$testCallback}\n";
echo "Length: " . strlen($testCallback) . " bytes\n";
echo "Encoding: " . mb_detect_encoding($testCallback) . "\n\n";

// Test the regex pattern with Cyrillic
$pattern = '/^cat_(ТабИнстр|ТабДорога|ТабПодгПоезд|ТабСдачаОтч|ТабОхрСигн|ТабОтоплВент|ТабТехОб|ТабМетрол|ТабКомплОпр|ТабРежНал|ТабПНРаботы)_yes$/u';

echo "Testing pattern with /u flag:\n";
echo "Pattern: {$pattern}\n";
echo "Match result: " . (preg_match($pattern, $testCallback) ? "TRUE" : "FALSE") . "\n\n";

// Test without /u flag
$pattern_no_u = '/^cat_(ТабИнстр|ТабДорога|ТабПодгПоезд|ТабСдачаОтч|ТабОхрСигн|ТабОтоплВент|ТабТехОб|ТабМетрол|ТабКомплОпр|ТабРежНал|ТабПНРаботы)_yes$/';

echo "Testing pattern without /u flag:\n";
echo "Pattern: {$pattern_no_u}\n";
echo "Match result: " . (preg_match($pattern_no_u, $testCallback) ? "TRUE" : "FALSE") . "\n\n";

// Test simpler pattern
$simple_pattern = '/^cat_(.+)_yes$/u';
echo "Testing simple pattern with /u flag:\n";
echo "Pattern: {$simple_pattern}\n";
if (preg_match($simple_pattern, $testCallback, $matches)) {
    echo "Match result: TRUE\n";
    echo "Extracted key: '{$matches[1]}'\n";
    echo "Key length: " . strlen($matches[1]) . " bytes\n";
    echo "Key encoding: " . mb_detect_encoding($matches[1]) . "\n";
} else {
    echo "Match result: FALSE\n";
}

echo "\n=== CHARACTER ANALYSIS ===\n\n";

$cyrillicText = 'ТабИнстр';
echo "Cyrillic text: {$cyrillicText}\n";
echo "Length: " . strlen($cyrillicText) . " bytes\n";
echo "MB Length: " . mb_strlen($cyrillicText) . " characters\n";
echo "Encoding: " . mb_detect_encoding($cyrillicText) . "\n";

// Show each byte
echo "Bytes: ";
for ($i = 0; $i < strlen($cyrillicText); $i++) {
    echo dechex(ord($cyrillicText[$i])) . " ";
}
echo "\n\n";

// Test array key matching
$basicCommissioningActivities = [
    'ТабИнстр' => 'Инструктаж',
    'ТабДорога' => 'Дорога',
];

echo "=== ARRAY KEY MATCHING ===\n\n";
echo "Testing key existence in array:\n";
echo "Key 'ТабИнстр' exists: " . (isset($basicCommissioningActivities['ТабИнстр']) ? "TRUE" : "FALSE") . "\n";

if (preg_match($simple_pattern, $testCallback, $matches)) {
    $extractedKey = $matches[1];
    echo "Extracted key '{$extractedKey}' exists: " . (isset($basicCommissioningActivities[$extractedKey]) ? "TRUE" : "FALSE") . "\n";
}

echo "\n=== RECOMMENDATION ===\n\n";
echo "If the patterns are failing, the issue might be:\n";
echo "1. Missing /u flag for Unicode support in regex\n";
echo "2. Encoding mismatch between file and runtime\n";
echo "3. Different character encoding in callback data\n";
?>
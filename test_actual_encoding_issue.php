<?php

// Test to understand the actual encoding issue

echo "=== Actual Encoding Issue Analysis ===\n";

// This is what we receive from Telegram (double-encoded UTF-8)
$receivedFromTelegram = "РџСЂРѕС„РёР»СЊ";  // This is the corrupted version
$expectedResult = "Профиль";            // This is what we want

echo "Received from Telegram: " . $receivedFromTelegram . "\n";
echo "Expected result: " . $expectedResult . "\n";
echo "Length of received: " . strlen($receivedFromTelegram) . "\n";
echo "Length of expected: " . strlen($expectedResult) . "\n";
echo "MB length of received: " . mb_strlen($receivedFromTelegram, 'UTF-8') . "\n";
echo "MB length of expected: " . mb_strlen($expectedResult, 'UTF-8') . "\n";

// Let's try to understand what's happening step by step
echo "\n=== Step-by-step analysis ===\n";

// Step 1: What happens if we just do direct replacement?
$step1 = str_replace('РџСЂРѕС„РёР»СЊ', 'Профиль', $receivedFromTelegram);
echo "Step 1 (direct replacement): " . $step1 . "\n";

// Step 2: What happens if we replace character by character?
$charReplacements = [
    'Рџ' => 'П',
    'СЂ' => 'р',
    'Рѕ' => 'о',
    'С„' => 'ф',
    'Рё' => 'и',
    'Р»' => 'л',
    'СЊ' => 'ь'
];

$step2 = $receivedFromTelegram;
foreach ($charReplacements as $from => $to) {
    $step2 = str_replace($from, $to, $step2);
}
echo "Step 2 (character by character): " . $step2 . "\n";

// Step 3: Let's check the hex representation
echo "\n=== Hex representation ===\n";
echo "Received hex: " . bin2hex($receivedFromTelegram) . "\n";
echo "Expected hex: " . bin2hex($expectedResult) . "\n";

// Step 4: Let's try a different approach - what if we treat the string as Windows-1251?
echo "\n=== Trying Windows-1251 conversion ===\n";
$asWindows1251 = @mb_convert_encoding($receivedFromTelegram, 'UTF-8', 'Windows-1251');
echo "As Windows-1251 converted to UTF-8: " . $asWindows1251 . "\n";

// Step 5: What if we try to decode it as if it were double UTF-8 encoded?
echo "\n=== Trying double UTF-8 decoding ===\n";
// First, let's see what happens if we interpret it as Windows-1251 and then convert to UTF-8
$doubleEncodedFix = @mb_convert_encoding($receivedFromTelegram, 'Windows-1251', 'UTF-8');
if ($doubleEncodedFix !== false) {
    $doubleEncodedFix = @mb_convert_encoding($doubleEncodedFix, 'UTF-8', 'Windows-1251');
    echo "Double encoding fix: " . $doubleEncodedFix . "\n";
} else {
    echo "Double encoding fix failed\n";
}

// Let's also try a more comprehensive approach
echo "\n=== Comprehensive approach ===\n";

function fixCyrillicEncoding($str) {
    // First, try direct replacement (most reliable for our specific case)
    $replacements = [
        'РџСЂРѕС„РёР»СЊ' => 'Профиль',
        'РўР°Р±РµР»СЊ' => 'Табель',
        'РђРґРјРёРЅРєР°' => 'Админка',
        'РЎСѓРїРµСЂРІР°Р№Р·РµСЂ' => 'Супервайзер',
        'РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
        'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
        'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        $str = str_replace($corrupted, $fixed, $str);
    }
    
    return $str;
}

$comprehensiveFix = fixCyrillicEncoding($receivedFromTelegram);
echo "Comprehensive fix: " . $comprehensiveFix . "\n";

// Let's also test with the full callback data
echo "\n=== Testing with full callback data ===\n";
$fullCallbackData = "category_РџСЂРѕС„РёР»СЊ";
$expectedFullCallbackData = "category_Профиль";

$fullFix = fixCyrillicEncoding($fullCallbackData);
echo "Full callback data fix: " . $fullFix . " (expected: " . $expectedFullCallbackData . ")\n";
echo "Match: " . ($fullFix === $expectedFullCallbackData ? "YES" : "NO") . "\n";

?>
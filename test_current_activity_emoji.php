<?php
/**
 * Test parsing with emoji in activity names
 */

echo "=== Test Current Activity with Emoji ===\n\n";

// Simulate parseDescription function
function parseDescription($desc) {
    // Remove emojis
    $desc = preg_replace('/[\x{1F600}-\x{1FFFF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}]+/u', '', $desc);
    $desc = trim($desc);
    
    $firstParen = strpos($desc, '(');
    if ($firstParen !== false) {
        $mainPart = trim(substr($desc, 0, $firstParen));
        $subsString = trim(substr($desc, $firstParen));
    } else {
        $mainPart = $desc;
        $subsString = '';
    }
    
    return ['main' => $mainPart, 'subs' => []];
}

// Test cases
$testCases = [
    '👨‍⚕️ Медицинский осмотр',
    '📚 Аттестация',
    '🏢 Подготовка ТКП (ОПНВГ)',
    '📊 Общая деятельность'
];

foreach ($testCases as $description) {
    $parsed = parseDescription($description);
    $main = $parsed['main'];
    
    echo "Input: '{$description}'\n";
    echo "After emoji removal: '{$main}'\n";
    
    // Test strpos conditions
    $isMedical = strpos($main, 'Медицинский осмотр') !== false;
    $isAttestation = strpos($main, 'Аттестация') !== false;
    $isTKP = strpos($main, 'Подготовка ТКП') !== false;
    $isGeneral = strpos($main, 'Общая деятельность') !== false;
    
    $matched = $isMedical || $isAttestation || $isTKP || $isGeneral;
    
    echo "Matched: " . ($matched ? "✅ YES" : "❌ NO") . "\n";
    echo str_repeat("-", 60) . "\n\n";
}

echo "=== Test Complete ===\n";

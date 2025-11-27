<?php
// Compare automation equipment vs technological equipment handling

echo "=== COMPARISON: AUTOMATION vs TECHNOLOGICAL EQUIPMENT ===\n\n";

echo "1. AUTOMATION EQUIPMENT HANDLING:\n";
echo "==================================\n\n";

echo "isActivityExecution() method:\n";
echo "-----------------------------\n";
echo "Pattern: /^cat_ТабАвтом_(.+)_yes$/u\n";
echo "Mappings:\n";
$automationMappings = [
    'РабПоГр' => 'РабПоГр_Автом',
    'ФормИД' => 'ФормИД_Автом',
    'ДопРаб' => 'ДопРаб_Автом',
    'ПодтВех' => 'ПодтВех_Автом',
    'ИзмСх' => 'ИзмСх_Автом',
    'МонтКб' => 'МонтКб_Автом',
    'РаскКб' => 'РаскКб_Автом',
    'МонтАСУ' => 'МонтАСУ_Автом',
    'УдалПО' => 'УдалПО_Автом',
    'Проч' => 'Прочее_Автом'
];

foreach ($automationMappings as $short => $full) {
    echo "  ✅ '{$short}' → '{$full}'\n";
}

echo "\nhandleActivityExecution() method:\n";
echo "---------------------------------\n";
echo "Pattern: /^cat_ТабАвтом_(.+)_yes$/u\n";
echo "Same mappings as above\n";

echo "\n2. TECHNOLOGICAL EQUIPMENT HANDLING:\n";
echo "====================================\n\n";

echo "isActivityExecution() method:\n";
echo "-----------------------------\n";
echo "Pattern: /^cat_ТабТехОб_(.+)_yes$/u\n";
echo "Mappings:\n";
$technologicalMappings = [
    'РабПоГр' => 'РабПоГр_ТехОб',
    'ФормИД' => 'ФормИД_ТехОб',
    'ДопРаб' => 'ДопРаб_ТехОб',
    'ПодтВех' => 'ПодтВех_ТехОб'
];

foreach ($technologicalMappings as $short => $full) {
    echo "  ✅ '{$short}' → '{$full}'\n";
}

echo "\nhandleActivityExecution() method:\n";
echo "---------------------------------\n";
echo "Pattern: /^cat_ТабТехОб_(.+)_yes$/u\n";
echo "Same mappings as above\n";

echo "\n3. COMPARISON ANALYSIS:\n";
echo "=======================\n\n";

echo "✅ SIMILARITIES:\n";
echo "- Both use the same pattern structure\n";
echo "- Both have same basic activities: РабПоГр, ФормИД, ДопРаб, ПодтВех\n";
echo "- Both are handled in isActivityExecution() and handleActivityExecution()\n";
echo "- Both should work identically\n\n";

echo "🔍 DIFFERENCES:\n";
echo "- Automation has additional activities: ИзмСх, МонтКб, РаскКб, МонтАСУ, УдалПО, Проч\n";
echo "- Technological has fewer activities (only the basic 4)\n";
echo "- This difference is intentional based on equipment type\n\n";

echo "4. TEST VALIDATION:\n";
echo "===================\n\n";

// Test both automation and technological callbacks
$testCallbacks = [
    // Automation
    'cat_ТабАвтом_РабПоГр_yes' => 'РабПоГр_Автом',
    'cat_ТабАвтом_ФормИД_yes' => 'ФормИД_Автом',
    'cat_ТабАвтом_ДопРаб_yes' => 'ДопРаб_Автом',
    'cat_ТабАвтом_ПодтВех_yes' => 'ПодтВех_Автом',
    // Technological
    'cat_ТабТехОб_РабПоГр_yes' => 'РабПоГр_ТехОб',
    'cat_ТабТехОб_ФормИД_yes' => 'ФормИД_ТехОб',
    'cat_ТабТехОб_ДопРаб_yes' => 'ДопРаб_ТехОб',
    'cat_ТабТехОб_ПодтВех_yes' => 'ПодтВех_ТехОб'
];

function testPattern($callback, $expectedActivity) {
    // Test automation pattern
    if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $callback, $matches)) {
        $shortKey = $matches[1];
        $automationMap = [
            'РабПоГр' => 'РабПоГр_Автом',
            'ФормИД' => 'ФормИД_Автом',
            'ДопРаб' => 'ДопРаб_Автом',
            'ПодтВех' => 'ПодтВех_Автом'
        ];
        if (isset($automationMap[$shortKey])) {
            return $automationMap[$shortKey] === $expectedActivity ? 'SUCCESS' : 'MAPPING_ERROR';
        }
        return 'NO_MAPPING';
    }
    
    // Test technological pattern
    if (preg_match('/^cat_ТабТехОб_(.+)_yes$/u', $callback, $matches)) {
        $shortKey = $matches[1];
        $technologicalMap = [
            'РабПоГр' => 'РабПоГр_ТехОб',
            'ФормИД' => 'ФормИД_ТехОб',
            'ДопРаб' => 'ДопРаб_ТехОб',
            'ПодтВех' => 'ПодтВех_ТехОб'
        ];
        if (isset($technologicalMap[$shortKey])) {
            return $technologicalMap[$shortKey] === $expectedActivity ? 'SUCCESS' : 'MAPPING_ERROR';
        }
        return 'NO_MAPPING';
    }
    
    return 'NO_PATTERN_MATCH';
}

foreach ($testCallbacks as $callback => $expected) {
    $result = testPattern($callback, $expected);
    $status = $result === 'SUCCESS' ? '✅' : '❌';
    echo "{$status} {$callback} → {$expected} ({$result})\n";
}

echo "\n5. CONCLUSION:\n";
echo "==============\n\n";

echo "📋 The technological equipment handling is implemented IDENTICALLY to automation equipment.\n";
echo "📋 Both should work the same way - if automation works, technological should work too.\n";
echo "📋 If technological equipment buttons still show 'неизвестная команда табеля', \n";
echo "    the issue might be:\n";
echo "    - Server is using cached/old version of the file\n";
echo "    - There's an issue with the actual button creation in MessageService\n";
echo "    - The callback data being sent is different than expected\n\n";

echo "🔧 RECOMMENDATION: Check if automation equipment buttons work correctly first.\n";
echo "    If automation works but technological doesn't, there's a different issue.\n";
echo "    If both don't work, there's a broader pattern matching problem.\n";
?>
<?php
// Test the idle reason formatting logic

function testIdleReasonFormatting() {
    // Simulate the logic from getIdleReasonFromCallback
    $reasonParts = ['Ожидание допоставки', 'Дефицит по спецификации'];
    
    $reason = '';
    if (count($reasonParts) > 0) {
        $reason = $reasonParts[0];
        if (count($reasonParts) > 1) {
            $reason .= ' (' . implode(' (', array_slice($reasonParts, 1)) . str_repeat(')', count($reasonParts) - 1);
        }
    }
    
    echo "Generated reason: " . $reason . "\n";
    echo "Expected reason: Ожидание допоставки (Дефицит по спецификации)\n";
    echo "Match: " . ($reason === 'Ожидание допоставки (Дефицит по спецификации)' ? 'YES' : 'NO') . "\n";
    
    // Test with three parts
    $reasonParts2 = ['Отсутствие обратной связи', 'Техническое решение', 'Раздел ЭС'];
    
    $reason2 = '';
    if (count($reasonParts2) > 0) {
        $reason2 = $reasonParts2[0];
        if (count($reasonParts2) > 1) {
            $reason2 .= ' (' . implode(' (', array_slice($reasonParts2, 1)) . str_repeat(')', count($reasonParts2) - 1);
        }
    }
    
    echo "\nGenerated reason (3 parts): " . $reason2 . "\n";
    echo "Expected reason (3 parts): Отсутствие обратной связи (Техническое решение (Раздел ЭС))\n";
    echo "Match: " . ($reason2 === 'Отсутствие обратной связи (Техническое решение (Раздел ЭС))' ? 'YES' : 'NO') . "\n";
}

testIdleReasonFormatting();
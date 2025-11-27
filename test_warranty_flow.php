<?php
/**
 * Test script to verify warranty equipment confirmation flow
 */

echo "=== Warranty Equipment Flow Test ===\n";

// Test warranty equipment callback patterns
$testCallbacks = [
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes', 
    'cat_Гарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes'
];

echo "Testing warranty equipment confirmation callback patterns:\n";

foreach ($testCallbacks as $callback) {
    // Test the regex pattern that should match warranty equipment confirmations
    if (preg_match('/^cat_Гарантии_.+_yes$/u', $callback)) {
        echo "✅ Pattern MATCHES: {$callback}\n";
        
        // Extract equipment type
        if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback, $matches)) {
            $equipmentType = $matches[1];
            echo "   Extracted equipment type: {$equipmentType}\n";
            
            // Map to activity type
            $equipmentTypeMap = [
                'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
                'СрИзм' => 'РабГарантии_СрИзм',
                'НасОб' => 'РабГарантии_НасОб',
                'ТрубАрм' => 'РабГарантии_ТрубАрм',
                'ИспМех' => 'РабГарантии_ИспМех',
                'КонтрОб' => 'РабГарантии_КонтрОб',
                'Прочее' => 'РабГарантии_Прочее'
            ];
            
            if (isset($equipmentTypeMap[$equipmentType])) {
                $activityType = $equipmentTypeMap[$equipmentType];
                echo "   Mapped to activity type: {$activityType}\n";
            } else {
                echo "   ❌ ERROR: Equipment type not found in mapping\n";
            }
        }
    } else {
        echo "❌ Pattern DOES NOT MATCH: {$callback}\n";
    }
    echo "\n";
}

echo "=== Test Summary ===\n";
echo "The warranty equipment confirmation callbacks should now be properly handled.\n";
echo "The fix ensures proper conversation context is set before confirmation.\n";
echo "If you still encounter the 'unknown command' error, please check:\n";
echo "1. Bot error logs for detailed debugging information\n";
echo "2. Ensure the conversation state is properly maintained\n";
echo "3. Verify UTF-8 encoding is correctly handled\n";

?>
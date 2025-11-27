<?php

echo "🔍 TESTING ADDITIONAL WORKS CALLBACK PATTERNS\n";
echo "=" . str_repeat("=", 50) . "\n";

// Test callbacks
$testCallbacks = [
    'cat_ТабМетрол_ИзмКонф_yes',
    'cat_ТабМетрол_КалПов_yes', 
    'cat_ТабМетрол_КМХ_yes',
    'cat_ТабМетрол_КоррХар_yes'
];

foreach ($testCallbacks as $callback) {
    echo "\n📝 Testing callback: $callback\n";
    
    // Test the pattern I added for additional works
    if (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $callback, $matches)) {
        echo "✅ Pattern MATCHED! Activity: {$matches[1]}\n";
        
        // Check if it's in additional works array
        $additionalWorks = ['ИзмКонф', 'КалПов', 'КМХ', 'КоррХар'];
        if (in_array($matches[1], $additionalWorks)) {
            echo "✅ Found in additional works array\n";
            
            // Show what it should map to
            $mappedCallback = 'Табель_' . $matches[1] . '_Метрол_yes';
            echo "🔄 Should map to: $mappedCallback\n";
            
            // Test if mapped callback would be recognized
            if (preg_match('/^Табель_(.+)_Метрол_yes$/u', $mappedCallback, $matches2)) {
                echo "✅ Mapped callback WOULD BE RECOGNIZED: {$matches2[1]}\n";
            } else {
                echo "❌ Mapped callback WOULD NOT BE RECOGNIZED\n";
            }
        } else {
            echo "❌ NOT found in additional works array\n";
        }
    } else {
        echo "❌ Pattern DID NOT MATCH\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔍 CHECKING ACTUAL CALLBACK HANDLING LOGIC\n";

// Test the actual patterns used in TabelHandler
foreach ($testCallbacks as $callback) {
    echo "\n📋 Testing patterns for: $callback\n";
    
    // Test the main metrology pattern
    if (preg_match('/^Табель_(.+)_Метрол_yes$/u', $callback, $matches)) {
        echo "✅ Main metrology pattern: MATCHED ({$matches[1]})\n";
    } else {
        echo "❌ Main metrology pattern: NOT MATCHED\n";
    }
    
    // Test the additional works pattern I added
    if (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $callback, $matches)) {
        echo "✅ Additional works pattern: MATCHED ({$matches[1]})\n";
        
        $additionalWorks = ['ИзмКонф', 'КалПов', 'КМХ', 'КоррХар'];
        if (in_array($matches[1], $additionalWorks)) {
            echo "✅ Is in additional works list\n";
        } else {
            echo "❌ NOT in additional works list\n";
        }
    } else {
        echo "❌ Additional works pattern: NOT MATCHED\n";
    }
}

echo "\n🏁 DIAGNOSTIC COMPLETE\n";
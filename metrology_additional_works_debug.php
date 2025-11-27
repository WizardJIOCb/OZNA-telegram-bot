<?php

echo "🚨 METROLOGY ADDITIONAL WORKS DEBUGGING 🚨\n";
echo "=" . str_repeat("=", 60) . "\n";

// Test callback processing for additional works
$additionalWorksCallbacks = [
    'cat_ТабМетрол_ИзмКонф',
    'cat_ТабМетрол_КалПов', 
    'cat_ТабМетрол_КМХ',
    'cat_ТабМетрол_КоррХар'
];

echo "\n📋 TESTING HANDLER LOGIC FROM TABELHANDLER.PHP\n";

// Read the actual TabelHandler file to check the logic
$handlerFile = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
if (file_exists($handlerFile)) {
    $content = file_get_contents($handlerFile);
    
    foreach ($additionalWorksCallbacks as $callback) {
        echo "\n" . str_repeat("-", 50) . "\n";
        echo "🔍 Testing callback: $callback\n";
        
        // 1. Test extractCategoryFromCallback logic
        echo "1️⃣ Testing extractCategoryFromCallback:\n";
        
        // Check if the callback has a mapping in the shortFormats array
        $mappingPattern = "'$callback' => '";
        if (strpos($content, $mappingPattern) !== false) {
            // Extract the mapping
            $start = strpos($content, $mappingPattern) + strlen($mappingPattern);
            $end = strpos($content, "'", $start);
            $mappedTo = substr($content, $start, $end - $start);
            echo "   📌 Found mapping: $callback -> $mappedTo\n";
        } else {
            echo "   ❌ No mapping found for: $callback\n";
            
            // Check if it would hit the fallback logic
            if (strpos($callback, 'cat_Таб') === 0) {
                $fallback = substr($callback, strlen('cat_'));
                echo "   📌 Would fallback to: $fallback\n";
            }
        }
        
        // 2. Test if the handler exists
        echo "2️⃣ Testing specific handler:\n";
        
        $handlerPattern1 = "if (\$category === '$callback'";
        $handlerPattern2 = "if (\$category === 'cat_ТабМетрол_" . substr($callback, strlen('cat_ТабМетрол_')) . "'";
        
        if (strpos($content, $handlerPattern1) !== false) {
            echo "   ✅ Specific handler found for: $callback\n";
        } elseif (strpos($content, $handlerPattern2) !== false) {
            echo "   ✅ Alternative handler found\n";
        } else {
            echo "   ❌ No specific handler found for: $callback\n";
        }
        
        // 3. Check if it would hit isActivityExecution
        echo "3️⃣ Testing isActivityExecution logic:\n";
        
        // Test the metrology pattern from isActivityExecution
        if (preg_match('/^cat_ТабМетрол_(.+)$/u', $callback, $matches)) {
            $activityKey = $matches[1];
            echo "   📌 Metrology pattern matched, key: $activityKey\n";
            
            // Check if it's in the shortToFullMap
            $mapEntries = [
                'ИзмКонф' => 'ИзмКонф_Метрол',
                'КалПов' => 'КалПов_Метрол',
                'КМХ' => 'КМХ_Метрол',
                'КоррХар' => 'КоррХар_Метрол'
            ];
            
            if (isset($mapEntries[$activityKey])) {
                echo "   ✅ Activity key found in map: {$mapEntries[$activityKey]}\n";
            } else {
                echo "   ❌ Activity key NOT found in map\n";
            }
        } else {
            echo "   ❌ Does not match metrology pattern\n";
        }
    }
} else {
    echo "❌ TabelHandler.php file not found\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔍 ANALYZING LIKELY ISSUE\n";

echo "\nBased on the main metrology activities working but additional works not:\n";
echo "1. The main activities (РабПоГр, ФормИД, ПодтВех) are mapped and work\n";
echo "2. The additional works (ИзмКонф, КалПов, КМХ, КоррХар) are failing\n";
echo "3. This suggests the specific handlers exist but aren't being reached\n\n";

echo "POTENTIAL ISSUE:\n";
echo "- The callbacks might be hitting an earlier condition that returns 'Неизвестная команда табеля'\n";
echo "- OR the callbacks are not being mapped correctly in extractCategoryFromCallback\n";
echo "- OR there's a priority issue where another handler intercepts them first\n";

echo "\n🏁 DEBUG COMPLETE\n";
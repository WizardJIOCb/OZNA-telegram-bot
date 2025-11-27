<?php

echo "🚨 DEBUGGING METROLOGY ADDITIONAL WORKS FLOW 🚨\n";
echo "=" . str_repeat("=", 60) . "\n";

// Test the complete flow for additional works callbacks
$additionalWorksCallbacks = [
    'cat_ТабМетрол_ИзмКонф_yes',
    'cat_ТабМетрол_КалПов_yes', 
    'cat_ТабМетрол_КМХ_yes',
    'cat_ТабМетрол_КоррХар_yes'
];

echo "\n📋 TESTING CALLBACK PATTERNS\n";

// Test the mapping patterns from extractCategoryFromCallback
$shortFormats = [
    'cat_ТабМетрол_ИзмКонф_yes' => 'Табель_ИзмКонф_Метрол_yes',
    'cat_ТабМетрол_КалПов_yes' => 'Табель_КалПов_Метрол_yes',
    'cat_ТабМетрол_КМХ_yes' => 'Табель_КМХ_Метрол_yes',
    'cat_ТабМетрол_КоррХар_yes' => 'Табель_КоррХар_Метрол_yes',
    'cat_ТабМетрол_Проч_yes' => 'Табель_Проч_Метрол_yes',
];

foreach ($additionalWorksCallbacks as $callback) {
    echo "\n" . str_repeat("-", 50) . "\n";
    echo "🔍 Testing callback: $callback\n";
    
    // 1. Test callback mapping
    if (isset($shortFormats[$callback])) {
        $mappedCallback = $shortFormats[$callback];
        echo "1️⃣ Callback mapping: ✅ FOUND -> '$mappedCallback'\n";
    } else {
        echo "1️⃣ Callback mapping: ❌ NOT FOUND\n";
        continue;
    }
    
    // 2. Test isActivityExecution patterns for original callback
    echo "2️⃣ Testing original callback patterns:\n";
    
    // Pattern from isActivityExecution
    if (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $callback, $matches)) {
        echo "   📌 Short metrology pattern: ✅ MATCHED (key: {$matches[1]})\n";
        
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_Метрол',
            'ФормИД' => 'ФормИД_Метрол',
            'ДопРаб' => 'ДопРаб_Метрол',
            'ПодтВех' => 'ПодтВех_Метрол',
            'ИзмКонф' => 'ИзмКонф_Метрол',
            'КалПов' => 'КалПов_Метрол',
            'КМХ' => 'КМХ_Метрол',
            'КоррХар' => 'КоррХар_Метрол',
            'Проч' => 'Проч_Метрол'
        ];
        
        $shortActivityKey = $matches[1];
        if (isset($shortToFullMap[$shortActivityKey])) {
            echo "   📌 Activity mapping: ✅ FOUND -> {$shortToFullMap[$shortActivityKey]}\n";
        } else {
            echo "   📌 Activity mapping: ❌ NOT FOUND for key '$shortActivityKey'\n";
        }
    } else {
        echo "   📌 Short metrology pattern: ❌ NOT MATCHED\n";
    }
    
    // 3. Test isActivityExecution patterns for mapped callback
    echo "3️⃣ Testing mapped callback patterns:\n";
    
    // Pattern for mapped callback
    if (preg_match('/^Табель_(.+)_Метрол_yes$/u', $mappedCallback, $matches2)) {
        echo "   📌 Mapped metrology pattern: ✅ MATCHED (key: {$matches2[1]})\n";
        
        $mappedToFullMap = [
            'РабПоГр' => 'РабПоГр_Метрол',
            'ФормИД' => 'ФормИД_Метрол',
            'ПодтВех' => 'ПодтВех_Метрол',
            'ДопРаб' => 'ДопРаб_Метрол',
            'ИзмКонф' => 'ИзмКонф_Метрол',
            'КалПов' => 'КалПов_Метрол',
            'КМХ' => 'КМХ_Метрол',
            'КоррХар' => 'КоррХар_Метрол',
            'Проч' => 'Проч_Метрол'
        ];
        
        $activityKey = $matches2[1];
        if (isset($mappedToFullMap[$activityKey])) {
            echo "   📌 Mapped activity: ✅ FOUND -> {$mappedToFullMap[$activityKey]}\n";
        } else {
            echo "   📌 Mapped activity: ❌ NOT FOUND for key '$activityKey'\n";
        }
    } else {
        echo "   📌 Mapped metrology pattern: ❌ NOT MATCHED\n";
    }
    
    // 4. Test additional works pattern
    if (preg_match('/^Табель_(ИзмКонф|КалПов|КМХ|КоррХар|Проч)_Метрол_yes$/u', $mappedCallback, $matches3)) {
        echo "   📌 Additional works pattern: ✅ MATCHED (key: {$matches3[1]})\n";
    } else {
        echo "   📌 Additional works pattern: ❌ NOT MATCHED\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 DEBUGGING COMPLETE - Check results above\n";
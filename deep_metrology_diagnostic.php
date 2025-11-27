<?php

echo "=== DEEP METROLOGY DIAGNOSTIC ===\n\n";

// Test 1: Callback data generation
echo "1. CONFIRMATION BUTTON CALLBACK GENERATION:\n";
echo "==========================================\n";

$activities = [
    'РабПоГр' => 'Работы по графику',
    'ФормИД' => 'Формирование ИД', 
    'ПодтВех' => 'Подтвердить достижение вехи'
];

foreach ($activities as $key => $name) {
    $confirmCallback = "cat_ТабМетрол_{$key}_yes";
    echo "Activity: {$name}\n";
    echo "Confirmation callback: '{$confirmCallback}'\n";
    echo "Callback length: " . strlen($confirmCallback) . " bytes\n\n";
}

// Test 2: Category extraction simulation
echo "2. CALLBACK EXTRACTION LOGIC:\n";
echo "==============================\n";

function simulateExtractCategory($callbackData) {
    echo "Input callback: '{$callbackData}'\n";
    
    // Simulate the mapping logic from TabelHandler
    $callbackMappings = [
        'cat_ТабМетрол_РабПоГр_yes' => 'Табель_РабПоГр_Метрол_yes',
        'cat_ТабМетрол_ФормИД_yes' => 'Табель_ФормИД_Метрол_yes',
        'cat_ТабМетрол_ПодтВех_yes' => 'Табель_ПодтВех_Метрол_yes',
    ];
    
    // Check for mapped callback first
    if (isset($callbackMappings[$callbackData])) {
        $mapped = $callbackMappings[$callbackData];
        echo "✅ Found in mappings: '{$mapped}'\n";
        return $mapped;
    }
    
    // Remove 'cat_' prefix if present
    if (str_starts_with($callbackData, 'cat_')) {
        $withoutPrefix = substr($callbackData, 4);
        echo "❌ Not in mappings, removed 'cat_' prefix: '{$withoutPrefix}'\n";
        return $withoutPrefix;
    }
    
    echo "❌ No transformation applied: '{$callbackData}'\n";
    return $callbackData;
}

foreach ($activities as $key => $name) {
    $confirmCallback = "cat_ТабМетрол_{$key}_yes";
    echo "\nTesting: {$name}\n";
    $extracted = simulateExtractCategory($confirmCallback);
    echo "Final extracted category: '{$extracted}'\n";
    echo "---\n";
}

// Test 3: isActivityExecution check
echo "\n3. isActivityExecution() CHECK:\n";
echo "===============================\n";

function simulateIsActivityExecution($category) {
    echo "Checking category: '{$category}'\n";
    
    // Check if contains '_yes'
    if (!str_contains($category, '_yes')) {
        echo "❌ Does not contain '_yes' - FAIL\n";
        return false;
    }
    
    // Check metrology pattern
    if (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $category, $matches)) {
        echo "✅ Matches metrology pattern!\n";
        $shortKey = $matches[1];
        echo "Extracted activity key: '{$shortKey}'\n";
        
        $validKeys = ['РабПоГр', 'ФормИД', 'ПодтВех', 'ИзмКонф', 'КалПов', 'КМХ', 'КоррХар', 'Проч'];
        if (in_array($shortKey, $validKeys)) {
            echo "✅ Activity key is valid - SUCCESS\n";
            return true;
        } else {
            echo "❌ Activity key not in valid list - FAIL\n";
            return false;
        }
    }
    
    echo "❌ Does not match metrology pattern - FAIL\n";
    return false;
}

foreach ($activities as $key => $name) {
    $confirmCallback = "cat_ТабМетрол_{$key}_yes";
    echo "\nTesting isActivityExecution for: {$name}\n";
    $result = simulateIsActivityExecution($confirmCallback);
    echo "Result: " . ($result ? "PASS" : "FAIL") . "\n";
    echo "---\n";
}

// Test 4: handleActivityExecution mapping
echo "\n4. handleActivityExecution() MAPPING:\n";
echo "=====================================\n";

function simulateActivityExecution($category) {
    echo "Processing category: '{$category}'\n";
    
    if (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $category, $matches)) {
        echo "✅ Matches metrology execution pattern!\n";
        $shortKey = $matches[1];
        echo "Extracted key: '{$shortKey}'\n";
        
        $mappings = [
            'РабПоГр' => 'РабПоГр_Метрол',
            'ФормИД' => 'ФормИД_Метрол',
            'ПодтВех' => 'ПодтВех_Метрол',
        ];
        
        if (isset($mappings[$shortKey])) {
            $activityType = $mappings[$shortKey];
            echo "✅ Mapped to activity type: '{$activityType}'\n";
            return $activityType;
        } else {
            echo "❌ Key not found in mappings!\n";
            return null;
        }
    }
    
    echo "❌ Does not match execution pattern!\n";
    return null;
}

foreach ($activities as $key => $name) {
    $confirmCallback = "cat_ТабМетрол_{$key}_yes";
    echo "\nTesting handleActivityExecution for: {$name}\n";
    $activityType = simulateActivityExecution($confirmCallback);
    if ($activityType) {
        echo "✅ Would execute with activity type: '{$activityType}'\n";
    } else {
        echo "❌ Would NOT execute - this causes 'Неизвестная команда табеля'\n";
    }
    echo "---\n";
}

// Test 5: Check TabelEntry activity types
echo "\n5. TABEL ENTRY ACTIVITY TYPES CHECK:\n";
echo "====================================\n";

$expectedActivityTypes = [
    'РабПоГр_Метрол' => '📅 Работы по графику (Метрология)',
    'ФормИД_Метрол' => '📄 Формирование ИД (Метрология)',
    'ПодтВех_Метрол' => '✅ Подтвердить достижение вехи (Метрология)',
];

echo "Expected activity types in TabelEntry:\n";
foreach ($expectedActivityTypes as $type => $description) {
    echo "'{$type}' => '{$description}'\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "\nThis diagnostic shows the expected flow. If metrology is still not working,\n";
echo "the issue is likely:\n";
echo "1. Callback mapping is not working correctly\n";
echo "2. isActivityExecution is not detecting metrology callbacks\n";
echo "3. handleActivityExecution is not being called\n";
echo "4. Activity type is not found in TabelEntry\n";
echo "\nCheck the actual debug logs to see where the flow breaks!\n";
<?php

// Runtime verification script for milestone confirmation
// This will help us see what actually happens when the button is pressed

echo "Runtime Milestone Verification Script\n";
echo "====================================\n\n";

// Check if the TabelHandler file is accessible
$tabelHandlerPath = 'CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerPath)) {
    echo "✅ TabelHandler.php found\n";
    
    // Check if the milestone mapping exists in the file
    $content = file_get_contents($tabelHandlerPath);
    
    if (strpos($content, "'ПодтВех' => 'ПодтверждениеВехи_ШМР'") !== false) {
        echo "✅ Milestone mapping found in TabelHandler\n";
    } else {
        echo "❌ Milestone mapping NOT found in TabelHandler\n";
    }
    
    if (strpos($content, "/^ШМР_(.+)_yes$/u") !== false) {
        echo "✅ Milestone pattern found in TabelHandler\n";
    } else {
        echo "❌ Milestone pattern NOT found in TabelHandler\n";
    }
} else {
    echo "❌ TabelHandler.php not found\n";
}

// Check if the TabelEntry file has the activity type
$tabelEntryPath = 'CustomCommands/Services/TabelEntry.php';
if (file_exists($tabelEntryPath)) {
    echo "✅ TabelEntry.php found\n";
    
    $content = file_get_contents($tabelEntryPath);
    
    if (strpos($content, "'ПодтверждениеВехи_ШМР' => '✅ Этап шеф-монтажа завершен'") !== false) {
        echo "✅ Milestone activity type found in TabelEntry\n";
    } else {
        echo "❌ Milestone activity type NOT found in TabelEntry\n";
    }
    
    if (strpos($content, "'ПодтверждениеВехи_ШМР', 'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'") !== false) {
        echo "✅ Milestone in commissioning activities array\n";
    } else {
        echo "❌ Milestone NOT in commissioning activities array\n";
    }
} else {
    echo "❌ TabelEntry.php not found\n";
}

echo "\n====================================\n";
echo "RUNTIME TEST INSTRUCTIONS:\n";
echo "1. Press the 'Подтвердить достижение вехи' button\n";
echo "2. Check the PHP error log for these messages:\n";
echo "   - Look for: '🎯 SITE INSPECTION: isActivityExecution detected simple supervision callback'\n";
echo "   - Look for: '🎯 SITE INSPECTION: Extracted key: ПодтВех'\n";
echo "   - Look for: '🎯 SITE INSPECTION: Key found in map'\n";
echo "3. If you don't see those messages, the callback format might be different\n";
echo "\nTo check error log: tail -f /path/to/php/error.log\n";
echo "Or check your web server error logs\n\n";

// Create a webhook test simulation
echo "CALLBACK SIMULATION TEST:\n";
echo "========================\n";
$testCallbacks = [
    'cat_ШМР_ПодтВех_yes',
    'ШМР_ПодтВех_yes', 
    'category_ШМР_ПодтВех_yes',
    'Подтвердить_достижение_вехи_yes'
];

foreach ($testCallbacks as $callback) {
    echo "Testing callback: '{$callback}'\n";
    
    // Simulate routeTabelCallback processing
    $processed = str_replace(['cat_', 'category_'], '', $callback);
    echo "  After processing: '{$processed}'\n";
    
    // Test pattern
    if (preg_match('/^ШМР_(.+)_yes$/u', $processed, $matches)) {
        echo "  ✅ Matches pattern, extracted: '{$matches[1]}'\n";
        
        $map = ['ПодтВех' => 'ПодтверждениеВехи_ШМР'];
        if (isset($map[$matches[1]])) {
            echo "  ✅ Found in mapping: '{$map[$matches[1]]}'\n";
        } else {
            echo "  ❌ NOT found in mapping\n";
        }
    } else {
        echo "  ❌ Does NOT match pattern\n";
    }
    echo "\n";
}

echo "Verification complete!\n";
<?php
echo "=== FINAL DEEP ELECTRICAL EQUIPMENT DIAGNOSIS ===\n";
echo "The routing and methods exist - let's find what's broken!\n\n";

// Test 1: Verify complete method chain exists
echo "1. METHOD CHAIN VERIFICATION:\n";
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';

$methods_to_check = [
    'TabelHandler' => [
        'handleElectricalEquipmentMenu',
        'handleAdditionalWorksElectricalMenu',
        'extractCategoryFromCallback',
        'handleOtherWorkTypes',
        'isActivityExecution'
    ],
    'MessageService' => [
        'createElectricalEquipmentKeyboard',
        'createAdditionalWorksElectricalKeyboard',
        'createConfirmationKeyboard'
    ]
];

foreach ($methods_to_check as $className => $methods) {
    $path = $className === 'TabelHandler' ? $tabelHandlerPath : $messageServicePath;
    echo "   $className:\n";
    
    if (file_exists($path)) {
        $content = file_get_contents($path);
        foreach ($methods as $method) {
            $found = strpos($content, "function $method") !== false;
            echo "     - $method: " . ($found ? "✅ EXISTS" : "❌ MISSING") . "\n";
        }
    } else {
        echo "     ❌ FILE NOT FOUND\n";
    }
    echo "\n";
}

// Test 2: Check exact routing path
echo "2. ROUTING PATH TEST:\n";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Check for the exact routing
    $routingExists = strpos($content, "if (\$category === 'cat_ТабЭлОб' || \$category === 'Табель_Электрооборудование')") !== false;
    echo "   ✓ Direct routing condition: " . ($routingExists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    if ($routingExists) {
        // Extract the routing block
        $start = strpos($content, "if (\$category === 'cat_ТабЭлОб' || \$category === 'Табель_Электрооборудование')");
        $nextIf = strpos($content, "if (", $start + 10);
        $routingBlock = substr($content, $start, $nextIf - $start);
        
        echo "   Routing block content:\n";
        $lines = explode("\n", trim($routingBlock));
        foreach ($lines as $line) {
            echo "     " . trim($line) . "\n";
        }
    }
}

// Test 3: Check MessageService keyboard methods
echo "\n3. KEYBOARD CREATION TEST:\n";
if (file_exists($messageServicePath)) {
    $content = file_get_contents($messageServicePath);
    
    // Look for createElectricalEquipmentKeyboard method
    $keyboardMethodExists = strpos($content, 'function createElectricalEquipmentKeyboard') !== false;
    echo "   ✓ createElectricalEquipmentKeyboard: " . ($keyboardMethodExists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    if ($keyboardMethodExists) {
        // Extract method content to see what buttons it creates
        $start = strpos($content, 'function createElectricalEquipmentKeyboard');
        $end = strpos($content, "}", $start);
        $methodContent = substr($content, $start, $end - $start + 1);
        
        echo "   Keyboard method content preview:\n";
        $lines = explode("\n", $methodContent);
        for ($i = 0; $i < min(15, count($lines)); $i++) {
            echo "     " . trim($lines[$i]) . "\n";
        }
    }
}

// Test 4: Check callback processing chain
echo "\n4. CALLBACK PROCESSING CHAIN:\n";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    $checks = [
        'canHandle method' => strpos($content, 'function canHandle') !== false,
        'extractCategoryFromCallback' => strpos($content, 'extractCategoryFromCallback') !== false,
        'handleOtherWorkTypes call' => strpos($content, 'handleOtherWorkTypes') !== false,
        'isActivityExecution' => strpos($content, 'isActivityExecution') !== false
    ];
    
    foreach ($checks as $check => $exists) {
        echo "   ✓ $check: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }
}

// Test 5: Look for any error patterns
echo "\n5. POTENTIAL ISSUE DETECTION:\n";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    $issues = [
        'Missing return statement' => strpos($content, 'return $this->handleElectricalEquipmentMenu') === false,
        'Encoding issues' => strpos($content, 'ensureUtf8Encoding') !== false,
        'Category pattern corruption' => strpos($content, 'fixKnownCyrillicPatterns') !== false,
        'Method accessibility' => strpos($content, 'private function handleElectricalEquipmentMenu') !== false
    ];
    
    foreach ($issues as $issue => $detected) {
        if ($issue === 'Missing return statement') {
            echo "   ⚠ $issue: " . ($detected ? "❌ PROBLEM" : "✅ OK") . "\n";
        } else {
            echo "   ✓ $issue: " . ($detected ? "✅ HANDLED" : "❓ N/A") . "\n";
        }
    }
}

// Test 6: Check if there are alternative routing paths that might interfere
echo "\n6. INTERFERENCE CHECK:\n";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Look for any OTHER handlers that might catch electrical equipment callbacks
    $patterns = [
        'Early return patterns' => '/return.*before.*handleElectricalEquipmentMenu/i',
        'Other electrical handlers' => '/if.*Электрооборудование.*(?!handleElectricalEquipmentMenu)/u',
        'Override patterns' => '/handleElectricalEquipmentMenu.*override|override.*handleElectricalEquipmentMenu/i'
    ];
    
    foreach ($patterns as $pattern_name => $pattern) {
        $matches = preg_match_all($pattern, $content);
        echo "   ✓ $pattern_name: " . ($matches > 0 ? "⚠ FOUND ($matches)" : "✅ CLEAN") . "\n";
    }
}

// Test 7: Trace the exact flow
echo "\n7. EXPECTED FLOW TRACE:\n";
echo "   When user clicks 'Электрооборудование' in commissioning:\n";
echo "   1. Button sends callback 'category_Табель_Электрооборудование'\n";
echo "   2. TabelHandler.canHandle() returns true\n";
echo "   3. TabelHandler.handle() processes callback\n";
echo "   4. extractCategoryFromCallback() returns 'Табель_Электрооборудование'\n";
echo "   5. Routing condition matches: (\$category === 'cat_ТабЭлОб' || \$category === 'Табель_Электрооборудование')\n";
echo "   6. handleElectricalEquipmentMenu(\$chatId) is called\n";
echo "   7. createElectricalEquipmentKeyboard() creates submenu\n";
echo "   8. User sees electrical equipment work types menu\n";

echo "\n8. CRITICAL DIAGNOSIS:\n";
$hasMethods = file_exists($tabelHandlerPath) && strpos(file_get_contents($tabelHandlerPath), 'function handleElectricalEquipmentMenu') !== false;
$hasRouting = file_exists($tabelHandlerPath) && strpos(file_get_contents($tabelHandlerPath), "|| \$category === 'Табель_Электрооборудование'") !== false;
$hasKeyboard = file_exists($messageServicePath) && strpos(file_get_contents($messageServicePath), 'createElectricalEquipmentKeyboard') !== false;

echo "   Core components status:\n";
echo "   - Handler method: " . ($hasMethods ? "✅ EXISTS" : "❌ MISSING") . "\n";
echo "   - Routing logic: " . ($hasRouting ? "✅ EXISTS" : "❌ MISSING") . "\n";
echo "   - Keyboard method: " . ($hasKeyboard ? "✅ EXISTS" : "❌ MISSING") . "\n";

if ($hasMethods && $hasRouting && $hasKeyboard) {
    echo "\n🎯 CONCLUSION: All core components exist!\n";
    echo "   The issue might be:\n";
    echo "   - In the keyboard button configuration\n";
    echo "   - In the callback data format\n";
    echo "   - In the activity execution flow\n";
    echo "   - Or a timing/state management issue\n";
} else {
    echo "\n❌ CONCLUSION: Missing critical components!\n";
    echo "   Need to add missing pieces before testing further.\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
?>
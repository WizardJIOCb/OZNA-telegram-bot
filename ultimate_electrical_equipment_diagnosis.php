<?php
echo "=== ULTIMATE ELECTRICAL EQUIPMENT DIAGNOSIS ===\n";
echo "Tracing COMPLETE flow from button click to report generation\n\n";

// Step 1: Find all mentions of electrical equipment in commissioning context
echo "1. COMMISSIONING ELECTRICAL EQUIPMENT DETECTION:\n";
$files = [
    'CustomCommands/Handlers/TabelHandler.php',
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Services/TabelEntry.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Search for commissioning electrical patterns
        $patterns = [
            'Электрооборудование',
            'ЭлектрОб',
            'Табель_Электрооборудование',
            'cat_ТабЭлОб',
            'ТабЭлОб',
            'Пусконаладка.*Электр',
            'Commissioning.*Electrical'
        ];
        
        echo "   File: $file\n";
        foreach ($patterns as $pattern) {
            $matches = preg_match_all('/' . $pattern . '/ui', $content, $found);
            if ($matches > 0) {
                echo "     ✓ Found '$pattern': $matches times\n";
            }
        }
        echo "\n";
    }
}

// Step 2: Deep callback flow analysis
echo "2. CALLBACK FLOW ANALYSIS:\n";
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Find all electrical equipment related callbacks
    echo "   Searching for ALL electrical equipment callbacks...\n";
    
    $electricalCallbacks = [
        'category_Табель_Электрооборудование',
        'cat_ТабЭлОб',
        'Табель_Электрооборудование',
        'ТабЭлОб',
        'category_Табель_РабГарантии_ЭлектрОб',
        'Табель_РабГарантии_ЭлектрОб'
    ];
    
    foreach ($electricalCallbacks as $callback) {
        $found = strpos($content, $callback) !== false;
        $count = substr_count($content, $callback);
        echo "     - $callback: " . ($found ? "✅ FOUND ($count times)" : "❌ MISSING") . "\n";
    }
}

echo "\n3. COMMISSIONING MENU STRUCTURE ANALYSIS:\n";
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServicePath)) {
    $content = file_get_contents($messageServicePath);
    
    // Find commissioning menu creation methods
    $menuMethods = [
        'createCommissioningWorksMenu',
        'createCommissioningEquipmentMenu', 
        'createElectricalEquipmentMenu',
        'createWarrantyEquipmentKeyboard'
    ];
    
    foreach ($menuMethods as $method) {
        $found = strpos($content, $method) !== false;
        echo "   - $method: " . ($found ? "✅ EXISTS" : "❌ MISSING") . "\n";
        
        if ($found) {
            // Extract method content
            $start = strpos($content, "function $method");
            if ($start !== false) {
                $end = strpos($content, "}", $start);
                $methodContent = substr($content, $start, $end - $start + 1);
                
                // Check for electrical equipment in this method
                if (strpos($methodContent, 'Электрооборудование') !== false || 
                    strpos($methodContent, 'ЭлектрОб') !== false) {
                    echo "     ✓ Contains electrical equipment references\n";
                } else {
                    echo "     ⚠ No electrical equipment references found\n";
                }
            }
        }
    }
}

echo "\n4. HANDLER ROUTING DIAGNOSIS:\n";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Check for commissioning routing
    echo "   Checking commissioning work routing...\n";
    
    $commissioningPatterns = [
        "if.*category.*Табель_Пусконаладка",
        "if.*category.*Пусконаладка",
        "if.*category.*Commissioning",
        "handleCommissioningWorksMenu",
        "handleCommissioningDateSelection"
    ];
    
    foreach ($commissioningPatterns as $pattern) {
        $matches = preg_match_all('/' . $pattern . '/ui', $content);
        echo "     - Pattern '$pattern': " . ($matches ? "✅ FOUND" : "❌ MISSING") . "\n";
    }
}

echo "\n5. REPORT GENERATION FLOW ANALYSIS:\n";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Find report generation methods
    $reportMethods = [
        'generateTabelReport',
        'createTabelEntry', 
        'submitTabelData',
        'handleTabelSubmission',
        'saveTabelEntry'
    ];
    
    echo "   Report generation methods:\n";
    foreach ($reportMethods as $method) {
        $found = strpos($content, $method) !== false;
        echo "     - $method: " . ($found ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }
    
    // Check for confirmation handling
    echo "\n   Confirmation handling:\n";
    $confirmationPatterns = [
        'isActivityExecution.*true',
        'handleActivityExecution',
        '_yes.*callback',
        'confirmation.*keyboard'
    ];
    
    foreach ($confirmationPatterns as $pattern) {
        $matches = preg_match_all('/' . $pattern . '/ui', $content);
        echo "     - Pattern '$pattern': " . ($matches ? "✅ FOUND" : "❌ MISSING") . "\n";
    }
}

echo "\n6. SPECIFIC ELECTRICAL EQUIPMENT HANDLERS:\n";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    // Look for specific electrical equipment handlers in different contexts
    $contexts = [
        'Warranty' => ['РабГарантии_ЭлектрОб', 'warranty.*electrical'],
        'Commissioning' => ['Пусконаладка.*ЭлектрОб', 'commissioning.*electrical'], 
        'Regular Work' => ['ТабЭлОб', 'electrical.*equipment.*menu'],
        'Additional Works' => ['ДопРаб.*ЭлектрОб', 'additional.*electrical']
    ];
    
    foreach ($contexts as $contextName => $patterns) {
        echo "   $contextName context:\n";
        foreach ($patterns as $pattern) {
            $matches = preg_match_all('/' . $pattern . '/ui', $content);
            echo "     - Pattern '$pattern': " . ($matches ? "✅ FOUND ($matches)" : "❌ MISSING") . "\n";
        }
    }
}

echo "\n7. CRITICAL FLOW VERIFICATION:\n";
echo "   Expected flow for COMMISSIONING electrical equipment:\n";
echo "   1. User goes to Commissioning Works (Пусконаладочные работы)\n";
echo "   2. Selects equipment type menu\n"; 
echo "   3. Clicks 'Электрооборудование' button\n";
echo "   4. System processes callback\n";
echo "   5. Shows confirmation dialog\n";
echo "   6. User confirms\n";
echo "   7. REPORT IS GENERATED ← THIS MUST WORK!\n";

// Check if this flow exists
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    $flowComponents = [
        'handleCommissioningWorksMenu' => strpos($content, 'handleCommissioningWorksMenu') !== false,
        'electrical_equipment_handler' => strpos($content, 'Табель_Электрооборудование') !== false,
        'confirmation_creation' => strpos($content, 'createConfirmationKeyboard') !== false,
        'activity_execution' => strpos($content, 'isActivityExecution') !== false
    ];
    
    echo "\n   Flow components check:\n";
    foreach ($flowComponents as $component => $exists) {
        echo "     - $component: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }
}

echo "\n8. URGENT SEARCH FOR MISSING COMMISSIONING ELECTRICAL HANDLER:\n";
// Search in backup files for reference
$backupFiles = glob(__DIR__ . '/TabelHandler*backup*.php');
echo "   Checking backup files for reference implementation...\n";

foreach ($backupFiles as $backupFile) {
    if (file_exists($backupFile)) {
        $content = file_get_contents($backupFile);
        
        // Look for commissioning electrical equipment handler
        if (strpos($content, 'Табель_Электрооборудование') !== false) {
            echo "     ✓ Found electrical equipment reference in: " . basename($backupFile) . "\n";
            
            // Extract the handler
            $lines = explode("\n", $content);
            $foundHandler = false;
            for ($i = 0; $i < count($lines); $i++) {
                if (strpos($lines[$i], "category === 'Табель_Электрооборудование'") !== false) {
                    echo "       Handler found at line " . ($i + 1) . ":\n";
                    // Show next 10 lines
                    for ($j = $i; $j < min($i + 10, count($lines)); $j++) {
                        echo "       " . ($j + 1) . ": " . trim($lines[$j]) . "\n";
                    }
                    $foundHandler = true;
                    break;
                }
            }
            
            if (!$foundHandler) {
                echo "       ⚠ Reference found but no complete handler located\n";
            }
        }
    }
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
echo "Now proceeding to identify and fix the exact issue...\n";
?>
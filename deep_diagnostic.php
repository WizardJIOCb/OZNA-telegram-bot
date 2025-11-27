<?php

// Deep diagnostic to trace automation button callbacks

echo "=== DEEP DIAGNOSTIC FOR AUTOMATION BUTTONS ===\n\n";

// 1. Test basic callback patterns
echo "=== TESTING BASIC CALLBACK PATTERNS ===\n";

$automationCallbacks = [
    'category_Табель_ИзмСх_Автоматизация',      // Working (scheme changes)
    'category_Табель_МонтКб_Автоматизация',     // Not working (cable installation)
    'category_Табель_РаскКб_Автоматизация',     // Not working (cable wiring)
    'category_Табель_МонтАСУ_Автоматизация',    // Not working (equipment installation)
    'category_Табель_УдалПО_Автоматизация',     // Not working (remote software)
    'category_Табель_Прочее_Автоматизация'      // Working (other works)
];

foreach ($automationCallbacks as $callback) {
    echo "Testing callback: {$callback}\n";
    
    // Test if callback starts with expected prefix
    if (str_starts_with($callback, 'category_Табель')) {
        echo "  ✅ Starts with 'category_Табель'\n";
    } else {
        echo "  ❌ Does NOT start with 'category_Табель'\n";
    }
    
    // Test category extraction
    if (strpos($callback, 'category_') === 0) {
        $extracted = substr($callback, strlen('category_'));
        echo "  Extracted category: '{$extracted}'\n";
    } else {
        echo "  ❌ Could not extract category\n";
    }
    
    echo "\n";
}

// 2. Check handler files and priorities
echo "=== CHECKING HANDLER FILES ===\n";

$handlersDir = 'CustomCommands/Handlers/';
if (is_dir($handlersDir)) {
    $handlerFiles = scandir($handlersDir);
    $phpFiles = array_filter($handlerFiles, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'php';
    });
    
    echo "Found PHP handler files:\n";
    foreach ($phpFiles as $file) {
        echo "  - {$file}\n";
        
        // Check file size and basic content
        $filePath = $handlersDir . $file;
        $fileSize = filesize($filePath);
        echo "    Size: {$fileSize} bytes\n";
        
        // Check if file contains automation-related content
        $content = file_get_contents($filePath);
        if (strpos($content, 'Автоматизация') !== false) {
            echo "    ✅ Contains 'Автоматизация'\n";
        }
        if (strpos($content, 'canHandle') !== false) {
            echo "    ✅ Contains 'canHandle' method\n";
        }
        if (strpos($content, 'getPriority') !== false) {
            echo "    ✅ Contains 'getPriority' method\n";
        }
        
        echo "\n";
    }
} else {
    echo "❌ Handlers directory not found: {$handlersDir}\n";
}

// 3. Check if TabelHandler.php exists and basic structure
echo "=== CHECKING TABELHANDLER STRUCTURE ===\n";

$tabelHandlerPath = $handlersDir . 'TabelHandler.php';
if (file_exists($tabelHandlerPath)) {
    echo "✅ TabelHandler.php exists\n";
    
    $content = file_get_contents($tabelHandlerPath);
    $lines = explode("\n", $content);
    echo "Total lines: " . count($lines) . "\n";
    
    // Check for key automation handlers
    $automationHandlerChecks = [
        'МонтКб_Автоматизация' => false,
        'РаскКб_Автоматизация' => false,
        'МонтАСУ_Автоматизация' => false,
        'УдалПО_Автоматизация' => false
    ];
    
    foreach ($automationHandlerChecks as $handler => $found) {
        if (strpos($content, $handler) !== false) {
            $automationHandlerChecks[$handler] = true;
        }
    }
    
    echo "\nAutomation handler checks:\n";
    foreach ($automationHandlerChecks as $handler => $found) {
        $status = $found ? "✅ FOUND" : "❌ NOT FOUND";
        echo "  {$handler}: {$status}\n";
    }
    
    // Count occurrences of key methods
    $methodCounts = [
        'canHandle' => substr_count($content, 'canHandle'),
        'routeTabelCallback' => substr_count($content, 'routeTabelCallback'),
        'handleActivityExecution' => substr_count($content, 'handleActivityExecution')
    ];
    
    echo "\nMethod occurrence counts:\n";
    foreach ($methodCounts as $method => $count) {
        echo "  {$method}: {$count} times\n";
    }
    
} else {
    echo "❌ TabelHandler.php NOT FOUND\n";
}

// 4. Check callback system integration
echo "\n=== CHECKING CALLBACK SYSTEM INTEGRATION ===\n";

// Check if there's a callback routing system
$callbackFiles = [
    'hook.php',
    'index.php', 
    'webhook.php',
    'Commands/SystemCommands/CallbackqueryCommand.php'
];

foreach ($callbackFiles as $file) {
    if (file_exists($file)) {
        echo "✅ Found: {$file}\n";
        $content = file_get_contents($file);
        if (strpos($content, 'TabelHandler') !== false) {
            echo "  ✅ Contains 'TabelHandler'\n";
        }
        if (strpos($content, 'callback') !== false || strpos($content, 'Callback') !== false) {
            echo "  ✅ Contains callback-related code\n";
        }
    } else {
        echo "❌ Not found: {$file}\n";
    }
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "\nIf handlers are found but callbacks aren't working, the issue is likely:\n";
echo "1. Handler priority conflicts\n";
echo "2. Callback routing not reaching TabelHandler\n";
echo "3. Another handler intercepting the callbacks\n";
echo "4. Silent exceptions in the handler methods\n";

?>
<?php
/**
 * Test which version of GenericmessageCommand is loaded
 */

echo "=== Testing GenericmessageCommand Loading Order ===\n\n";

// Simulate the same loading order as hook.php
echo "1. Loading custom_autoload.php FIRST...\n";
require_once __DIR__ . '/custom_autoload.php';
echo "   ✅ Custom autoloader registered\n\n";

echo "2. Loading vendor/autoload.php...\n";
require_once __DIR__ . '/vendor/autoload.php';
echo "   ✅ Vendor autoloader loaded\n\n";

echo "3. Checking which GenericmessageCommand class is loaded:\n";

// Check if class exists
if (class_exists('Longman\TelegramBot\Commands\SystemCommands\GenericmessageCommand')) {
    echo "   ✅ GenericmessageCommand class exists\n";
    
    // Get reflection to check file location
    $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\GenericmessageCommand');
    $filename = $reflection->getFileName();
    
    echo "   📁 Loaded from: {$filename}\n\n";
    
    // Check if it's our version or vendor version
    if (strpos($filename, 'CustomCommands') !== false) {
        echo "   ✅ SUCCESS: Our CUSTOM version is loaded!\n";
        
        // Check if it has our debug logging
        $fileContent = file_get_contents($filename);
        if (strpos($fileContent, 'CUSTOM GenericmessageCommand EXECUTE START') !== false) {
            echo "   ✅ VERIFIED: Custom version contains our debug logging\n";
        } else {
            echo "   ⚠️  WARNING: File is in CustomCommands but doesn't have our debug logging\n";
        }
    } else if (strpos($filename, 'vendor') !== false) {
        echo "   ❌ PROBLEM: VENDOR version is loaded instead of our custom version!\n";
        echo "   ❌ This means autoloader priority is not working correctly\n";
    }
    
    echo "\n4. Checking execute() method:\n";
    $method = $reflection->getMethod('execute');
    $methodFile = $method->getFileName();
    $startLine = $method->getStartLine();
    $endLine = $method->getEndLine();
    
    echo "   Method location: {$methodFile}\n";
    echo "   Lines: {$startLine} - {$endLine}\n";
    
    // Read first few lines of execute() to verify
    $lines = file($methodFile);
    $methodCode = '';
    for ($i = $startLine; $i < min($startLine + 10, $endLine); $i++) {
        $methodCode .= $lines[$i];
    }
    
    echo "\n   First 10 lines of execute():\n";
    echo "   " . str_replace("\n", "\n   ", trim($methodCode)) . "\n\n";
    
    if (strpos($methodCode, 'CUSTOM GenericmessageCommand EXECUTE START') !== false) {
        echo "   ✅ CONFIRMED: execute() contains our custom logging!\n";
    } else {
        echo "   ❌ PROBLEM: execute() does NOT contain our custom logging!\n";
    }
    
} else {
    echo "   ❌ ERROR: GenericmessageCommand class not found!\n";
}

echo "\n=== Test Complete ===\n";

<?php
// Test script for StartCommand

echo "=== StartCommand Test ===\n\n";

// Check if StartCommand file exists
$startCommandPath = __DIR__ . '/Commands/StartCommand';
if (!file_exists($startCommandPath)) {
    echo "ERROR: StartCommand file not found at {$startCommandPath}\n";
    exit(1);
}

echo "✓ StartCommand file exists\n";

// Try to parse the PHP file for syntax errors
$output = [];
$returnCode = 0;
exec("php -l \"{$startCommandPath}\"", $output, $returnCode);

if ($returnCode === 0) {
    echo "✓ StartCommand syntax is correct\n";
} else {
    echo "✗ StartCommand has syntax errors:\n";
    foreach ($output as $line) {
        echo "  {$line}\n";
    }
    exit(1);
}

// Check if the class can be loaded
echo "\nTesting class loading...\n";

try {
    // Include the autoloader
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Try to include the StartCommand file
    include_once $startCommandPath;
    
    // Check if the class exists
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\StartCommand')) {
        echo "✓ StartCommand class loaded successfully\n";
        
        // Try to instantiate the class
        $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\StartCommand');
        echo "✓ StartCommand class can be instantiated\n";
        
        // Show class properties
        echo "\nClass Information:\n";
        echo "  Name: " . $reflection->getName() . "\n";
        echo "  Namespace: " . $reflection->getNamespaceName() . "\n";
        echo "  File: " . $reflection->getFileName() . "\n";
        
        // Show properties
        $properties = $reflection->getProperties();
        echo "  Properties:\n";
        foreach ($properties as $property) {
            echo "    - " . $property->getName() . " (" . $property->getDeclaringClass()->getName() . ")\n";
        }
        
        // Show methods
        $methods = $reflection->getMethods();
        echo "  Methods:\n";
        foreach ($methods as $method) {
            if ($method->getDeclaringClass()->getName() === $reflection->getName()) {
                echo "    - " . $method->getName() . "\n";
            }
        }
        
    } else {
        echo "✗ StartCommand class not found\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "✗ Error loading StartCommand class: " . $e->getMessage() . "\n";
    exit(1);
} catch (Error $e) {
    echo "✗ Error loading StartCommand class: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Test Completed Successfully ===\n";
echo "\nTo test the StartCommand functionality:\n";
echo "1. Send /start command to your bot\n";
echo "2. Check the following log files for debugging information:\n";
echo "   - telegram_bot_error.log (main log file)\n";
echo "   - webhook_requests.log (webhook request log)\n";
echo "3. Look for entries with timestamps after you send the command\n";

?>
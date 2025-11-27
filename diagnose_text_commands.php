<?php
// Diagnostic script to check text command handling

echo "Diagnosing text command handling...\n\n";

// Check if the Telegram bot library is available
echo "1. Checking Telegram bot library...\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "   ✓ Composer autoload.php found\n";
} else {
    echo "   ✗ Composer autoload.php not found\n";
    exit(1);
}

// Load composer
require __DIR__ . '/vendor/autoload.php';

// Check if CustomCommands directory exists
echo "\n2. Checking CustomCommands directory...\n";
if (is_dir(__DIR__ . '/CustomCommands')) {
    echo "   ✓ CustomCommands directory found\n";
    
    // List command files
    $commandFiles = glob(__DIR__ . '/CustomCommands/*.php');
    echo "   Command files found:\n";
    foreach ($commandFiles as $file) {
        $filename = basename($file);
        echo "     - $filename\n";
    }
} else {
    echo "   ✗ CustomCommands directory not found\n";
    exit(1);
}

// Check TextmenuCommand specifically
echo "\n3. Checking TextmenuCommand...\n";
$textmenuPath = __DIR__ . '/CustomCommands/TextmenuCommand.php';
if (file_exists($textmenuPath)) {
    echo "   ✓ TextmenuCommand.php found\n";
    
    // Check if it has the correct name
    $content = file_get_contents($textmenuPath);
    if (strpos($content, "protected \$name = 'genericmessage'") !== false) {
        echo "   ✓ TextmenuCommand properly configured as genericmessage handler\n";
    } else {
        echo "   ✗ TextmenuCommand not properly configured\n";
    }
    
    // Check if it has the right class
    if (strpos($content, 'class TextmenuCommand extends UserCommand') !== false) {
        echo "   ✓ TextmenuCommand extends UserCommand\n";
    } else {
        echo "   ✗ TextmenuCommand does not extend UserCommand\n";
    }
} else {
    echo "   ✗ TextmenuCommand.php not found\n";
}

// Check for conflicting commands
echo "\n4. Checking for conflicting commands...\n";
$conflictingCommands = [
    'GenericmessageCommand.php',
    'TestMessageCommand.php'
];

foreach ($conflictingCommands as $command) {
    $path = __DIR__ . '/CustomCommands/' . $command;
    if (file_exists($path)) {
        echo "   ✗ Conflicting command found: $command\n";
    } else {
        echo "   ✓ No conflicting command: $command\n";
    }
}

// Check command paths registration
echo "\n5. Checking command paths...\n";
$hookContent = file_get_contents(__DIR__ . '/hook.php');
if (strpos($hookContent, "addCommandsPaths(array(__DIR__ . '/CustomCommands'))") !== false) {
    echo "   ✓ CustomCommands path registered correctly\n";
} else {
    echo "   ✗ CustomCommands path not registered correctly\n";
}

echo "\n6. Testing command loading...\n";
try {
    // Try to load the TextmenuCommand
    require_once __DIR__ . '/CustomCommands/TextmenuCommand.php';
    echo "   ✓ TextmenuCommand loaded successfully\n";
    
    // Check if the class exists
    if (class_exists('Longman\TelegramBot\Commands\UserCommands\TextmenuCommand')) {
        echo "   ✓ TextmenuCommand class exists\n";
    } else {
        echo "   ✗ TextmenuCommand class does not exist\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error loading TextmenuCommand: " . $e->getMessage() . "\n";
}

echo "\nDiagnosis complete.\n";
?>
<?php
/**
 * Check if all required files can be loaded
 */

echo "=== Checking File Loading ===\n\n";

$files = [
    'CustomCommands/Services/DatabaseService.php',
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Services/AdminService.php',
    'CustomCommands/Handlers/ProjectModerationHandler.php',
    'CustomCommands/Handlers/ProjectHandler.php',
    'CustomCommands/Handlers/AdminHandler.php',
    'CustomCommands/CallbackqueryCommand.php',
    'CustomCommands/GenericmessageCommand.php'
];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;
    
    echo "Checking: $file\n";
    
    if (!file_exists($fullPath)) {
        echo "  ✗ File not found!\n";
        continue;
    }
    
    echo "  ✓ File exists\n";
    
    // Try to include
    try {
        include_once $fullPath;
        echo "  ✓ File loads successfully\n";
    } catch (Throwable $e) {
        echo "  ✗ Error loading: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== Checking Classes ===\n\n";

$classes = [
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\DatabaseService',
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\MessageService',
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\AdminService',
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Handlers\\ProjectModerationHandler',
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Handlers\\ProjectHandler',
    'Longman\\TelegramBot\\Commands\\SystemCommands\\Handlers\\AdminHandler'
];

foreach ($classes as $class) {
    $shortName = substr($class, strrpos($class, '\\') + 1);
    echo "Class: $shortName\n";
    
    if (class_exists($class)) {
        echo "  ✓ Class exists\n";
        
        // Check if it has required methods
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        echo "  ✓ Public methods: " . count($methods) . "\n";
    } else {
        echo "  ✗ Class not found!\n";
    }
    
    echo "\n";
}

echo "=== All checks complete ===\n";

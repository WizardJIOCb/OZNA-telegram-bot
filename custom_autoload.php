<?php

// Custom autoloader for our services and handlers
// IMPORTANT: This autoloader runs BEFORE composer's autoloader
// to ensure our custom GenericmessageCommand is loaded instead of vendor version
spl_autoload_register(function ($class) {
    // Our custom namespace mappings
    $mappings = [
        'Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\' => __DIR__ . '/CustomCommands/Services/',
        'Longman\\TelegramBot\\Commands\\SystemCommands\\Handlers\\' => __DIR__ . '/CustomCommands/Handlers/',
        'Longman\\TelegramBot\\Commands\\SystemCommands\\' => __DIR__ . '/CustomCommands/',
    ];

    foreach ($mappings as $namespace => $path) {
        if (strpos($class, $namespace) === 0) {
            $relativeClass = substr($class, strlen($namespace));
            $file = $path . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (file_exists($file)) {
                require_once $file;
                error_log("Custom autoloader: Loaded custom class {$class} from {$file}");
                return;
            }
        }
    }
}, true, true); // prepend = true to run BEFORE other autoloaders
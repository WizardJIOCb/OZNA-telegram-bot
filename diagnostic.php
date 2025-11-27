<?php
/**
 * Diagnostic file - check if new handlers are loaded
 * Access via: https://ozna.ruc-nw.ru/diagnostic.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Bot Diagnostic Information ===\n\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Check if files exist
echo "1. File Check:\n";
$files = [
    'CustomCommands/Handlers/ProjectModerationHandler.php' => 'ProjectModerationHandler',
    'CustomCommands/Services/DatabaseService.php' => 'DatabaseService (updated)',
    'database/migrations/create_project_moderation_table.sql' => 'Migration SQL'
];

foreach ($files as $file => $desc) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "  " . ($exists ? '✓' : '✗') . " {$desc}: " . ($exists ? 'EXISTS' : 'NOT FOUND') . "\n";
}

echo "\n2. Class Check:\n";

// Try to load classes
require_once __DIR__ . '/vendor/autoload.php';

$classes = [
    'Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectModerationHandler',
    'Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService'
];

foreach ($classes as $class) {
    try {
        if (class_exists($class, true)) {
            $reflection = new ReflectionClass($class);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            $methodNames = array_map(function($m) { return $m->getName(); }, $methods);
            
            $shortName = substr($class, strrpos($class, '\\') + 1);
            echo "  ✓ {$shortName}: " . count($methods) . " methods\n";
            
            // Check for specific methods
            if ($shortName === 'DatabaseService') {
                $hasNewMethods = in_array('addPendingProject', $methodNames) && 
                                 in_array('getPendingProjects', $methodNames);
                echo "    " . ($hasNewMethods ? '✓' : '✗') . " New moderation methods present\n";
            }
            
            if ($shortName === 'ProjectModerationHandler') {
                $hasMethods = in_array('canHandle', $methodNames) && 
                              in_array('handle', $methodNames);
                echo "    " . ($hasMethods ? '✓' : '✗') . " Handler methods present\n";
            }
        } else {
            $shortName = substr($class, strrpos($class, '\\') + 1);
            echo "  ✗ {$shortName}: NOT FOUND\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n3. Database Check:\n";
$conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');

if ($conn->connect_error) {
    echo "  ✗ Database connection failed\n";
} else {
    echo "  ✓ Database connected\n";
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'project_moderation'");
    if ($result->num_rows > 0) {
        echo "  ✓ Table 'project_moderation' exists\n";
        
        // Count records
        $result = $conn->query("SELECT COUNT(*) as count FROM project_moderation");
        $row = $result->fetch_assoc();
        echo "  ✓ Records in table: " . $row['count'] . "\n";
    } else {
        echo "  ✗ Table 'project_moderation' NOT FOUND\n";
    }
    
    $conn->close();
}

echo "\n4. OPcache Status:\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status) {
        echo "  ✓ OPcache is enabled\n";
        echo "    Cached files: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "    Hits: " . $status['opcache_statistics']['hits'] . "\n";
        echo "    Misses: " . $status['opcache_statistics']['misses'] . "\n";
        
        echo "\n  To clear OPcache, use: opcache_reset()\n";
    } else {
        echo "  - OPcache is disabled\n";
    }
} else {
    echo "  - OPcache not available\n";
}

echo "\n5. Callback Handler Registration:\n";
try {
    require_once __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
    
    $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand');
    $source = file_get_contents($reflection->getFileName());
    
    if (strpos($source, 'ProjectModerationHandler') !== false) {
        echo "  ✓ ProjectModerationHandler is registered in CallbackqueryCommand\n";
    } else {
        echo "  ✗ ProjectModerationHandler NOT found in CallbackqueryCommand\n";
    }
    
    if (strpos($source, "proj_mod_") !== false) {
        echo "  ✓ proj_mod_ callbacks are allowed\n";
    } else {
        echo "  ✗ proj_mod_ callbacks NOT allowed\n";
    }
} catch (Exception $e) {
    echo "  ✗ Error checking: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
echo "\nIf everything shows ✓, try clearing OPcache:\n";
echo "  1. Visit: https://ozna.ruc-nw.ru/clear_cache.php\n";
echo "  2. Or restart PHP-FPM/Apache\n";

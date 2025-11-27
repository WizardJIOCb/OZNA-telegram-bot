<?php

/**
 * Live code verification - check if the fix is actually being used by the running system
 */

echo "=== LIVE CODE VERIFICATION ===\n\n";

// Check if the TabelEntry file was modified recently
$tabelEntryFile = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
$lastModified = filemtime($tabelEntryFile);
$lastModifiedFormatted = date('Y-m-d H:i:s', $lastModified);

echo "📁 File: {$tabelEntryFile}\n";
echo "📅 Last modified: {$lastModifiedFormatted}\n";

$timeSinceModified = time() - $lastModified;
if ($timeSinceModified < 3600) { // Less than 1 hour
    echo "✅ File was modified recently ({$timeSinceModified} seconds ago)\n";
} else {
    echo "⚠️  File was modified {$timeSinceModified} seconds ago\n";
}

// Read the actual content and verify our fix is present
$content = file_get_contents($tabelEntryFile);

echo "\n🔍 Checking for our fix...\n";

if (strpos($content, '$this->activityTypeMappings[$activityType]') !== false) {
    echo "✅ FOUND: Correct property usage (\$this->activityTypeMappings)\n";
} else {
    echo "❌ MISSING: Correct property usage not found\n";
}

if (strpos($content, '$this->activityTypes[$activityType]') !== false) {
    echo "❌ PROBLEM: Still using incorrect property (\$this->activityTypes)\n";
} else {
    echo "✅ GOOD: Not using incorrect property\n";
}

// Check the exact line 153 where our fix should be
$lines = file($tabelEntryFile);
$line153 = isset($lines[152]) ? trim($lines[152]) : 'Line not found';

echo "\n📝 Line 153 (where our fix should be):\n";
echo "   {$line153}\n";

if (strpos($line153, 'activityTypeMappings') !== false) {
    echo "✅ Line 153 contains correct property name\n";
} else {
    echo "❌ Line 153 does NOT contain correct property name\n";
}

// Check if there's any caching that might be interfering
echo "\n🔍 Checking for caching issues...\n";

if (function_exists('opcache_get_status')) {
    $opcacheStatus = opcache_get_status();
    if ($opcacheStatus && $opcacheStatus['opcache_enabled']) {
        echo "⚠️  OPcache is enabled - this could cache old code!\n";
        echo "   Cached scripts: " . $opcacheStatus['opcache_statistics']['num_cached_scripts'] . "\n";
        
        // Check if our file is cached
        if (function_exists('opcache_is_script_cached')) {
            $isCached = opcache_is_script_cached($tabelEntryFile);
            echo "   TabelEntry.php cached: " . ($isCached ? 'YES' : 'NO') . "\n";
            
            if ($isCached) {
                echo "   🚨 SOLUTION: Clear OPcache or restart PHP-FPM/Apache!\n";
            }
        }
    } else {
        echo "✅ OPcache is disabled\n";
    }
} else {
    echo "ℹ️  OPcache functions not available\n";
}

// Check if APCu is caching anything
if (function_exists('apcu_cache_info')) {
    try {
        // Note: apcu_cache_info may not be available in all environments
        $apcuInfo = @apcu_cache_info();
        echo "⚠️  APCu is active - might be caching data\n";
    } catch (Exception $e) {
        echo "✅ APCu not interfering\n";
    }
} else {
    echo "✅ APCu not available\n";
}

// Create a test to verify the actual running code behavior
echo "\n🧪 TESTING ACTUAL RUNNING CODE...\n";

// Try to include and test the actual class if possible
try {
    require_once $tabelEntryFile;
    
    // Use reflection to inspect the actual loaded class
    $reflectionClass = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry');
    
    // Check method source code in memory
    $method = $reflectionClass->getMethod('createTabelMessage');
    $filename = $method->getFileName();
    $startLine = $method->getStartLine();
    $endLine = $method->getEndLine();
    
    $source = implode('', array_slice(file($filename), $startLine - 1, $endLine - $startLine + 1));
    
    if (strpos($source, 'activityTypeMappings') !== false) {
        echo "✅ LOADED CODE: Uses correct property (activityTypeMappings)\n";
    } else {
        echo "❌ LOADED CODE: Does NOT use correct property\n";
    }
    
    if (strpos($source, '$this->activityTypes[') !== false) {
        echo "❌ LOADED CODE: Still uses incorrect property (activityTypes)\n";
    } else {
        echo "✅ LOADED CODE: Does NOT use incorrect property\n";
    }
    
} catch (Exception $e) {
    echo "⚠️  Could not load class for testing: {$e->getMessage()}\n";
}

echo "\n💡 RECOMMENDATIONS:\n";

if (function_exists('opcache_reset')) {
    echo "1. Clear OPcache: opcache_reset()\n";
    echo "2. Or restart web server (Apache/Nginx)\n";
    echo "3. Or restart PHP-FPM if using it\n";
} else {
    echo "1. Restart web server (Apache/Nginx)\n";
    echo "2. Restart PHP-FPM if using it\n";
}

echo "4. Check if there are multiple web servers running\n";
echo "5. Verify the bot is reading from the correct directory\n";

echo "\n=== VERIFICATION COMPLETE ===\n";
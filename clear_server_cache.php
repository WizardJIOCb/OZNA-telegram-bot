<?php
// OPcache reset script - upload this to your server and run it

echo "=== OPCACHE RESET ===\n";

if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo $result ? "✅ OPcache cleared successfully\n" : "❌ Failed to clear OPcache\n";
    
    // Get OPcache status
    if (function_exists('opcache_get_status')) {
        $status = opcache_get_status();
        echo "OPcache enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "\n";
        echo "Cache hits: " . $status['opcache_statistics']['hits'] . "\n";
        echo "Cache misses: " . $status['opcache_statistics']['misses'] . "\n";
    }
} else {
    echo "ℹ️ OPcache not available\n";
}

// Also try to clear any other caches
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✅ APC cache cleared\n";
}

if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✅ APCu cache cleared\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Upload this file to your server\n";
echo "2. Run it via web browser or command line\n";
echo "3. Test the PNR button again\n";
echo "4. The button should now work with date selection!\n";
?>
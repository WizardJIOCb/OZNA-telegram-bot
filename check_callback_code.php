<?php
/**
 * Check if CallbackqueryCommand.php has the critical fix applied
 */

$file = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';

if (!file_exists($file)) {
    die("❌ File not found: {$file}\n");
}

$content = file_get_contents($file);

echo "=== CHECKING CALLBACK QUERY COMMAND FILE ===\n\n";

// Check 1: Does it have the CRITICAL FIX comment?
if (strpos($content, 'CRITICAL FIX: Temporarily disable early exit') !== false) {
    echo "✅ Found CRITICAL FIX comment\n";
} else {
    echo "❌ CRITICAL FIX comment NOT found\n";
}

// Check 2: Is $shouldExit set to false?
if (preg_match('/\$shouldExit\s*=\s*false\s*;/', $content)) {
    echo "✅ Found '\$shouldExit = false;'\n";
} else {
    echo "❌ '\$shouldExit = false;' NOT found\n";
    echo "⚠️ Checking for other $shouldExit assignments...\n";
    if (preg_match_all('/\$shouldExit\s*=\s*([^;]+);/', $content, $matches)) {
        foreach ($matches[0] as $match) {
            echo "   Found: {$match}\n";
        }
    }
}

// Check 3: Deep callback monitor logging
if (strpos($content, 'deep_callback_monitor.log') !== false) {
    echo "✅ Found deep callback monitor logging\n";
} else {
    echo "❌ Deep callback monitor logging NOT found\n";
}

// Check 4: Extract the relevant code section
echo "\n=== RELEVANT CODE SECTION ===\n";
$startPos = strpos($content, '// CRITICAL FIX:');
if ($startPos !== false) {
    $endPos = strpos($content, 'if ($shouldExit)', $startPos);
    if ($endPos !== false) {
        $endPos = strpos($content, '}', $endPos) + 1;
        $section = substr($content, $startPos, $endPos - $startPos);
        echo $section;
    }
} else {
    echo "❌ Could not find CRITICAL FIX section\n";
}

echo "\n\n=== FILE INFO ===\n";
echo "File size: " . filesize($file) . " bytes\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";

echo "\n=== OPcache STATUS ===\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status) {
        echo "OPcache enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "\n";
        if (isset($status['scripts'])) {
            if (isset($status['scripts'][$file])) {
                echo "⚠️ File IS cached in OPcache\n";
                echo "   Cached at: " . date('Y-m-d H:i:s', $status['scripts'][$file]['timestamp']) . "\n";
            } else {
                echo "✅ File NOT cached in OPcache (or cache cleared)\n";
            }
        }
    } else {
        echo "❌ Could not get OPcache status\n";
    }
} else {
    echo "❌ OPcache functions not available\n";
}

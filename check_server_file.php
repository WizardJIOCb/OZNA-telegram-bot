<?php
/**
 * Check EXACT server file version via database query
 * This will tell us if the file was actually uploaded
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Checking ACTUAL Server File Version ===\n\n";

// Connect to database
$conn = new mysqli('localhost', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
if ($conn->connect_error) {
    die("❌ DB connection failed: " . $conn->connect_error);
}

// Read the actual file content from server filesystem using LOAD_FILE
// This shows what's REALLY on the server, not cached version
$result = $conn->query("SELECT LOAD_FILE('/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php') as content");
$row = $result->fetch_assoc();

if (!$row || !$row['content']) {
    echo "❌ Cannot read file via LOAD_FILE\n";
    echo "   File may not exist or permissions issue\n\n";
    
    // Fallback: check file stats
    $file = '/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php';
    if (file_exists($file)) {
        echo "File exists:\n";
        echo "  Size: " . filesize($file) . " bytes\n";
        echo "  Modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
        echo "  MD5: " . md5_file($file) . "\n\n";
        
        // Count our fixes
        $content = file_get_contents($file);
        $freeCount = substr_count($content, '$result->free();');
        $bind9 = substr_count($content, "bind_param('sssssssss'");
        $bind8 = substr_count($content, "bind_param('ssssssss'");
        
        echo "Fix counts:\n";
        echo "  \$result->free(): {$freeCount}\n";
        echo "  bind_param('sssssssss'): {$bind9}\n";
        echo "  bind_param('ssssssss'): {$bind8}\n\n";
        
        if ($freeCount >= 15 && $bind9 >= 3) {
            echo "✅ File IS correctly updated on server!\n";
            echo "   Problem is OPcache - needs restart.\n";
        } else {
            echo "❌ File NOT updated on server!\n";
            echo "   Expected: 15+ free(), 3+ bind_param('sssssssss')\n";
            echo "   SFTP sync may have failed.\n";
        }
    } else {
        echo "❌ File does not exist at: {$file}\n";
    }
    
    $conn->close();
    exit(1);
}

$content = $row['content'];
$size = strlen($content);
$md5 = md5($content);

echo "File loaded via DATABASE (real server version):\n";
echo "  Size: {$size} bytes\n";
echo "  MD5: {$md5}\n\n";

// Count our specific fixes
$freeCount = substr_count($content, '$result->free();');
$bind9 = substr_count($content, "bind_param('sssssssss'");
$bind8 = substr_count($content, "bind_param('ssssssss'");
$bind11 = substr_count($content, "bind_param('sssssssssss'");

echo "Fix Analysis:\n";
echo "  \$result->free() calls: {$freeCount}\n";
echo "  bind_param('sssssssss') [9 params]: {$bind9}\n";
echo "  bind_param('ssssssss') [8 params]: {$bind8}\n";
echo "  bind_param('sssssssssss') [11 params - OLD]: {$bind11}\n\n";

// Check line 12094 specifically
$lines = explode("\n", $content);
$line12094 = isset($lines[12093]) ? trim($lines[12093]) : 'NOT FOUND';

echo "Line 12094 check:\n";
echo "  Content: {$line12094}\n\n";

if (strpos($line12094, "bind_param('sssssssss'") !== false) {
    echo "  ✅ Line 12094 is CORRECT (9 parameters)\n\n";
} elseif (strpos($line12094, "bind_param('sssssssssss'") !== false) {
    echo "  ❌ Line 12094 has OLD CODE (11 parameters!)\n";
    echo "     File was NOT uploaded despite auto-sync!\n\n";
} else {
    echo "  ⚠️ Line 12094: Unable to determine\n\n";
}

// Final verdict
echo "=== VERDICT ===\n";
if ($freeCount >= 15 && $bind9 >= 3 && $bind11 == 0) {
    echo "✅ File IS correctly updated on server.\n";
    echo "   PROBLEM: OPcache is serving old version.\n";
    echo "   SOLUTION: Clear OPcache (see instructions).\n";
} elseif ($bind11 > 0) {
    echo "❌ Server has OLD version with bugs!\n";
    echo "   Found {$bind11} occurrences of old 11-parameter bind_param.\n";
    echo "   SFTP auto-sync FAILED or file reverted.\n";
    echo "   SOLUTION: Manually upload ProjectHandler.php\n";
} else {
    echo "⚠️ Partial update detected.\n";
    echo "   Some fixes present but incomplete.\n";
    echo "   SOLUTION: Re-upload ProjectHandler.php\n";
}

$conn->close();
?>

<?php
echo "=== CALLBACK TEST RESULTS ===\n\n";

// Check for detailed log
$detailedLog = __DIR__ . "/callback_detailed.log";
if (file_exists($detailedLog)) {
    echo "📋 Detailed callback log:\n";
    echo file_get_contents($detailedLog);
} else {
    echo "ℹ️ No detailed callback log found\n";
}

// Check for standard monitor log
$monitorLog = __DIR__ . "/callback_monitor.log";
if (file_exists($monitorLog)) {
    echo "\n📱 Standard monitor log:\n";
    $content = file_get_contents($monitorLog);
    $lines = explode("\n", trim($content));
    $recent = array_slice($lines, -10);
    foreach ($recent as $line) {
        if (!empty($line)) {
            echo "$line\n";
        }
    }
} else {
    echo "\nℹ️ No standard monitor log found\n";
}

echo "\n=== ANALYSIS ===\n";
echo "Look for:\n";
echo "• What callback data is actually being sent\n";
echo "• Whether it matches expected patterns\n";
echo "• Any encoding or format issues\n";
?>
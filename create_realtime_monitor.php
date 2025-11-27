<?php

echo "=== REAL-TIME MONITORING FOR SITE INSPECTION BUTTON ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Create a comprehensive monitoring script
$monitorScript = '<?php

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "=== LIVE MONITORING STARTED ===\n";
echo "Time: " . date("Y-m-d H:i:s") . "\n\n";

// List of log files to monitor
$logFiles = [
    "telegram_bot_error.log",
    "webhook_error.log", 
    "error.log",
    "debug.log"
];

$lastPositions = [];
$foundFiles = [];

// Initialize positions for existing files
foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $lastPositions[$logFile] = filesize($logFile);
        $foundFiles[] = $logFile;
        echo "✅ Monitoring: $logFile\n";
    }
}

if (empty($foundFiles)) {
    echo "❌ No log files found. Creating a test log entry...\n";
    error_log("TEST: Site inspection monitoring started at " . date("Y-m-d H:i:s"));
    echo "Check for error.log file after this test.\n";
}

echo "\n🎯 READY! Press the \"Проверка монтажной площадки\" button in the bot NOW!\n";
echo "================================================\n\n";

$startTime = time();
$maxWaitTime = 120; // Wait for 2 minutes

while (time() - $startTime < $maxWaitTime) {
    foreach ($foundFiles as $logFile) {
        if (file_exists($logFile)) {
            $currentSize = filesize($logFile);
            if ($currentSize > $lastPositions[$logFile]) {
                // Read new lines
                $handle = fopen($logFile, "r");
                fseek($handle, $lastPositions[$logFile]);
                
                $newContent = "";
                while (($line = fgets($handle)) !== false) {
                    $newContent .= $line;
                }
                fclose($handle);
                
                // Check for relevant content
                $relevantLines = [];
                $lines = explode("\n", $newContent);
                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;
                    
                    if (strpos($line, "АналФунд") !== false || 
                        strpos($line, "ПроверкаМонтажнойПлощадки") !== false ||
                        strpos($line, "createTabelMessage") !== false ||
                        strpos($line, "SITE INSPECTION") !== false ||
                        strpos($line, "Запись в табель от") !== false ||
                        strpos($line, "cat_РЁ") !== false ||
                        strpos($line, "cat_ШМР") !== false ||
                        strpos($line, "TabelEntry") !== false ||
                        strpos($line, "addEntry") !== false) {
                        $relevantLines[] = $line;
                    }
                }
                
                if (!empty($relevantLines)) {
                    echo "\n🔥 NEW ACTIVITY DETECTED in $logFile:\n";
                    echo "=====================================\n";
                    foreach ($relevantLines as $line) {
                        echo $line . "\n";
                    }
                    echo "=====================================\n\n";
                }
                
                $lastPositions[$logFile] = $currentSize;
            }
        }
    }
    
    // Check for new log files that might have been created
    foreach ($logFiles as $logFile) {
        if (!in_array($logFile, $foundFiles) && file_exists($logFile)) {
            $foundFiles[] = $logFile;
            $lastPositions[$logFile] = 0; // Start from beginning of new file
            echo "📁 New log file detected: $logFile\n";
        }
    }
    
    usleep(500000); // 0.5 second intervals
}

echo "\n⏰ Monitoring period ended. If no logs appeared:\n";
echo "1. The button might not be triggering our code paths\n";
echo "2. Logs might be in a different location\n";
echo "3. There might be a different createTabelMessage method being used\n";
echo "4. The issue might be client-side caching\n\n";

echo "💡 Alternative approach: Check if there are any other PHP files with createTabelMessage:\n";
$files = glob("**/*.php", GLOB_BRACE);
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, "Запись в табель от") !== false && strpos($content, "function") !== false) {
            echo "⚠️  Found old format in: $file\n";
        }
    }
}
';

file_put_contents('real_time_monitor.php', $monitorScript);
echo "✅ Created real_time_monitor.php\n\n";

echo "NEXT STEPS:\n";
echo "===========\n";
echo "1. Run: php real_time_monitor.php\n";
echo "2. IMMEDIATELY after starting the monitor, press the 'Проверка монтажной площадки' button\n";
echo "3. Watch the output for any relevant log entries\n";
echo "4. If no logs appear, we'll need to check for alternative code paths\n\n";

echo "This will help us identify exactly which code is being executed!\n";
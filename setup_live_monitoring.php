<?php
/**
 * Live callback monitoring - Safety protocol implementation
 * This will show us exactly what's happening when you click buttons
 */

echo "=== ENABLE LIVE CALLBACK MONITORING ===\n\n";

// First, let's check what the actual showProjectControlSettings method generates
$handlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
$content = file_get_contents($handlerFile);

echo "1. Checking showProjectControlSettings method for button generation...\n";
if (strpos($content, 'function showProjectControlSettings') !== false) {
    echo "   ✓ Method exists\n";
    
    // Look for callback_data generation patterns
    $patterns = [
        'proj_ctrl_cfg_group_',
        'proj_ctrl_cfg_chat_',
        'proj_ctrl_cfg_types_',
        'proj_ctrl_cfg_mode_',
        'proj_ctrl_test_'
    ];
    
    foreach ($patterns as $pattern) {
        if (strpos($content, $pattern) !== false) {
            echo "   ✓ Generates callback: $pattern\n";
        } else {
            echo "   ✗ Missing callback: $pattern\n";
        }
    }
} else {
    echo "   ✗ Method not found\n";
}

echo "\n2. Setting up enhanced callback monitoring...\n";

// Create a more detailed monitoring script
$monitorScript = '<?php
// Enhanced callback monitor - shows all callback details
$logFile = __DIR__ . "/callback_detailed.log";

// Get current timestamp
$timestamp = date("Y-m-d H:i:s");

// Read the callback data from URL or POST
$callback_data = $_GET["callback_data"] ?? $_POST["callback_data"] ?? "UNKNOWN";
$user_id = $_GET["user_id"] ?? $_POST["user_id"] ?? "UNKNOWN";

// Log detailed information
$logEntry = "[$timestamp] DETAILED CALLBACK\n";
$logEntry .= "  User ID: $user_id\n";
$logEntry .= "  Callback Data: \'$callback_data\'\n";
$logEntry .= "  Data Length: " . strlen($callback_data) . " bytes\n";
$logEntry .= "  Data Hex: " . bin2hex($callback_data) . "\n";

// Test pattern matching
$patterns = [
    "proj_ctrl_set_" => "Project Settings",
    "proj_ctrl_cfg_group_" => "Group Configuration", 
    "proj_ctrl_cfg_chat_" => "Chat Configuration",
    "proj_ctrl_cfg_types_" => "Types Configuration",
    "proj_ctrl_cfg_mode_" => "Mode Configuration",
    "proj_ctrl_test_" => "Test Notification"
];

foreach ($patterns as $pattern => $description) {
    if (strpos($callback_data, $pattern) === 0) {
        $logEntry .= "  ✓ MATCHES: $description ($pattern)\n";
        $remaining = str_replace($pattern, "", $callback_data);
        $logEntry .= "  Project Code: \'$remaining\'\n";
        break;
    }
}

$logEntry .= "  Raw $_GET: " . print_r($_GET, true);
$logEntry .= "  Raw $_POST: " . print_r($_POST, true);
$logEntry .= "\n---\n\n";

file_put_contents($logFile, $logEntry, FILE_APPEND);

echo "Callback logged: $callback_data";
?>';

file_put_contents(__DIR__ . '/monitor_callback.php', $monitorScript);
echo "   ✓ Created enhanced monitoring script\n";

echo "\n3. Instructions for testing:\n";
echo "   🤖 Go to your Telegram bot\n";
echo "   📱 Navigate to: Projects → My Projects → Projects Under Control\n";
echo "   ⚙️ Click: Notification Settings\n";
echo "   🎯 Click on your project (⚙️ ОИ789)\n";
echo "   📊 Try clicking any configuration button\n";

echo "\n4. After testing, run this command to see results:\n";
echo "   php check_callback_results.php\n";

// Create the results checker
$resultChecker = '<?php
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
?>';

file_put_contents(__DIR__ . '/check_callback_results.php', $resultChecker);
echo "   ✓ Created results checker script\n";

echo "\n5. Testing immediate callback pattern matching:\n";
$testCallbacks = [
    'proj_ctrl_set_ОИ789',
    'proj_ctrl_cfg_group_ОИ789',
    'proj_ctrl_cfg_chat_ОИ789'
];

foreach ($testCallbacks as $callback) {
    echo "   Testing: $callback\n";
    
    if (str_starts_with($callback, 'proj_ctrl_set_')) {
        echo "     ✓ Would call showProjectControlSettings\n";
    } elseif (str_starts_with($callback, 'proj_ctrl_cfg_')) {
        echo "     ✓ Would call handleConfigurationCallbacks\n";
        
        if (str_starts_with($callback, 'proj_ctrl_cfg_group_')) {
            echo "     ✓ Would call showGroupConfiguration\n";
        } elseif (str_starts_with($callback, 'proj_ctrl_cfg_chat_')) {
            echo "     ✓ Would call showChatConfiguration\n";
        }
    } else {
        echo "     ✗ Would show unknown command error\n";
    }
}

echo "\n🎯 NEXT STEP: Please test the bot and then run:\n";
echo "   php check_callback_results.php\n";
echo "\nThis will show us exactly what callback data your buttons are generating.\n";

?>
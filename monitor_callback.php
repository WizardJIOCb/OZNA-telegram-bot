<?php
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
$logEntry .= "  Callback Data: '$callback_data'\n";
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
        $logEntry .= "  Project Code: '$remaining'\n";
        break;
    }
}

$logEntry .= "  Raw $_GET: " . print_r($_GET, true);
$logEntry .= "  Raw $_POST: " . print_r($_POST, true);
$logEntry .= "\n---\n\n";

file_put_contents($logFile, $logEntry, FILE_APPEND);

echo "Callback logged: $callback_data";
?>
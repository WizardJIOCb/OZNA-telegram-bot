<?php
// Simple callback test - just log any POST request
$logFile = __DIR__ . "/webhook_test.log";
$timestamp = date("Y-m-d H:i:s");

$logEntry = "[$timestamp] WEBHOOK REQUEST\n";
$logEntry .= "  Method: " . ($_SERVER["REQUEST_METHOD"] ?? "UNKNOWN") . "\n";
$logEntry .= "  Content-Type: " . ($_SERVER["CONTENT_TYPE"] ?? "UNKNOWN") . "\n";
$logEntry .= "  User-Agent: " . ($_SERVER["HTTP_USER_AGENT"] ?? "UNKNOWN") . "\n";

$input = file_get_contents("php://input");
$logEntry .= "  Input length: " . strlen($input) . " bytes\n";

if (!empty($input)) {
    $data = json_decode($input, true);
    if ($data) {
        $logEntry .= "  JSON decoded successfully\n";
        if (isset($data["callback_query"])) {
            $logEntry .= "  CALLBACK QUERY FOUND!\n";
            $logEntry .= "  Callback data: " . ($data["callback_query"]["data"] ?? "UNKNOWN") . "\n";
            $logEntry .= "  User ID: " . ($data["callback_query"]["from"]["id"] ?? "UNKNOWN") . "\n";
        }
    } else {
        $logEntry .= "  JSON decode failed\n";
        $logEntry .= "  Raw input: " . substr($input, 0, 200) . "\n";
    }
}

$logEntry .= "\n---\n\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Continue to regular hook.php
include __DIR__ . "/hook.php";
?>
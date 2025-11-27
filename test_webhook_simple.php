<?php
// Simple test hook that just logs everything it receives
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/test_webhook.log');

// Log that the webhook was called
error_log("=== TEST WEBHOOK CALLED AT " . date('Y-m-d H:i:s') . " ===");

// Get the input
$input = file_get_contents('php://input');
error_log("Input received: " . $input);

// Try to decode JSON
if ($input) {
    $json = json_decode($input, true);
    if ($json) {
        error_log("JSON decoded successfully");
        
        // Check for callback query
        if (isset($json['callback_query'])) {
            $callback_data = $json['callback_query']['data'] ?? 'NO_DATA';
            error_log("CALLBACK QUERY DETECTED: " . $callback_data);
            
            // Specifically look for warranty equipment
            if (strpos($callback_data, 'РабГарантии') !== false) {
                error_log("*** WARRANTY EQUIPMENT CALLBACK DETECTED: " . $callback_data . " ***");
            }
        }
        
        // Check for message
        if (isset($json['message'])) {
            $text = $json['message']['text'] ?? 'NO_TEXT';
            error_log("MESSAGE DETECTED: " . $text);
        }
    } else {
        error_log("Failed to decode JSON");
    }
} else {
    error_log("No input received");
}

// Always return success to Telegram
http_response_code(200);
echo "OK";

error_log("=== TEST WEBHOOK FINISHED ===");
?>
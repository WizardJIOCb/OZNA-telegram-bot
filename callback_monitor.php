<?php
/**
 * Live Callback Monitor - Place this at the beginning of CallbackqueryCommand execute()
 */

// Add this logging at the start of CallbackqueryCommand execute() method
$logFile = __DIR__ . "/../../callback_monitor.log";
$timestamp = date("Y-m-d H:i:s");
$userId = $callback_query->getFrom()->getId();
$data = $callback_query->getData();
$messageText = "No message";

if ($callback_query->getMessage()) {
    $messageText = substr($callback_query->getMessage()->getText(), 0, 100);
}

$logEntry = "[$timestamp] USER: $userId | DATA: $data | MSG: $messageText\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Also log to main log
error_log("CALLBACK RECEIVED: User $userId clicked $data");

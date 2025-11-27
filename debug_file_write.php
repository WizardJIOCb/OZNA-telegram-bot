<?php

// Debug file writing
echo "Debugging file write...\n";

// Test what json_encode produces for an empty array
$emptyArray = [];
$jsonData = json_encode($emptyArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "JSON for empty array: " . $jsonData . "\n";
echo "JSON length: " . strlen($jsonData) . "\n";
echo "JSON hex: " . bin2hex($jsonData) . "\n";

// Write to file
$filePath = __DIR__ . '/data/test_file.json';
file_put_contents($filePath, $jsonData);

// Read back
$content = file_get_contents($filePath);
echo "File content: " . $content . "\n";
echo "File content length: " . strlen($content) . "\n";
echo "File content hex: " . bin2hex($content) . "\n";

// Check if valid JSON
$data = json_decode($content, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "File contains valid JSON\n";
} else {
    echo "File contains invalid JSON: " . json_last_error_msg() . "\n";
}

echo "Done.\n";
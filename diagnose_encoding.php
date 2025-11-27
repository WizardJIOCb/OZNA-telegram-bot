<?php

// Diagnostic script to check encoding issues

echo "=== Encoding Diagnosis ===\n";

// Check file encoding
$filePath = __DIR__ . '/data/access_requests.json';
echo "File path: {$filePath}\n";
echo "File exists: " . (file_exists($filePath) ? 'Yes' : 'No') . "\n";

if (file_exists($filePath)) {
    $data = file_get_contents($filePath);
    echo "Raw data length: " . strlen($data) . " bytes\n";
    echo "Data preview: " . substr($data, 0, 100) . "\n";
    
    // Check encoding
    echo "Is UTF-8: " . (mb_check_encoding($data, 'UTF-8') ? 'Yes' : 'No') . "\n";
    echo "Is ASCII: " . (mb_check_encoding($data, 'ASCII') ? 'Yes' : 'No') . "\n";
    
    // Try to detect encoding
    $detected = mb_detect_encoding($data, ['UTF-8', 'Windows-1251', 'CP866', 'KOI8-R'], true);
    echo "Detected encoding: " . ($detected ?: 'Unknown') . "\n";
    
    // Decode JSON
    $requests = json_decode($data, true);
    echo "JSON decode success: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON error: " . json_last_error_msg() . "\n";
    }
    
    if (is_array($requests)) {
        echo "Number of requests: " . count($requests) . "\n";
        foreach ($requests as $index => $request) {
            echo "Request #{$index}:\n";
            foreach ($request as $key => $value) {
                echo "  {$key}: " . (is_string($value) ? $value : json_encode($value)) . "\n";
                if (is_string($value)) {
                    echo "    UTF-8 valid: " . (mb_check_encoding($value, 'UTF-8') ? 'Yes' : 'No') . "\n";
                }
            }
        }
    }
}

echo "\n=== PHP Info ===\n";
echo "Default charset: " . ini_get('default_charset') . "\n";
echo "Internal encoding: " . mb_internal_encoding() . "\n";

?>
<?php
// Script to fix encoding issues in access_requests.json

echo "=== Fixing Access Requests Encoding ===\n";

$filePath = __DIR__ . '/data/access_requests.json';
echo "File path: {$filePath}\n";

if (!file_exists($filePath)) {
    echo "File not found!\n";
    exit(1);
}

// Read the corrupted file
$data = file_get_contents($filePath);
echo "Original data length: " . strlen($data) . "\n";

// Try to fix encoding
echo "Attempting to fix encoding...\n";

// Method 1: Auto detection
$fixedData1 = mb_convert_encoding($data, 'UTF-8', 'auto');
echo "After auto conversion length: " . strlen($fixedData1) . "\n";

// Test JSON decode
$test1 = json_decode($fixedData1, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "Auto conversion successful!\n";
    $fixedData = $fixedData1;
} else {
    echo "Auto conversion failed: " . json_last_error_msg() . "\n";
    
    // Method 2: UTF-8 to UTF-8
    $fixedData2 = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
    echo "After UTF-8 conversion length: " . strlen($fixedData2) . "\n";
    
    $test2 = json_decode($fixedData2, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "UTF-8 conversion successful!\n";
        $fixedData = $fixedData2;
    } else {
        echo "UTF-8 conversion failed: " . json_last_error_msg() . "\n";
        
        // Method 3: Windows-1251 to UTF-8
        $fixedData3 = mb_convert_encoding($data, 'UTF-8', 'Windows-1251');
        echo "After Windows-1251 conversion length: " . strlen($fixedData3) . "\n";
        
        $test3 = json_decode($fixedData3, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "Windows-1251 conversion successful!\n";
            $fixedData = $fixedData3;
        } else {
            echo "Windows-1251 conversion failed: " . json_last_error_msg() . "\n";
            $fixedData = $data; // Use original if all methods fail
        }
    }
}

// Decode and re-encode with proper UTF-8
$requests = json_decode($fixedData, true);
if (is_array($requests)) {
    echo "Successfully decoded " . count($requests) . " requests\n";
    
    // Fix encoding for all text fields
    foreach ($requests as $index => $request) {
        foreach ($request as $key => $value) {
            if (is_string($value)) {
                $requests[$index][$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
                echo "Fixed encoding for request #{$index}, field '{$key}': {$requests[$index][$key]}\n";
            }
        }
    }
    
    // Re-encode with proper UTF-8
    $finalData = json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $finalData = mb_convert_encoding($finalData, 'UTF-8', 'UTF-8');
    
    // Save the fixed data
    file_put_contents($filePath, $finalData);
    echo "Fixed file saved successfully!\n";
    
    // Verify the fix
    $verifyData = file_get_contents($filePath);
    $verifyRequests = json_decode($verifyData, true);
    if (is_array($verifyRequests)) {
        echo "Verification successful! File now contains " . count($verifyRequests) . " properly encoded requests.\n";
    } else {
        echo "Verification failed!\n";
    }
} else {
    echo "Failed to decode requests array\n";
}

echo "=== Fix Complete ===\n";
?>
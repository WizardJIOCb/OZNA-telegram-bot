<?php
// Check access requests file encoding

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Checking access requests file...\n";

try {
    $filePath = __DIR__ . '/data/access_requests.json';
    
    echo "File path: {$filePath}\n";
    echo "File exists: " . (file_exists($filePath) ? 'yes' : 'no') . "\n";
    
    if (file_exists($filePath)) {
        // Get file info
        $fileInfo = stat($filePath);
        echo "File size: " . $fileInfo['size'] . " bytes\n";
        echo "File modified: " . date('Y-m-d H:i:s', $fileInfo['mtime']) . "\n";
        
        // Read raw file content
        $rawContent = file_get_contents($filePath);
        echo "Raw content length: " . strlen($rawContent) . " bytes\n";
        echo "Raw content (first 200 chars):\n";
        echo mb_substr($rawContent, 0, 200) . "\n";
        
        // Check encoding
        echo "Content encoding detection:\n";
        echo "  mb_detect_encoding: " . mb_detect_encoding($rawContent) . "\n";
        echo "  Is UTF-8: " . (mb_check_encoding($rawContent, 'UTF-8') ? 'yes' : 'no') . "\n";
        
        // Try to decode JSON
        $decoded = json_decode($rawContent, true);
        echo "JSON decode result: " . (json_last_error() === JSON_ERROR_NONE ? 'success' : 'failed') . "\n";
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON error: " . json_last_error_msg() . "\n";
        } else {
            echo "Decoded data:\n";
            print_r($decoded);
        }
    } else {
        echo "File does not exist!\n";
    }
    
    echo "\nCheck completed!\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
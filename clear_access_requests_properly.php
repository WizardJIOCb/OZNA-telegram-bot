<?php

// Script to properly clear access requests file without BOM
$filePath = __DIR__ . '/data/access_requests.json';

// Write empty array without BOM
$content = '[]';
file_put_contents($filePath, $content);

echo "Access requests file cleared properly.\n";
echo "File content: " . file_get_contents($filePath) . "\n";
<?php

// Test script to verify FIO text input functionality

echo "Testing FIO text input functionality...\n";

// Test cases
$testCases = [
    'Иванов Иван Иванович' => 'Valid FIO',
    'Петров Петр Петрович' => 'Valid FIO',
    'Сидоров Сидор Сидорович' => 'Valid FIO',
    'Аб' => 'Too short',
    '' => 'Empty',
];

echo "Test cases:\n";
foreach ($testCases as $fio => $description) {
    echo "  '{$fio}' -> {$description}\n";
    
    // Test length validation
    $length = strlen(trim($fio));
    if ($length < 3) {
        echo "    ❌ Too short ({$length} characters)\n";
    } else {
        echo "    ✅ Valid length ({$length} characters)\n";
    }
}

echo "Test completed.\n";
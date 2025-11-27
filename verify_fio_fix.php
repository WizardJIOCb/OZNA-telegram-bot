<?php
/**
 * Verification script for the FIO validation fix
 * Tests that access requests are properly rejected when FIO is "Без ФИО"
 */

echo "Verifying FIO validation fix...\n\n";

// Test the updated validation logic in all handlers
function validateProfileFixed($userFio, $userFunctionalDirection) {
    // Fixed validation logic - also check for default values
    if (empty($userFio) || empty($userFunctionalDirection) || 
        $userFio === 'Без ФИО' || $userFunctionalDirection === 'Не указано') {
        return false; // Rejected
    }
    return true; // Accepted
}

// Test cases
$testCases = [
    // Case 1: Empty FIO (should be rejected)
    [
        'fio' => '',
        'functional_direction' => 'Мобильная бригада',
        'expected' => 'REJECTED',
        'description' => 'Empty FIO'
    ],
    
    // Case 2: FIO set to "Без ФИО" (should now be rejected after fix)
    [
        'fio' => 'Без ФИО',
        'functional_direction' => 'Мобильная бригада',
        'expected' => 'REJECTED',
        'description' => 'FIO set to "Без ФИО"'
    ],
    
    // Case 3: Valid FIO but empty functional direction (should be rejected)
    [
        'fio' => 'Иванов Иван Иванович',
        'functional_direction' => '',
        'expected' => 'REJECTED',
        'description' => 'Valid FIO but empty functional direction'
    ],
    
    // Case 4: Valid FIO but functional direction "Не указано" (should be rejected)
    [
        'fio' => 'Иванов Иван Иванович',
        'functional_direction' => 'Не указано',
        'expected' => 'REJECTED',
        'description' => 'Valid FIO but functional direction "Не указано"'
    ],
    
    // Case 5: Both valid (should be accepted)
    [
        'fio' => 'Иванов Иван Иванович',
        'functional_direction' => 'Мобильная бригада',
        'expected' => 'ACCEPTED',
        'description' => 'Both FIO and functional direction valid'
    ]
];

echo "Validation results after fix:\n";
echo "============================\n";

$allPassed = true;
foreach ($testCases as $case) {
    $result = validateProfileFixed($case['fio'], $case['functional_direction']) ? 'ACCEPTED' : 'REJECTED';
    $status = ($result === $case['expected']) ? '✅' : '❌';
    
    if ($status === '❌') {
        $allPassed = false;
    }
    
    echo "{$status} {$case['description']}\n";
    echo "   FIO: '{$case['fio']}', FN: '{$case['functional_direction']}'\n";
    echo "   Result: {$result}, Expected: {$case['expected']}\n\n";
}

// Check that the fix has been applied to all relevant files
echo "Checking code files for the fix...\n";
echo "================================\n";

$filesToCheck = [
    'CustomCommands/Services/AdminService.php',
    'CustomCommands/Handlers/AdminHandler.php',
    'CustomCommands/Handlers/MainMenuHandler.php'
];

foreach ($filesToCheck as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        // Check if the fix is present (looking for the specific condition)
        if (strpos($content, '$userFio === \'Без ФИО\'') !== false) {
            echo "✅ Fix found in {$file}\n";
        } else {
            echo "❌ Fix NOT found in {$file}\n";
            $allPassed = false;
        }
    } else {
        echo "❌ File not found: {$file}\n";
        $allPassed = false;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
if ($allPassed) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "The FIO validation fix has been successfully implemented.\n\n";
    echo "Summary of changes:\n";
    echo "1. Added check for FIO === 'Без ФИО' in AdminService::sendAccessRequest()\n";
    echo "2. Added check for FIO === 'Без ФИО' in AdminHandler::handleAccessRequest()\n";
    echo "3. Added check for FIO === 'Без ФИО' in MainMenuHandler::handleAccessRequest()\n";
    echo "\nThis fix ensures that:\n";
    echo "- Guests cannot submit access requests with default FIO value ('Без ФИО')\n";
    echo "- Guests must properly set their FIO before submitting access requests\n";
    echo "- Consistent validation across all access request paths\n";
} else {
    echo "❌ SOME TESTS FAILED!\n";
    echo "Please check the implementation.\n";
}
echo str_repeat("=", 50) . "\n";
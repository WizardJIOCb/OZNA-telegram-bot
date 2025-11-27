<?php
/**
 * Test script to verify the FIO validation issue
 * Testing if access requests are allowed when FIO is "Без ФИО"
 */

echo "Testing FIO validation issue...\n\n";

// Test cases
$testCases = [
    // Case 1: Empty FIO (should be rejected)
    [
        'fio' => '',
        'functional_direction' => 'Мобильная бригада',
        'expected' => 'REJECTED',
        'description' => 'Empty FIO'
    ],
    
    // Case 2: FIO set to "Без ФИО" (current issue - should be rejected but might be allowed)
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

// Simulate the current validation logic from AdminService
function validateProfile($userFio, $userFunctionalDirection) {
    // Current validation logic
    if (empty($userFio) || empty($userFunctionalDirection) || $userFunctionalDirection === 'Не указано') {
        return false; // Rejected
    }
    return true; // Accepted
}

echo "Current validation results:\n";
echo "==========================\n";

foreach ($testCases as $case) {
    $result = validateProfile($case['fio'], $case['functional_direction']) ? 'ACCEPTED' : 'REJECTED';
    $status = ($result === $case['expected']) ? '✅' : '❌';
    
    echo "{$status} {$case['description']}\n";
    echo "   FIO: '{$case['fio']}', FN: '{$case['functional_direction']}'\n";
    echo "   Result: {$result}, Expected: {$case['expected']}\n\n";
}

echo "Issue identified:\n";
echo "The current validation logic allows access requests when FIO is 'Без ФИО'\n";
echo "because it only checks for empty() but not specifically for the 'Без ФИО' value.\n\n";

// Test the fixed validation logic
function validateProfileFixed($userFio, $userFunctionalDirection) {
    // Fixed validation logic - also check for default values
    if (empty($userFio) || empty($userFunctionalDirection) || 
        $userFio === 'Без ФИО' || $userFunctionalDirection === 'Не указано') {
        return false; // Rejected
    }
    return true; // Accepted
}

echo "Fixed validation results:\n";
echo "=======================\n";

foreach ($testCases as $case) {
    $result = validateProfileFixed($case['fio'], $case['functional_direction']) ? 'ACCEPTED' : 'REJECTED';
    $status = ($result === $case['expected']) ? '✅' : '❌';
    
    echo "{$status} {$case['description']}\n";
    echo "   FIO: '{$case['fio']}', FN: '{$case['functional_direction']}'\n";
    echo "   Result: {$result}, Expected: {$case['expected']}\n\n";
}

echo "Fix summary:\n";
echo "Added check for FIO === 'Без ФИО' in the validation logic\n";
echo "This will prevent access requests when FIO is not properly set\n";
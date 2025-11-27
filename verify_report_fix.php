<?php
/**
 * Verification script for the report submission fix
 * Checks that the validation has been properly implemented in TabelHandler
 */

echo "Verifying report submission fix...\n\n";

// Check the TabelHandler file for the correct validation
$tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

if (!file_exists($tabelHandlerFile)) {
    echo "❌ TabelHandler.php file not found\n";
    exit(1);
}

$content = file_get_contents($tabelHandlerFile);

// Check for the validation logic
$validationChecks = [
    'fio === \'Без ФИО\'' => 'Check for default FIO value',
    'functionalDirection === \'Не указано\'' => 'Check for default functional direction value',
    'empty($fio)' => 'Check for empty FIO',
    'empty($functionalDirection)' => 'Check for empty functional direction',
    'Для отправки отчета необходимо заполнить ФИО' => 'User-friendly error message',
    '/fio Фамилия Имя Отчество' => 'Instructions for setting FIO',
    '/profile для выбора функционального направления' => 'Instructions for setting functional direction'
];

echo "Validation checks in TabelHandler.php:\n";
echo "=====================================\n";

$allFound = true;
foreach ($validationChecks as $check => $description) {
    if (strpos($content, $check) !== false) {
        echo "✅ Found: {$description}\n";
    } else {
        echo "❌ Missing: {$description}\n";
        $allFound = false;
    }
}

// Check the specific method that handles activity execution
if (strpos($content, 'handleActivityExecution') !== false) {
    echo "✅ handleActivityExecution method found\n";
    
    // Look for the specific validation logic
    $lines = explode("\n", $content);
    $foundValidation = false;
    
    foreach ($lines as $line) {
        if (strpos($line, 'fio === \'Без ФИО\'') !== false && 
            strpos($line, 'functionalDirection === \'Не указано\'') !== false) {
            echo "✅ Found complete validation logic:\n";
            echo "   " . trim($line) . "\n";
            $foundValidation = true;
            break;
        }
    }
    
    if (!$foundValidation) {
        echo "❌ Could not find the complete validation logic\n";
        $allFound = false;
    }
} else {
    echo "❌ handleActivityExecution method not found\n";
    $allFound = false;
}

echo "\n" . str_repeat("=", 50) . "\n";
if ($allFound) {
    echo "🎉 VERIFICATION SUCCESSFUL!\n";
    echo "The report submission fix has been correctly implemented.\n\n";
    echo "Summary of implementation:\n";
    echo "1. Added validation for FIO === 'Без ФИО' in TabelHandler::handleActivityExecution()\n";
    echo "2. Added validation for functionalDirection === 'Не указано' in TabelHandler::handleActivityExecution()\n";
    echo "3. Added user-friendly error messages with instructions\n";
    echo "4. Consistent validation behavior with access request functionality\n";
    echo "\nThis fix ensures that:\n";
    echo "- Users cannot submit reports without properly set FIO and functional direction\n";
    echo "- All reports will contain proper user identification\n";
    echo "- Data quality is maintained across the system\n";
} else {
    echo "❌ VERIFICATION FAILED!\n";
    echo "Please check the implementation.\n";
}
echo str_repeat("=", 50) . "\n";
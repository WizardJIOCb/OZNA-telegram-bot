<?php
// Final test to verify server is using the updated code

echo "=== FINAL SERVER TEST ===\n\n";

$file = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$content = file_get_contents($file);

// Check if our fix is present
$hasBasicCommissioningInExecution = strpos($content, "'ТабИнстр' => 'Инструктаж'") !== false;
$hasBasicCommissioningInConfirmation = strpos($content, "preg_match('/^cat_(ТабИнстр|ТабДорога|") !== false;

echo "1. Checking if isActivityExecution has basic commissioning activities:\n";
echo "   Found 'ТабИнстр' => 'Инструктаж': " . ($hasBasicCommissioningInExecution ? "✓ YES" : "✗ NO") . "\n\n";

echo "2. Checking if isActivityConfirmation has commissioning pattern:\n";
echo "   Found regex pattern with ТабИнстр: " . ($hasBasicCommissioningInConfirmation ? "✓ YES" : "✗ NO") . "\n\n";

// Count how many times ТабИнстр appears
$instructionCount = substr_count($content, 'ТабИнстр');
echo "3. Total occurrences of 'ТабИнстр' in file: {$instructionCount}\n\n";

// Check the exact line where the error message is generated
$errorLinePattern = "неизвестная команда табеля";
$hasErrorMessage = strpos($content, $errorLinePattern) !== false;
echo "4. Error message 'неизвестная команда табеля' found: " . ($hasErrorMessage ? "✓ YES" : "✗ NO") . "\n";

if ($hasErrorMessage) {
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, $errorLinePattern) !== false) {
            echo "   Found at line: " . ($lineNum + 1) . "\n";
            echo "   Line content: " . trim($line) . "\n";
        }
    }
}

echo "\n=== METHOD ANALYSIS ===\n\n";

// Extract the isActivityExecution method to verify its content
$executionMethodStart = strpos($content, 'private function isActivityExecution');
$executionMethodEnd = strpos($content, 'private function handleActivityExecution', $executionMethodStart);

if ($executionMethodStart !== false && $executionMethodEnd !== false) {
    $executionMethod = substr($content, $executionMethodStart, $executionMethodEnd - $executionMethodStart);
    
    echo "5. isActivityExecution method contains commissioning activities:\n";
    $hasCommissioningInMethod = strpos($executionMethod, '$basicCommissioningActivities') !== false;
    echo "   Found \$basicCommissioningActivities: " . ($hasCommissioningInMethod ? "✓ YES" : "✗ NO") . "\n";
    
    if ($hasCommissioningInMethod) {
        echo "   ✓ Method should handle cat_ТабИнстр_yes correctly\n";
    } else {
        echo "   ✗ Method is missing commissioning activities handling\n";
    }
}

echo "\n=== CONCLUSION ===\n\n";

if ($hasBasicCommissioningInExecution && $hasBasicCommissioningInConfirmation) {
    echo "✓ All fixes are in place in the server file.\n";
    echo "✓ cat_ТабИнстр_yes should be handled correctly.\n\n";
    echo "If the error still persists, possible causes:\n";
    echo "1. 🔄 Server needs restart to reload PHP files\n";
    echo "2. 🗂️ Server is using a different file path\n";
    echo "3. 🔀 There's another routing issue or method override\n";
    echo "4. 📝 Log files might show more specific error details\n\n";
    echo "RECOMMENDATION: Restart the web server/PHP process\n";
} else {
    echo "✗ Some fixes are missing from the server file.\n";
    echo "The code needs to be updated before testing.\n";
}
?>
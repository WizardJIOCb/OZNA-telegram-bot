<?php
// Test to check if electrical equipment activities are handled correctly

echo "Testing electrical equipment callback processing\n";

// Read the TabelHandler file
$content = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Check if the short callback format is properly handled in isActivityExecution method
$pattern = "/Handle short callback format for electrical equipment first/";
if (preg_match($pattern, $content)) {
    echo "OK: Short callback format handling found in isActivityExecution method\n";
} else {
    echo "ERROR: Short callback format handling NOT found in isActivityExecution method\n";
}

// Check if the short callback format is properly handled in handleActivityExecution method
$pattern = "/Handle short callback format for electrical equipment FIRST/";
if (preg_match($pattern, $content)) {
    echo "OK: Short callback format handling found in handleActivityExecution method\n";
} else {
    echo "ERROR: Short callback format handling NOT found in handleActivityExecution method\n";
}

// Check if the short callback format mappings exist
$pattern = "/'РабПоГр' => 'РабПоГр_ЭлектрОб'/";
if (preg_match($pattern, $content)) {
    echo "OK: Short to full mapping for РабПоГр found\n";
} else {
    echo "ERROR: Short to full mapping for РабПоГр NOT found\n";
}

$pattern = "/'ФормИД' => 'ФормИД_ЭлектрОб'/";
if (preg_match($pattern, $content)) {
    echo "OK: Short to full mapping for ФормИД found\n";
} else {
    echo "ERROR: Short to full mapping for ФормИД NOT found\n";
}

// Check if the confirmation keyboards use the short format
$pattern = "/createConfirmationKeyboard\(\"cat_ТабЭлОб_РабПоГр_yes\"\)/";
if (preg_match($pattern, $content)) {
    echo "OK: Short format confirmation keyboard for РабПоГр found\n";
} else {
    echo "ERROR: Short format confirmation keyboard for РабПоГр NOT found\n";
}

$pattern = "/createConfirmationKeyboard\(\"cat_ТабЭлОб_ФормИД_yes\"\)/";
if (preg_match($pattern, $content)) {
    echo "OK: Short format confirmation keyboard for ФормИД found\n";
} else {
    echo "ERROR: Short format confirmation keyboard for ФормИД NOT found\n";
}

echo "Electrical equipment test completed!\n";
?>
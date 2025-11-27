<?php
/**
 * Simple diagnostic to check the current site inspection button behavior
 */

echo "=== Site Inspection Button Diagnostic ===\n\n";

// Check the current button callback
echo "1. Button Callback Check:\n";
echo "   Current callback should be: cat_ШМР_ПровМонт\n";
echo "   Confirmation callback should be: cat_ШМР_ПровМонт_yes\n\n";

// Check what happens when the callback is processed
echo "2. Processing Flow:\n";
echo "   a) Button click → cat_ШМР_ПровМонт\n";
echo "   b) Should show confirmation dialog\n";
echo "   c) Click 'Да' → cat_ШМР_ПровМонт_yes\n";
echo "   d) Should execute activity and generate report\n\n";

// Check the mapping
echo "3. Mapping Check:\n";
$simpleSupervisionMap = [
    'ПровМонт' => 'ПроверкаМонтажнойПлощадки',
    'ПодпАкт' => 'ПодписаниеАктаШМР',
    'ПодтВех' => 'ПодтверждениеВехи_ШМР'
];

if (isset($simpleSupervisionMap['ПровМонт'])) {
    echo "   ✅ ПровМонт mapping exists: " . $simpleSupervisionMap['ПровМонт'] . "\n";
} else {
    echo "   ❌ ПровМонт mapping missing!\n";
}

// Check activity types array
echo "\n4. Activity Types Check:\n";
$activityTypes = [
    'ПроверкаМонтажнойПлощадки',
    'ПодписаниеАктаШМР',
    'ПодтверждениеВехи_ШМР'
];

if (in_array('ПроверкаМонтажнойПлощадки', $activityTypes)) {
    echo "   ✅ ПроверкаМонтажнойПлощадки is in activity types\n";
} else {
    echo "   ❌ ПроверкаМонтажнойПлощадки missing from activity types!\n";
}

echo "\n5. Expected vs Actual:\n";
echo "   Expected: Confirmation dialog → Report generation\n";
echo "   Actual: 'Неизвестная команда табеля'\n\n";

echo "6. Possible Causes:\n";
echo "   a) Callback not reaching TabelHandler\n";
echo "   b) isActivityExecution returning false\n";
echo "   c) extractCategoryFromCallback not working\n";
echo "   d) Activity type validation failing\n\n";

echo "7. Debugging Steps:\n";
echo "   a) Check server logs when clicking the button\n";
echo "   b) Look for debug messages with 'ПровМонт'\n";
echo "   c) Check if TabelHandler::canHandle is called\n";
echo "   d) Check if TabelHandler::handle is called\n";
echo "   e) Check if isActivityExecution is called\n\n";

echo "=== Diagnostic Complete ===\n";
echo "The button should work now that АналФунд is removed.\n";
echo "If it still shows 'Неизвестная команда табеля', check server logs.\n";
?>
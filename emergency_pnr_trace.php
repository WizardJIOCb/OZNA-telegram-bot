<?php
// Emergency trace script for "Отдел ПНР" button issue

echo "=== EMERGENCY PNR BUTTON TRACE ===\n";

// The exact callback from MessageService
$pnrCallback = 'category_Табель_Пусконаладка';
echo "1. Expected PNR callback: '{$pnrCallback}'\n";
echo "2. Callback bytes: " . bin2hex($pnrCallback) . "\n\n";

// Test CallbackHandlerFactory extractCategory
function extractCategory($callbackData) {
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    return '';
}

$extractedCategory = extractCategory($pnrCallback);
echo "3. Extracted category: '{$extractedCategory}'\n";
echo "4. Category bytes: " . bin2hex($extractedCategory) . "\n\n";

// Test TabelHandler canHandle
function tabelHandlerCanHandle($callbackData) {
    return strpos($callbackData, 'category_Табель') === 0;
}

$canHandle = tabelHandlerCanHandle($pnrCallback);
echo "5. TabelHandler canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n\n";

// Test the specific routing condition
$routingTest = ($extractedCategory === 'Табель_Пусконаладка');
echo "6. Routing condition test: \$category === 'Табель_Пусконаладка'\n";
echo "   Result: " . ($routingTest ? 'MATCH' : 'NO MATCH') . "\n\n";

// Check for potential conflicts with other handlers
echo "7. POTENTIAL CONFLICTS CHECK:\n";
echo "   If other handlers with higher priority can handle this callback,\n";
echo "   they will intercept it before TabelHandler gets a chance.\n\n";

echo "8. HANDLER PRIORITIES TO CHECK:\n";
echo "   - TabelHandler: priority 70 (highest)\n";
echo "   - MainMenuHandler: priority 50\n";
echo "   - ProjectHandler: priority 30\n";
echo "   - Others: lower priorities\n\n";

echo "9. CRITICAL DEBUG POINTS TO ADD:\n";
echo "   Add this to TabelHandler canHandle method:\n";
echo "   if (\$callbackData === 'category_Табель_Пусконаладка') {\n";
echo "       error_log('*** PNR BUTTON DETECTED IN TABEL HANDLER! ***');\n";
echo "   }\n\n";

echo "10. EMERGENCY FIX ATTEMPT:\n";
echo "    If no logs appear for PNR button, add emergency logging\n";
echo "    at the start of ALL handler canHandle methods.\n\n";

echo "NEXT STEP: Add the debug logging above and test the button!\n";
?>
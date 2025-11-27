<?php
// Warranty Equipment Flow Trace

echo "=== TRACING WARRANTY EQUIPMENT CALLBACK FLOW ===\n";

$electricalCallback = 'category_Табель_РабГарантии_ЭлектрОб';
echo "Testing callback: '{$electricalCallback}'\n\n";

// Step 1: Check if TabelHandler canHandle will accept this callback
echo "STEP 1: TabelHandler::canHandle() test\n";
$handlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Find the canHandle method
$canHandleStart = strpos($handlerContent, 'public function canHandle(string $callbackData): bool');
$canHandleEnd = strpos($handlerContent, 'return $canHandle;', $canHandleStart);
$canHandleMethod = substr($handlerContent, $canHandleStart, $canHandleEnd - $canHandleStart + 20);

// Check if our callback matches any of the patterns
if (str_starts_with($electricalCallback, 'category_Табель')) {
    echo "✅ Callback starts with 'category_Табель' - will be handled\n";
} else {
    echo "❌ Callback does NOT start with 'category_Табель'\n";
}

// Step 2: Check routing in routeTabelCallback
echo "\nSTEP 2: routeTabelCallback() routing test\n";

// Find where warranty equipment individual callbacks are handled
if (strpos($handlerContent, "str_starts_with(\$category, 'category_Табель_РабГарантии_')") !== false) {
    echo "✅ Individual warranty equipment routing exists\n";
} else {
    echo "❌ Individual warranty equipment routing missing\n";
}

// Step 3: Check if isActivityConfirmation recognizes warranty equipment
echo "\nSTEP 3: isActivityConfirmation() test\n";
if (strpos($handlerContent, "str_starts_with(\$category, 'category_Табель_РабГарантии_')") !== false) {
    echo "✅ isActivityConfirmation recognizes warranty equipment\n";
} else {
    echo "❌ isActivityConfirmation does NOT recognize warranty equipment\n";
}

// Step 4: Check activity type mapping
echo "\nSTEP 4: Activity type mapping test\n";
$equipmentType = str_replace('category_Табель_РабГарантии_', '', $electricalCallback);
$activityType = 'РабГарантии_' . $equipmentType;
echo "Equipment type: '{$equipmentType}'\n";
echo "Activity type: '{$activityType}'\n";

$tabelEntryContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\TabelEntry.php');
if (strpos($tabelEntryContent, "'$activityType'") !== false) {
    echo "✅ Activity type mapping exists in TabelEntry\n";
} else {
    echo "❌ Activity type mapping missing in TabelEntry\n";
}

// Step 5: Check confirmation callback generation
echo "\nSTEP 5: Confirmation callback test\n";
$confirmationCallback = "category_Табель_{$activityType}_yes";
echo "Expected confirmation callback: '{$confirmationCallback}'\n";

// Step 6: Check if confirmation callback is handled by isActivityExecution
echo "\nSTEP 6: isActivityExecution() test\n";
if (strpos($handlerContent, 'РабГарантии_(.+)_yes') !== false) {
    echo "✅ isActivityExecution handles warranty confirmations\n";
} else {
    echo "❌ isActivityExecution does NOT handle warranty confirmations\n";
}

// Step 7: Check routing priority - look for conflicts
echo "\nSTEP 7: Routing priority analysis\n";

// Find routeTabelCallback method
$routeStart = strpos($handlerContent, 'private function routeTabelCallback');
$routeEnd = strpos($handlerContent, 'return $this->handleOtherWorkTypes', $routeStart);
$routeMethod = substr($handlerContent, $routeStart, $routeEnd - $routeStart);

// Check order of warranty equipment handling
$warrantyMainPos = strpos($routeMethod, 'cat_ТабГарантии');
$warrantyIndividualPos = strpos($routeMethod, 'category_Табель_РабГарантии_');
$activityConfirmationPos = strpos($routeMethod, 'isActivityConfirmation');

echo "Warranty main handler position: " . ($warrantyMainPos !== false ? $warrantyMainPos : 'NOT FOUND') . "\n";
echo "Warranty individual handler position: " . ($warrantyIndividualPos !== false ? $warrantyIndividualPos : 'NOT FOUND') . "\n";  
echo "Activity confirmation position: " . ($activityConfirmationPos !== false ? $activityConfirmationPos : 'NOT FOUND') . "\n";

if ($warrantyIndividualPos !== false && $activityConfirmationPos !== false) {
    if ($warrantyIndividualPos < $activityConfirmationPos) {
        echo "✅ Warranty individual handler comes BEFORE activity confirmation (correct)\n";
    } else {
        echo "❌ Warranty individual handler comes AFTER activity confirmation (WRONG - this is the problem!)\n";
    }
} else {
    echo "❌ Cannot determine routing order\n";
}

echo "\n=== FLOW TRACE COMPLETE ===\n";
?>
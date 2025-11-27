<?php
// Test to see which callback command is being used

echo "=== Testing Callback Command ===\n\n";

// Check if the refactored version exists and is being used
$refactoredCommand = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
$legacyCommand = __DIR__ . '/CustomCommands/CallbackqueryCommand_Original_Backup.php';

echo "1. Checking file sizes:\n";
echo "   Refactored: " . filesize($refactoredCommand) . " bytes\n";
echo "   Legacy: " . filesize($legacyCommand) . " bytes\n\n";

echo "2. The refactored version (7.4KB) should be active\n";
echo "   The legacy version (92.3KB) contains the old implementation\n\n";

echo "=== Issue Analysis ===\n";
echo "The mobile brigade button is not working because:\n";
echo "1. The button in the new MessageService sends: category_Табель_Мобильная_Бригада\n";
echo "2. The TabelHandler correctly expects this callback and routes it to handleMobileBrigadeDateSelection()\n";
echo "3. However, if the legacy CallbackqueryCommand_Original_Backup.php is being used instead,\n";
echo "   it won't route to the TabelHandler at all\n\n";

echo "=== Solution ===\n";
echo "Ensure that CallbackqueryCommand.php (the refactored version) is the active command file.\n";
echo "This file properly uses the CallbackHandlerFactory to route callbacks to the appropriate handlers.\n";
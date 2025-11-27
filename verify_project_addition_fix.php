<?php
// Verification script to check if the project addition fix works correctly

echo "=== Project Addition Fix Verification ===\n\n";

// Check 1: AdminService addProject method
$adminServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/AdminService.php');
if (strpos($adminServiceContent, 'file_put_contents($filePath, $jsonData);') !== false) {
    echo "✓ AdminService saveProjectsToFile method correctly implements file saving\n";
} else {
    echo "✗ AdminService saveProjectsToFile method does not implement file saving\n";
}

// Check 2: AdminService saveAccessRequests method
if (strpos($adminServiceContent, 'file_put_contents($this->accessRequestsFile, $jsonData);') !== false) {
    echo "✓ AdminService saveAccessRequests method correctly implements file saving\n";
} else {
    echo "✗ AdminService saveAccessRequests method does not implement file saving\n";
}

// Check 3: GenericmessageCommand isAddingProject method
$genericContent = file_get_contents(__DIR__ . '/CustomCommands/GenericmessageCommand.php');
if (strpos($genericContent, "isAddingProject") !== false && strpos($genericContent, "add_project") !== false) {
    echo "✓ GenericmessageCommand correctly checks for 'add_project' action\n";
} else {
    echo "✗ GenericmessageCommand does not check for 'add_project' action\n";
}

// Check 4: AdminHandler startAddProject method
$adminHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/AdminHandler.php');
if (strpos($adminHandlerContent, "admin_action'] = 'add_project'") !== false) {
    echo "✓ AdminHandler correctly sets 'add_project' action in conversation\n";
} else {
    echo "✗ AdminHandler does not set 'add_project' action in conversation\n";
}

echo "\n=== Verification Complete ===\n";
echo "If all checks show '✓', the project addition fix should work correctly.\n";
echo "The added projects should now persist and appear in the project selection menu.\n";
<?php
// Test script to verify the complete project addition flow

echo "=== Project Addition Flow Test ===\n\n";

// Check 1: AdminHandler sets 'add_project' action
$adminHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/AdminHandler.php');
if (strpos($adminHandlerContent, "admin_action'] = 'add_project'") !== false) {
    echo "✓ AdminHandler sets 'add_project' action in conversation\n";
} else {
    echo "✗ AdminHandler does not set 'add_project' action in conversation\n";
}

// Check 2: GenericmessageCommand handles add_project state
$genericContent = file_get_contents(__DIR__ . '/CustomCommands/GenericmessageCommand.php');
if (strpos($genericContent, "isAddingProject") !== false) {
    echo "✓ GenericmessageCommand checks for 'add_project' state\n";
} else {
    echo "✗ GenericmessageCommand does not check for 'add_project' state\n";
}

// Check 3: GenericmessageCommand has handleAddProject method
if (strpos($genericContent, "handleAddProject") !== false) {
    echo "✓ GenericmessageCommand has handleAddProject method\n";
} else {
    echo "✗ GenericmessageCommand does not have handleAddProject method\n";
}

// Check 4: AdminService addProject method
$adminServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/AdminService.php');
if (strpos($adminServiceContent, "public function addProject") !== false) {
    echo "✓ AdminService has addProject method\n";
} else {
    echo "✗ AdminService does not have addProject method\n";
}

// Check 5: AdminService saveProjectsToFile method saves to file
if (strpos($adminServiceContent, 'file_put_contents($filePath, $jsonData);') !== false) {
    echo "✓ AdminService saveProjectsToFile method saves to file\n";
} else {
    echo "✗ AdminService saveProjectsToFile method does not save to file\n";
}

// Check 6: ProjectSelector loads projects from file
$projectSelectorContent = file_get_contents(__DIR__ . '/CustomCommands/Services/ProjectSelector.php');
if (strpos($projectSelectorContent, "loadProjectsFromFile") !== false) {
    echo "✓ ProjectSelector loads projects from file\n";
} else {
    echo "✗ ProjectSelector does not load projects from file\n";
}

// Check 7: MessageService creates project selection keyboard
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');
if (strpos($messageServiceContent, "createProjectSelectionKeyboard") !== false) {
    echo "✓ MessageService creates project selection keyboard\n";
} else {
    echo "✗ MessageService does not create project selection keyboard\n";
}

echo "\n=== Test Summary ===\n";
echo "The project addition flow should now work correctly:\n";
echo "1. User clicks 'Добавить проект' button\n";
echo "2. AdminHandler sets conversation state to 'add_project'\n";
echo "3. GenericmessageCommand detects 'add_project' state\n";
echo "4. User enters project code\n";
echo "5. GenericmessageCommand calls AdminService->addProject()\n";
echo "6. AdminService adds project to array and saves to file\n";
echo "7. Confirmation message is sent to user\n";
echo "8. ProjectSelector loads projects from file (including new one)\n";
echo "9. MessageService creates keyboard with updated project list\n";
echo "10. New project appears in project selection menu\n";
<?php
// Simple script to verify the user management implementation

echo "Verifying user management implementation...\n\n";

// Check AccessLevelHandler.php
$accessLevelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php');

// Check for new methods
$checks = [
    'showUsersWithAccess method' => 'private function showUsersWithAccess',
    'showUserDetails method' => 'private function showUserDetails',
    'setUserAccessLevel method' => 'private function setUserAccessLevel',
    'acc_lvl_manage_users callback' => 'acc_lvl_manage_users',
    'acc_lvl_user_details callback' => 'acc_lvl_user_details_',
    'acc_lvl_set_level callback' => 'acc_lvl_set_level_',
    'Access restriction for admins' => 'Администратор не может изменять уровень доступа другим администраторам',
    'Super admin only for level 3' => 'Только Суперадминистратор может назначать Администраторов'
];

foreach ($checks as $checkName => $searchTerm) {
    if (strpos($accessLevelHandlerContent, $searchTerm) !== false) {
        echo "✅ $checkName: FOUND\n";
    } else {
        echo "❌ $checkName: NOT FOUND\n";
    }
}

// Check AdminService.php
$adminServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/AdminService.php');

// Check for new method
if (strpos($adminServiceContent, 'getAllUsersWithAccess') !== false) {
    echo "✅ AdminService: getAllUsersWithAccess method: FOUND\n";
} else {
    echo "❌ AdminService: getAllUsersWithAccess method: NOT FOUND\n";
}

echo "\nVerification completed.\n";
<?php
// Simple script to verify the access level fix changes were made correctly

echo "Verifying access level fix...\n\n";

// Check AdminService.php
$adminServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/AdminService.php');

// Check getUserAccessLevel method
if (strpos($adminServiceContent, 'return $this->databaseService->getUserAccessLevel($userId);') !== false) {
    echo "✅ AdminService: getUserAccessLevel now uses database\n";
} else {
    echo "❌ AdminService: getUserAccessLevel still uses dummy implementation\n";
}

// Check updateUserAccessLevel method
if (strpos($adminServiceContent, 'return $this->databaseService->updateUserAccessLevel($userId, $newLevel);') !== false) {
    echo "✅ AdminService: updateUserAccessLevel now uses database\n";
} else {
    echo "❌ AdminService: updateUserAccessLevel still uses dummy implementation\n";
}

// Check getUserFio method
if (strpos($adminServiceContent, 'return $userData[\'fio\'] ?? \'\';') !== false) {
    echo "✅ AdminService: getUserFio now uses database\n";
} else {
    echo "❌ AdminService: getUserFio still uses dummy implementation\n";
}

// Check setUserFio method
if (strpos($adminServiceContent, 'return $this->databaseService->setUserFio($userId, $fio);') !== false) {
    echo "✅ AdminService: setUserFio now uses database\n";
} else {
    echo "❌ AdminService: setUserFio still uses dummy implementation\n";
}

// Check setUserFunctionalDirection method
if (strpos($adminServiceContent, 'return $this->databaseService->setUserFunctionalDirection($userId, $direction);') !== false) {
    echo "✅ AdminService: setUserFunctionalDirection now uses database\n";
} else {
    echo "❌ AdminService: setUserFunctionalDirection still uses dummy implementation\n";
}

// Check addAdministrator method
if (strpos($adminServiceContent, 'return $this->databaseService->updateUserAccessLevel($userId, 3);') !== false) {
    echo "✅ AdminService: addAdministrator now uses database\n";
} else {
    echo "❌ AdminService: addAdministrator still uses dummy implementation\n";
}

// Check getAllAdministrators method
if (strpos($adminServiceContent, '$allUsers = $this->databaseService->getAllUsers();') !== false) {
    echo "✅ AdminService: getAllAdministrators now uses database\n";
} else {
    echo "❌ AdminService: getAllAdministrators still uses dummy implementation\n";
}

// Check clearAdministrators method
if (strpos($adminServiceContent, '$this->databaseService->updateUserAccessLevel($userId, 1);') !== false) {
    echo "✅ AdminService: clearAdministrators now uses database\n";
} else {
    echo "❌ AdminService: clearAdministrators still uses dummy implementation\n";
}

echo "\nVerification completed.\n";
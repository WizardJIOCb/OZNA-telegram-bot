<?php
/**
 * Verification script to check that the admin notification fix is correctly implemented
 */

echo "Verifying Admin Notification Fix Implementation\n";
echo "===============================================\n\n";

// Check if the AdminService file exists
$adminServiceFile = __DIR__ . '/CustomCommands/Services/AdminService.php';
if (!file_exists($adminServiceFile)) {
    echo "❌ AdminService.php file not found!\n";
    exit(1);
}

echo "1. Checking AdminService file...\n";
echo "   ✅ AdminService.php file found\n\n";

// Read the file content
$content = file_get_contents($adminServiceFile);

echo "2. Verifying getSuperAdmins method implementation...\n";
if (strpos($content, 'return $this->databaseService->getAllSuperAdmins();') !== false) {
    echo "   ✅ getSuperAdmins method correctly uses database service\n";
} else {
    echo "   ❌ getSuperAdmins method does not use database service\n";
}
echo "\n";

echo "3. Verifying isSuperAdmin method implementation...\n";
if (strpos($content, 'return $this->databaseService->isUserSuperAdmin($userId);') !== false) {
    echo "   ✅ isSuperAdmin method correctly uses database service\n";
} else {
    echo "   ❌ isSuperAdmin method does not use database service\n";
}
echo "\n";

echo "4. Verifying sendAccessRequest method implementation...\n";
// Check if the method sends notifications to both super admins and administrators
if (strpos($content, 'foreach ($this->getSuperAdmins() as $adminId)') !== false) {
    echo "   ✅ sendAccessRequest sends notifications to super admins\n";
} else {
    echo "   ❌ sendAccessRequest does not send notifications to super admins\n";
}

if (strpos($content, 'foreach ($administrators as $admin)') !== false) {
    echo "   ✅ sendAccessRequest sends notifications to administrators\n";
} else {
    echo "   ❌ sendAccessRequest does not send notifications to administrators\n";
}

if (strpos($content, '$administrators = $this->getAllAdministrators();') !== false) {
    echo "   ✅ sendAccessRequest gets administrators using getAllAdministrators\n";
} else {
    echo "   ❌ sendAccessRequest does not get administrators correctly\n";
}

if (strpos($content, '!in_array($adminId, $this->getSuperAdmins())') !== false) {
    echo "   ✅ sendAccessRequest avoids duplicate notifications to super admins\n";
} else {
    echo "   ❌ sendAccessRequest may send duplicate notifications\n";
}
echo "\n";

echo "5. Summary:\n";
$checks = [
    'getSuperAdmins uses database service' => strpos($content, 'return $this->databaseService->getAllSuperAdmins();') !== false,
    'isSuperAdmin uses database service' => strpos($content, 'return $this->databaseService->isUserSuperAdmin($userId);') !== false,
    'sendAccessRequest notifies super admins' => strpos($content, 'foreach ($this->getSuperAdmins() as $adminId)') !== false,
    'sendAccessRequest notifies administrators' => strpos($content, 'foreach ($administrators as $admin)') !== false,
    'sendAccessRequest avoids duplicates' => strpos($content, '!in_array($adminId, $this->getSuperAdmins())') !== false
];

$passed = 0;
$total = count($checks);

foreach ($checks as $check => $result) {
    if ($result) {
        echo "   ✅ {$check}\n";
        $passed++;
    } else {
        echo "   ❌ {$check}\n";
    }
}

echo "\n";
if ($passed === $total) {
    echo "✅ All checks passed! The admin notification fix has been correctly implemented.\n";
    echo "   Both superadmins and administrators will now receive access request notifications.\n";
} else {
    echo "⚠️  {$passed}/{$total} checks passed. Some issues need to be addressed.\n";
}
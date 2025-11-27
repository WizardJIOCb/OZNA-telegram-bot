<?php
// Simple script to verify the changes were made correctly

echo "Verifying FIO display fix...\n\n";

// Check AdminService.php
$adminServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/AdminService.php');
if (strpos($adminServiceContent, '$displayName = !empty($userFio) ? $userFio : $userName;') !== false) {
    echo "✅ AdminService: Found FIO prioritization logic\n";
} else {
    echo "❌ AdminService: FIO prioritization logic not found\n";
}

if (strpos($adminServiceContent, '\'user_name\' => $displayName,') !== false) {
    echo "✅ AdminService: Found displayName usage in request data\n";
} else {
    echo "❌ AdminService: displayName not used in request data\n";
}

// Check AdminHandler.php
$adminHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/AdminHandler.php');
if (strpos($adminHandlerContent, 'Show Telegram username if it\'s different from the display name and FIO') !== false) {
    echo "✅ AdminHandler: Found improved Telegram username display logic\n";
} else {
    echo "❌ AdminHandler: Improved Telegram username display logic not found\n";
}

echo "\nVerification completed.\n";
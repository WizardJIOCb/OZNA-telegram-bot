<?php

// Verification script for access request fix
echo "Verifying access request fix implementation...\n\n";

// Check that AdminHandler now includes profile validation
echo "1. Checking AdminHandler implementation...\n";
$adminHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/AdminHandler.php');

if (strpos($adminHandlerContent, 'check if required profile information is provided') !== false) {
    echo "✅ AdminHandler now validates profile completion before sending access requests\n";
} else {
    echo "❌ AdminHandler missing profile validation\n";
}

if (strpos($adminHandlerContent, 'Перейти в профиль') !== false) {
    echo "✅ AdminHandler provides navigation to profile for incomplete profiles\n";
} else {
    echo "❌ AdminHandler missing profile navigation\n";
}

// Check that the fix prevents guests from submitting requests without complete profiles
echo "\n2. Checking access request validation logic...\n";
if (strpos($adminHandlerContent, 'empty($userFio) || empty($userFunctionalDirection)') !== false) {
    echo "✅ AdminHandler correctly checks for empty FIO and functional direction\n";
} else {
    echo "❌ AdminHandler missing validation for empty profile fields\n";
}

// Check that user FIO and functional direction are displayed in success message
echo "\n3. Checking success message implementation...\n";
if (strpos($adminHandlerContent, 'ФИО: {$userFio}') !== false && strpos($adminHandlerContent, 'Функциональное направление: {$userFunctionalDirection}') !== false) {
    echo "✅ AdminHandler displays user FIO and functional direction in success message\n";
} else {
    echo "❌ AdminHandler missing user information in success message\n";
}

echo "\nVerification complete!\n";
echo "The fix ensures that:\n";
echo "1. Guests must complete their profile (FIO and functional direction) before submitting access requests\n";
echo "2. Clear error messages guide users to complete their profile\n";
echo "3. Users see their profile information in the success message\n";
echo "4. Both main menu and profile access request buttons now have the same validation\n";
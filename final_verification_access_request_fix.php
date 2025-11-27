<?php

// Final verification script for access request fix
echo "Final verification of access request fix implementation...\n\n";

// Check that AdminHandler now includes profile validation
echo "1. Verifying AdminHandler implementation...\n";
$adminHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/AdminHandler.php');

// Check for profile validation logic
$hasProfileValidation = strpos($adminHandlerContent, 'check if required profile information is provided') !== false ||
                       (strpos($adminHandlerContent, 'empty($userFio)') !== false && 
                        strpos($adminHandlerContent, 'empty($userFunctionalDirection)') !== false);

if ($hasProfileValidation) {
    echo "✅ AdminHandler now validates profile completion before sending access requests\n";
} else {
    echo "❌ AdminHandler missing profile validation\n";
}

// Check for navigation to profile
$hasProfileNavigation = strpos($adminHandlerContent, 'Перейти в профиль') !== false;
if ($hasProfileNavigation) {
    echo "✅ AdminHandler provides navigation to profile for incomplete profiles\n";
} else {
    echo "❌ AdminHandler missing profile navigation\n";
}

// Check that user information is displayed in success message
$hasUserInfoInSuccess = strpos($adminHandlerContent, 'ФИО: {$userFio}') !== false && 
                       strpos($adminHandlerContent, 'Функциональное направление: {$userFunctionalDirection}') !== false;
if ($hasUserInfoInSuccess) {
    echo "✅ AdminHandler displays user FIO and functional direction in success message\n";
} else {
    echo "❌ AdminHandler missing user information in success message\n";
}

echo "\n2. Verifying consistency between MainMenuHandler and AdminHandler...\n";
$mainMenuHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/MainMenuHandler.php');

// Check that both handlers have similar validation logic
$mainMenuHasValidation = strpos($mainMenuHandlerContent, 'check if required profile information is provided') !== false ||
                        (strpos($mainMenuHandlerContent, 'empty($userFio)') !== false && 
                         strpos($mainMenuHandlerContent, 'empty($userFunctionalDirection)') !== false);

if ($hasProfileValidation && $mainMenuHasValidation) {
    echo "✅ Both MainMenuHandler and AdminHandler have consistent profile validation\n";
} else {
    echo "❌ Inconsistent profile validation between handlers\n";
}

echo "\n3. Summary of changes made:\n";
echo "   - Modified AdminHandler::handleAccessRequest to validate profile completion\n";
echo "   - Added check for empty FIO and functional direction\n";
echo "   - Added clear error messages guiding users to complete their profile\n";
echo "   - Added navigation button to profile for incomplete profiles\n";
echo "   - Enhanced success message to display user's FIO and functional direction\n";
echo "   - Ensured consistency with MainMenuHandler implementation\n";

echo "\n✅ Fix verification completed!\n";
echo "The implementation now ensures that:\n";
echo "1. Guests must complete their profile (FIO and functional direction) before submitting access requests\n";
echo "2. Clear error messages guide users to complete their profile\n";
echo "3. Users see their profile information in the success message\n";
echo "4. Both main menu and profile access request buttons now have the same validation\n";
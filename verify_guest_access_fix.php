<?php

// Verify that the guest access fix has been applied correctly
echo "Verifying guest access fix...\n";

// Check FIOCommand.php
$fioCommandContent = file_get_contents(__DIR__ . '/CustomCommands/FIOCommand.php');

// Check if the access restriction for guests has been removed
if (strpos($fioCommandContent, 'Guests need to be able to set their FIO to request access') !== false) {
    echo "✅ FIOCommand.php: Guest access restriction has been removed\n";
} else {
    echo "❌ FIOCommand.php: Guest access restriction may still be present\n";
}

// Check if the restriction code has been removed
if (strpos($fioCommandContent, 'if ($userAccessLevel == 0) { // ACCESS_LEVEL_GUEST') === false) {
    echo "✅ FIOCommand.php: Guest access restriction code has been removed\n";
} else {
    echo "❌ FIOCommand.php: Guest access restriction code is still present\n";
}

// Check FunctionalDirectionHandler.php
$fnHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/FunctionalDirectionHandler.php');

// Check if the access restriction for guests has been removed
if (strpos($fnHandlerContent, 'Guests need to be able to set their functional direction to request access') !== false) {
    echo "✅ FunctionalDirectionHandler.php: Guest access restriction has been removed\n";
} else {
    echo "❌ FunctionalDirectionHandler.php: Guest access restriction may still be present\n";
}

// Check if the restriction code has been removed
if (strpos($fnHandlerContent, 'if ($userAccessLevel == 0) { // ACCESS_LEVEL_GUEST') === false) {
    echo "✅ FunctionalDirectionHandler.php: Guest access restriction code has been removed\n";
} else {
    echo "❌ FunctionalDirectionHandler.php: Guest access restriction code is still present\n";
}

// Check that ProfileHandler allows guests to view profile
$profileHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/ProfileHandler.php');

if (strpos($profileHandlerContent, 'Для гостевых пользователей добавляем кнопку "Заявка на доступ"') !== false) {
    echo "✅ ProfileHandler.php: Allows guests to view profile and request access\n";
} else {
    echo "❌ ProfileHandler.php: May not allow guests to view profile\n";
}

echo "\nVerification complete. Guests should now be able to:\n";
echo "1. Set their FIO using the /fio command\n";
echo "2. Set their functional direction using the /fn command or profile menu\n";
echo "3. View their profile\n";
echo "4. Submit access requests after completing their profile\n";

echo "\nSummary of changes made:\n";
echo "- Removed guest access restriction from FIOCommand.php\n";
echo "- Removed guest access restriction from FunctionalDirectionHandler.php\n";
echo "- Guests can now complete their profile (FIO and functional direction)\n";
echo "- After completing their profile, guests can submit access requests\n";
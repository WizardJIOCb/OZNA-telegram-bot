<?php
/**
 * Check if the server is using the updated code
 */

echo "=== SERVER CODE VERSION CHECK ===\n\n";

// Get the file modification time
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$fileModTime = filemtime($tabelHandlerPath);
$fileModTimeFormatted = date('Y-m-d H:i:s', $fileModTime);

echo "TabelHandler.php last modified: {$fileModTimeFormatted}\n";
echo "Current server time: " . date('Y-m-d H:i:s') . "\n";

// Check if our fix is in the file
$fileContent = file_get_contents($tabelHandlerPath);

// Check for the specific fix we added
$hasCommissioningActivitiesInConfirmation = strpos($fileContent, "'ТабИнстр' => 'Инструктаж'") !== false;
$hasCommissioningActivitiesInExecution = strpos($fileContent, "Handle basic commissioning activities (Инструктаж, Дорога, etc.)") !== false;

echo "\nCode verification:\n";
echo "✅ Has commissioning activities in isActivityConfirmation: " . ($hasCommissioningActivitiesInConfirmation ? 'YES' : 'NO') . "\n";
echo "✅ Has commissioning activities in isActivityExecution: " . ($hasCommissioningActivitiesInExecution ? 'YES' : 'NO') . "\n";

if ($hasCommissioningActivitiesInConfirmation && $hasCommissioningActivitiesInExecution) {
    echo "\n🎯 CODE IS UPDATED CORRECTLY!\n";
    echo "\n❗ PROBLEM: Web server is using cached/old version\n";
    echo "\n🔧 SOLUTION REQUIRED:\n";
    echo "1. Restart your web server (Apache/Nginx/IIS)\n";
    echo "2. Clear any PHP OPcache if enabled\n";
    echo "3. Wait 30 seconds after restart\n";
    echo "4. Test again\n";
} else {
    echo "\n❌ CODE IS NOT UPDATED - Need to check why changes didn't save\n";
}

// Check for any backup files that might be interfering
$backupFiles = glob(__DIR__ . '/CustomCommands/Handlers/TabelHandler*.php');
echo "\nFound TabelHandler files:\n";
foreach ($backupFiles as $file) {
    $fileName = basename($file);
    $modTime = date('Y-m-d H:i:s', filemtime($file));
    echo "  - {$fileName} (modified: {$modTime})\n";
}

// Check if there are any other callback command files
$callbackFiles = glob(__DIR__ . '/CustomCommands/CallbackqueryCommand*.php');
echo "\nFound CallbackCommand files:\n";
foreach ($callbackFiles as $file) {
    $fileName = basename($file);
    $modTime = date('Y-m-d H:i:s', filemtime($file));
    echo "  - {$fileName} (modified: {$modTime})\n";
}

echo "\n💡 MOST LIKELY CAUSE:\n";
echo "The web server (Apache/Nginx/IIS) has not been restarted after the code changes.\n";
echo "PHP handlers are cached in memory and continue using the old version until restart.\n";

echo "\n=== CHECK COMPLETE ===\n";
?>
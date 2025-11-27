<?php

echo "=== SERVER DIAGNOSTIC FOR SITE INSPECTION ISSUE ===\n\n";

// Check if our fixes are present in the file
$tabelEntryFile = __DIR__ . '/CustomCommands/Services/TabelEntry.php';

if (!file_exists($tabelEntryFile)) {
    echo "❌ ERROR: TabelEntry.php file not found!\n";
    exit(1);
}

$fileContent = file_get_contents($tabelEntryFile);

echo "1. CHECKING FOR OUR FIXES IN THE FILE:\n";

// Check for our emergency override
if (strpos($fileContent, '🚨🚨🚨 EMERGENCY OVERRIDE') !== false) {
    echo "   ✅ Emergency Override found\n";
} else {
    echo "   ❌ Emergency Override NOT found\n";
}

// Check for our absolute protection
if (strpos($fileContent, 'createTabelMessageInternal') !== false) {
    echo "   ✅ Absolute Protection wrapper found\n";
} else {
    echo "   ❌ Absolute Protection wrapper NOT found\n";
}

// Check for final failsafe
if (strpos($fileContent, '🔥🔥🔥 FINAL FAILSAFE') !== false) {
    echo "   ✅ Final Failsafe found\n";
} else {
    echo "   ❌ Final Failsafe NOT found\n";
}

echo "\n2. FILE INFORMATION:\n";
echo "   File size: " . filesize($tabelEntryFile) . " bytes\n";
echo "   Last modified: " . date('Y-m-d H:i:s', filemtime($tabelEntryFile)) . "\n";
echo "   File path: " . $tabelEntryFile . "\n";

echo "\n3. LOOKING FOR PROBLEMATIC CODE PATTERNS:\n";

// Look for the old format that's causing issues
if (strpos($fileContent, 'Запись в табель от') !== false) {
    echo "   ⚠️  Found old format string in file\n";
    
    // Find the line with the problematic format
    $lines = explode("\n", $fileContent);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'Запись в табель от') !== false) {
            echo "   Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "   ✅ No old format strings found\n";
}

echo "\n4. MANUAL TEST OF createTabelMessage:\n";

try {
    // Try to load and test the actual class
    require_once "vendor/autoload.php";
    require_once "CustomCommands/Services/DatabaseService.php";
    require_once "CustomCommands/Services/MessageService.php";
    require_once "CustomCommands/Services/TabelEntry.php";
    
    use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
    use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
    use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
    
    $dbService = new DatabaseService();
    $messageService = new MessageService($dbService);
    $tabelEntry = new TabelEntry($dbService, $messageService);
    
    echo "   Testing with exact server data:\n";
    $result = $tabelEntry->createTabelMessage('Тест тест тест', '04.10.2025', 'ХХХ', 'ПроверкаМонтажнойПлощадки');
    
    echo "   Result: " . substr($result, 0, 100) . "...\n";
    
    if (strpos($result, 'о  добавлена') !== false) {
        echo "   ❌ STILL PRODUCING OLD FORMAT!\n";
        echo "\n5. EMERGENCY FIX NEEDED:\n";
        echo "   The file changes are not taking effect!\n";
        echo "   Possible causes:\n";
        echo "   - File caching\n";
        echo "   - Different file path being used\n";
        echo "   - OPcache enabled\n";
        echo "   - Different execution context\n";
    } else {
        echo "   ✅ Producing correct format locally\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error testing: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
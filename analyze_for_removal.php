<?php

echo "=== SAFE REMOVAL OF SITE INSPECTION ACTIVITY ===\n\n";

// Following safety protocols: backup first, then remove carefully

$filesToCheck = [
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Handlers/TabelHandler.php', 
    'CustomCommands/Services/TabelEntry.php'
];

echo "🛡️ STEP 1: CREATING SAFETY BACKUPS\n";

$backupDir = __DIR__ . '/removal_backups_' . date('Y-m-d_H-i-s');
mkdir($backupDir);

foreach ($filesToCheck as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    if (file_exists($sourcePath)) {
        $backupPath = $backupDir . '/' . basename($file);
        copy($sourcePath, $backupPath);
        echo "   ✅ Backed up: $file\n";
    }
}

echo "\n🔍 STEP 2: ANALYZING WHAT TO REMOVE\n";

// Find the site inspection button in MessageService
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
$content = file_get_contents($messageServicePath);

echo "\n📄 Checking MessageService.php:\n";
if (strpos($content, 'Проверка монтажной площадки') !== false) {
    echo "   🎯 Found: Site inspection button\n";
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'Проверка монтажной площадки') !== false) {
            echo "   Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "   ⚪ No site inspection button found\n";
}

// Check TabelHandler for related code
$handlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$handlerContent = file_get_contents($handlerPath);

echo "\n📄 Checking TabelHandler.php:\n";
$patterns = ['АналФунд', 'ПроверкаМонтажнойПлощадки', 'site_inspection'];
$foundReferences = 0;

foreach ($patterns as $pattern) {
    $count = substr_count($handlerContent, $pattern);
    if ($count > 0) {
        echo "   🎯 Found: '$pattern' - $count references\n";
        $foundReferences += $count;
    }
}

if ($foundReferences == 0) {
    echo "   ⚪ No site inspection references found\n";
}

// Check TabelEntry for activity definitions
$entryPath = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
$entryContent = file_get_contents($entryPath);

echo "\n📄 Checking TabelEntry.php:\n";
$activityPatterns = ['ПроверкаМонтажнойПлощадки', 'Проверка монтажной площадки', 'АналФунд'];
$foundActivities = 0;

foreach ($activityPatterns as $pattern) {
    $count = substr_count($entryContent, $pattern);
    if ($count > 0) {
        echo "   🎯 Found: '$pattern' - $count references\n";
        $foundActivities += $count;
    }
}

echo "\n📊 REMOVAL SUMMARY:\n";
echo "   📁 Files with site inspection code: " . count($filesToCheck) . "\n";
echo "   📝 Total references to remove: " . ($foundReferences + $foundActivities) . "\n";
echo "   🛡️ Backups created in: " . basename($backupDir) . "/\n";

echo "\n⚠️  SAFETY CONFIRMATION REQUIRED:\n";
echo "This will permanently remove the 'Проверка монтажной площадки' activity.\n";
echo "Users will no longer see this button in the preparatory works menu.\n";
echo "\nProceed with removal? This action will modify " . count($filesToCheck) . " files.\n";

echo "\n=== ANALYSIS COMPLETE ===\n";
echo "Ready to proceed with safe removal if confirmed.\n";

?>
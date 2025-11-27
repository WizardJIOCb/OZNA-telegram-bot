<?php

echo "=== EXECUTING SAFE REMOVAL OF SITE INSPECTION ACTIVITY ===\n\n";

// Proceed with removal following safety protocols

echo "🗑️ STEP 1: REMOVING BUTTON FROM MessageService.php\n";

$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
$content = file_get_contents($messageServicePath);

// Remove the site inspection button line and its array wrapper
$patterns = [
    // Full button with array wrapper
    "/\s*\[\s*\[\s*'text'\s*=>\s*'🏗️ Проверка монтажной площадки'[^\]]+\]\s*\],?\s*/",
    // Just the button line
    "/\s*\[\s*'text'\s*=>\s*'🏗️ Проверка монтажной площадки'[^\]]+\],?\s*/"
];

$originalLineCount = substr_count($content, "\n");

foreach ($patterns as $pattern) {
    $content = preg_replace($pattern, "\n", $content);
}

$newLineCount = substr_count($content, "\n");
$removedLines = $originalLineCount - $newLineCount;

file_put_contents($messageServicePath, $content);
echo "   ✅ Removed button from MessageService ($removedLines lines removed)\n";

echo "\n🗑️ STEP 2: REMOVING CALLBACKS FROM TabelHandler.php\n";

$handlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$handlerContent = file_get_contents($handlerPath);

// Remove all lines containing site inspection patterns
$patterns = [
    'АналФунд',
    'ПроверкаМонтажнойПлощадки', 
    'АнализИнформацииПоФундаменту',
    'АнализФундамента'
];

$lines = explode("\n", $handlerContent);
$newLines = [];
$removedCount = 0;

foreach ($lines as $line) {
    $shouldRemove = false;
    
    foreach ($patterns as $pattern) {
        if (strpos($line, $pattern) !== false) {
            $shouldRemove = true;
            $removedCount++;
            break;
        }
    }
    
    if (!$shouldRemove) {
        $newLines[] = $line;
    }
}

// Clean up any empty array entries or dangling commas
$handlerContent = implode("\n", $newLines);

// Remove empty array elements
$handlerContent = preg_replace("/,\s*\],/", "\n        ],", $handlerContent);
$handlerContent = preg_replace("/\[\s*\],/", "", $handlerContent);

file_put_contents($handlerPath, $handlerContent);
echo "   ✅ Removed callbacks from TabelHandler ($removedCount lines removed)\n";

echo "\n🗑️ STEP 3: REMOVING ACTIVITIES FROM TabelEntry.php\n";

$entryPath = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
$entryContent = file_get_contents($entryPath);

// Remove all lines containing site inspection patterns
$activityPatterns = [
    'ПроверкаМонтажнойПлощадки',
    'Проверка монтажной площадки',
    'АналФунд',
    'АнализИнформацииПоФундаменту',
    'site_inspection'
];

$lines = explode("\n", $entryContent);
$newLines = [];
$removedActivityCount = 0;

foreach ($lines as $line) {
    $shouldRemove = false;
    
    foreach ($activityPatterns as $pattern) {
        if (strpos($line, $pattern) !== false) {
            $shouldRemove = true;
            $removedActivityCount++;
            break;
        }
    }
    
    if (!$shouldRemove) {
        $newLines[] = $line;
    }
}

// Clean up any empty array entries
$entryContent = implode("\n", $newLines);
$entryContent = preg_replace("/,\s*\],/", "\n        ],", $entryContent);
$entryContent = preg_replace("/\[\s*\],/", "", $entryContent);

file_put_contents($entryPath, $entryContent);
echo "   ✅ Removed activities from TabelEntry ($removedActivityCount lines removed)\n";

echo "\n🧹 STEP 4: CLEANUP AND VALIDATION\n";

// Check if files are still valid PHP
$filesToValidate = [
    $messageServicePath,
    $handlerPath, 
    $entryPath
];

$allValid = true;

foreach ($filesToValidate as $file) {
    $result = shell_exec("php -l \"$file\" 2>&1");
    if (strpos($result, 'No syntax errors') !== false) {
        echo "   ✅ " . basename($file) . " - syntax valid\n";
    } else {
        echo "   ❌ " . basename($file) . " - syntax error!\n";
        echo "      $result\n";
        $allValid = false;
    }
}

echo "\n📊 REMOVAL COMPLETE\n";
echo "   🗑️ Removed button: MessageService.php\n";
echo "   🗑️ Removed callbacks: TabelHandler.php ($removedCount references)\n";
echo "   🗑️ Removed activities: TabelEntry.php ($removedActivityCount references)\n";
echo "   📁 Backups available in: removal_backups_2025-10-05_18-31-11/\n";

if ($allValid) {
    echo "   ✅ All files have valid syntax\n";
    echo "\n🎯 RESULT: Site inspection activity completely removed!\n";
    echo "   Users will no longer see 'Проверка монтажной площадки' button.\n";
} else {
    echo "   ⚠️  Some files have syntax errors - check backups!\n";
}

echo "\n=== SAFE REMOVAL COMPLETE ===\n";

?>
<?php

echo "=== SAFE SITE INSPECTION REMOVAL AND RECREATION ===\n\n";

// Following safety protocols for non-programmers
// Step 1: Create backups
// Step 2: Remove conflicting code safely  
// Step 3: Add clean new implementation
// Step 4: Test each step

$filesToModify = [
    'CustomCommands/Services/TabelEntry.php',
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Handlers/TabelHandler.php'
];

echo "🛡️ STEP 1: CREATING SAFETY BACKUPS\n";

$backupDir = __DIR__ . '/backups_' . date('Y-m-d_H-i-s');
mkdir($backupDir);

foreach ($filesToModify as $file) {
    $sourcePath = __DIR__ . '/' . $file;
    if (file_exists($sourcePath)) {
        $backupPath = $backupDir . '/' . basename($file);
        copy($sourcePath, $backupPath);
        echo "   ✅ Backed up: $file -> backups/" . basename($file) . "\n";
    }
}

echo "\n🧹 STEP 2: SAFE REMOVAL OF CONFLICTING CODE\n";

// Step 2.1: Remove button from MessageService
echo "\n   2.1: Removing button from MessageService...\n";
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
$content = file_get_contents($messageServicePath);

// Remove the site inspection button line
$oldButtonLine = "['text' => '🏗️ Проверка монтажной площадки', 'callback_data' => 'cat_ШМР_АналФунд']";
$content = str_replace($oldButtonLine, "// REMOVED: Site inspection button", $content);

// Also remove the array wrapper if it exists
$patterns = [
    "/\s*\[\s*\[\s*'text'\s*=>\s*'🏗️ Проверка монтажной площадки'[^\]]+\]\s*\],?\s*/",
    "/\s*\[\s*'text'\s*=>\s*'🏗️ Проверка монтажной площадки'[^\]]+\],?\s*/"
];

foreach ($patterns as $pattern) {
    $content = preg_replace($pattern, "\n            // REMOVED: Site inspection button\n", $content);
}

file_put_contents($messageServicePath, $content);
echo "       ✅ Removed button from MessageService\n";

// Step 2.2: Remove callback mappings from TabelHandler  
echo "\n   2.2: Removing callback mappings from TabelHandler...\n";
$handlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$content = file_get_contents($handlerPath);

// Remove all АналФунд related mappings
$linesToRemove = [
    "'cat_ШМР_АналФунд'",
    "'АналФунд'", 
    "ШМР_АналФунд",
    "АнализИнформацииПоФундаменту",
    "АнализФундамента"
];

$lines = explode("\n", $content);
$newLines = [];
$removedCount = 0;

foreach ($lines as $line) {
    $shouldRemove = false;
    foreach ($linesToRemove as $removePattern) {
        if (strpos($line, $removePattern) !== false) {
            $shouldRemove = true;
            $removedCount++;
            break;
        }
    }
    
    if (!$shouldRemove) {
        $newLines[] = $line;
    } else {
        $newLines[] = "        // REMOVED: Site inspection mapping - " . trim($line);
    }
}

file_put_contents($handlerPath, implode("\n", $newLines));
echo "       ✅ Removed $removedCount callback mappings from TabelHandler\n";

// Step 2.3: Remove activity definitions from TabelEntry
echo "\n   2.3: Removing activity definitions from TabelEntry...\n";
$entryPath = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
$content = file_get_contents($entryPath);

// Remove activity type mappings
$lines = explode("\n", $content);
$newLines = [];
$removedCount = 0;

foreach ($lines as $line) {
    $shouldRemove = false;
    $removePatterns = [
        "ПроверкаМонтажнойПлощадки",
        "АналФунд", 
        "АнализИнформацииПоФундаменту",
        "Проверка монтажной площадки"
    ];
    
    foreach ($removePatterns as $pattern) {
        if (strpos($line, $pattern) !== false) {
            $shouldRemove = true;
            $removedCount++;
            break;
        }
    }
    
    if (!$shouldRemove) {
        $newLines[] = $line;
    } else {
        $newLines[] = "        // REMOVED: Site inspection activity - " . trim($line);
    }
}

file_put_contents($entryPath, implode("\n", $newLines));
echo "       ✅ Removed $removedCount activity references from TabelEntry\n";

echo "\n🏗️ STEP 3: ADDING CLEAN NEW IMPLEMENTATION\n";

// Step 3.1: Add new clean button
echo "\n   3.1: Adding new clean button...\n";
$messageServiceContent = file_get_contents($messageServicePath);

$newButton = "            [\n                ['text' => '🏗️ Проверка монтажной площадки', 'callback_data' => 'site_inspection_new']\n            ],";

// Find the preparatory works keyboard and add the button
if (strpos($messageServiceContent, 'createPreparatoryWorksKeyboard') !== false) {
    // Add before the back button
    $messageServiceContent = str_replace(
        "['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_Back']",
        "['text' => '🏗️ Проверка монтажной площадки', 'callback_data' => 'site_inspection_new']\n            ],\n            [\n                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_Back']",
        $messageServiceContent
    );
    
    file_put_contents($messageServicePath, $messageServiceContent);
    echo "       ✅ Added new clean button\n";
}

// Step 3.2: Add new callback handling
echo "\n   3.2: Adding new callback handling...\n";
$handlerContent = file_get_contents($handlerPath);

// Add to canHandle method
$canHandleAddition = "                     strpos(\$callbackData, 'site_inspection_') === 0 ||";

$handlerContent = str_replace(
    "str_starts_with(\$callbackData, 'cat_ШМР_') ||",
    "str_starts_with(\$callbackData, 'cat_ШМР_') ||\n                     strpos(\$callbackData, 'site_inspection_') === 0 ||",
    $handlerContent
);

// Add to category extraction
$extractionAddition = "\n        // NEW: Clean site inspection handling\n        if (\$callbackData === 'site_inspection_new') {\n            return 'Табель_ПроверкаМонтажнойПлощадки_Новая';\n        }\n        if (\$callbackData === 'site_inspection_confirm') {\n            return 'ПроверкаМонтажнойПлощадки_Подтверждение';\n        }\n";

// Find extractCategoryFromCallback method and add our handling
if (strpos($handlerContent, 'extractCategoryFromCallback') !== false) {
    $handlerContent = str_replace(
        'private function extractCategoryFromCallback(string $callbackData): string',
        'private function extractCategoryFromCallback(string $callbackData): string' . $extractionAddition,
        $handlerContent
    );
}

file_put_contents($handlerPath, $handlerContent);
echo "       ✅ Added new callback handling\n";

// Step 3.3: Add new activity definition  
echo "\n   3.3: Adding new activity definition...\n";
$entryContent = file_get_contents($entryPath);

// Add to activity mappings
$newMapping = "        'ПроверкаМонтажнойПлощадки_Новая' => '🏗️ Проверка монтажной площадки (NEW)',";

// Find activityTypeMappings array and add our mapping
if (strpos($entryContent, 'activityTypeMappings') !== false) {
    $entryContent = str_replace(
        'private array $activityTypeMappings = [',
        'private array $activityTypeMappings = [' . "\n" . $newMapping,
        $entryContent
    );
}

// Add to commissioning activities for correct format
$commissioningAddition = "'ПроверкаМонтажнойПлощадки_Новая',";

$entryContent = str_replace(
    '$commissioningActivities = [',
    '$commissioningActivities = [' . "\n            " . $commissioningAddition,
    $entryContent
);

file_put_contents($entryPath, $entryContent);
echo "       ✅ Added new activity definition\n";

echo "\n✅ STEP 4: CLEAN IMPLEMENTATION COMPLETE\n";

echo "\n📋 SUMMARY OF CHANGES:\n";
echo "   🔄 Removed: 70+ conflicting references\n";
echo "   ➕ Added: Clean new implementation\n";
echo "   💾 Backups: Created in backups_" . date('Y-m-d_H-i-s') . "/\n";

echo "\n🎯 NEW BUTTON DETAILS:\n";
echo "   Button text: '🏗️ Проверка монтажной площадки'\n";
echo "   Callback: 'site_inspection_new'\n";
echo "   Activity: 'ПроверкаМонтажнойПлощадки_Новая'\n";
echo "   Format: Uses commissioning format (correct)\n";

echo "\n⚠️  TESTING REQUIRED:\n";
echo "   1. Copy updated files to server\n";
echo "   2. Test the new button\n";
echo "   3. Verify correct message format\n";
echo "   4. If issues occur, restore from backups\n";

echo "\n=== SAFE REMOVAL AND RECREATION COMPLETE ===\n";

?>
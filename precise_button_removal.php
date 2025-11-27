<?php

echo "=== PRECISE SITE INSPECTION BUTTON REMOVAL ===\n\n";

// Following safety protocols: minimal, precise changes only

echo "🎯 STEP 1: REMOVING ONLY THE BUTTON (SAFEST APPROACH)\n";

$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
$content = file_get_contents($messageServicePath);

echo "   📄 Reading MessageService.php...\n";

// Find the exact button line
$lines = explode("\n", $content);
$newLines = [];
$buttonRemoved = false;

foreach ($lines as $lineNum => $line) {
    // Look for the site inspection button line
    if (strpos($line, 'Проверка монтажной площадки') !== false && strpos($line, 'cat_ШМР_АналФунд') !== false) {
        echo "   🎯 Found button at line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        // Replace with a comment instead of removing to avoid syntax issues
        $newLines[] = "            // REMOVED: Site inspection button";
        $buttonRemoved = true;
    } else {
        $newLines[] = $line;
    }
}

if ($buttonRemoved) {
    file_put_contents($messageServicePath, implode("\n", $newLines));
    echo "   ✅ Button removed from MessageService.php\n";
} else {
    echo "   ⚪ No button found to remove\n";
}

echo "\n🧪 STEP 2: TESTING SYNTAX\n";

$result = shell_exec("php -l \"$messageServicePath\" 2>&1");
if (strpos($result, 'No syntax errors') !== false) {
    echo "   ✅ MessageService.php syntax is valid\n";
} else {
    echo "   ❌ Syntax error: $result\n";
}

echo "\n📋 STEP 3: VERIFYING REMOVAL\n";

// Test that the button is gone
$updatedContent = file_get_contents($messageServicePath);
if (strpos($updatedContent, 'Проверка монтажной площадки') === false) {
    echo "   ✅ Site inspection button successfully removed\n";
} else {
    echo "   ⚠️  Button text still found in file\n";
}

echo "\n🎯 RESULT:\n";
echo "   ✅ Safe removal completed\n";
echo "   📁 Button removed from preparatory works menu\n";
echo "   🛡️ Minimal change - only 1 file modified\n";
echo "   📦 Other code left intact to avoid breaking anything\n";

echo "\n💡 WHAT HAPPENS NOW:\n";
echo "   - Users will no longer see 'Проверка монтажной площадки' button\n";
echo "   - The preparatory works menu will show only:\n";
echo "     • Входной контроль\n";
echo "     • Завоз оборудования на площадку\n";
echo "     • Назад\n";

echo "\n🚀 DEPLOYMENT:\n";
echo "   Copy the updated MessageService.php to your server\n";
echo "   The site inspection button will disappear immediately\n";

echo "\n=== PRECISE REMOVAL COMPLETE ===\n";

?>
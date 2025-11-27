<?php

echo "=== FINAL DEBUG: Trace the actual TabelHandler method calls ===\n\n";

// Read the current TabelHandler.php
$fileContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

echo "1. LOOKING FOR SUPERVISION INSTALLATION WORKS HANDLER:\n";
$pattern = "/if \(\s*\\\$category\s*===\s*'Табель_ШМ_ШМР'\s*\)\s*{([^}]+)}/";
if (preg_match($pattern, $fileContent, $matches)) {
    echo "   ✅ Found handler for 'Табель_ШМ_ШМР'\n";
    echo "   Handler content snippet: " . trim($matches[1]) . "\n";
} else {
    echo "   ❌ Handler for 'Табель_ШМ_ШМР' not found!\n";
}

echo "\n2. LOOKING FOR GENERIC ACTIVITY TYPE HANDLER:\n";
$pattern = "/if \(\s*in_array\(\s*\\\$strippedCategory,\s*\\\$this->activityTypes\s*\)\s*\)\s*{/";
if (preg_match($pattern, $fileContent)) {
    echo "   ✅ Found generic activity type handler\n";
} else {
    echo "   ❌ Generic activity type handler not found!\n";
}

echo "\n3. CHECK HANDLER ORDER:\n";
$submenuPos = strpos($fileContent, "if (\$category === 'Табель_ШМ_ШМР')");
$activityTypePos = strpos($fileContent, "if (in_array(\$strippedCategory, \$this->activityTypes))");

if ($submenuPos !== false && $activityTypePos !== false) {
    if ($submenuPos < $activityTypePos) {
        echo "   ✅ Submenu handler comes BEFORE activity type handler (correct order)\n";
        echo "   Submenu handler at position: {$submenuPos}\n";
        echo "   Activity type handler at position: {$activityTypePos}\n";
    } else {
        echo "   ❌ Activity type handler comes BEFORE submenu handler (WRONG ORDER)\n";
        echo "   This would cause the issue!\n";
    }
} else {
    echo "   ❌ Could not find both handlers\n";
}

echo "\n4. CHECK FOR HANDLERSUPERVISIONINSTALLATIONWORKSMENU METHOD:\n";
if (strpos($fileContent, 'handleSupervisionInstallationWorksMenu') !== false) {
    echo "   ✅ handleSupervisionInstallationWorksMenu method exists\n";
} else {
    echo "   ❌ handleSupervisionInstallationWorksMenu method NOT found!\n";
}

echo "\n5. CHECK FOR CREATESUPERVISIONINSTALLATIONWORKSKEYBOARD METHOD:\n";
$messageServiceContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\MessageService.php');
if (strpos($messageServiceContent, 'createSupervisionInstallationWorksKeyboard') !== false) {
    echo "   ✅ createSupervisionInstallationWorksKeyboard method exists in MessageService\n";
} else {
    echo "   ❌ createSupervisionInstallationWorksKeyboard method NOT found in MessageService!\n";
}

echo "\n6. CHECK SUPERVISION WORK TYPES HANDLER CONFLICT:\n";
$pattern = "/if \(\s*in_array\(\s*\\\$category,\s*\[\s*'Табель_ШМР',\s*'Табель_ШМ_Простой'\s*\]\s*\)\s*\)/";
if (preg_match($pattern, $fileContent)) {
    echo "   ⚠️  Found supervision work types handler that checks for 'Табель_ШМР'\n";
    echo "   This should NOT conflict with 'Табель_ШМ_ШМР'\n";
} else {
    echo "   ✅ No conflicting supervision work types handler found\n";
}

?>
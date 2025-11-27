<?php
/**
 * Test script to verify Automation "Прочее" button callback routing
 * This script verifies the current state without making changes
 */

echo "=== AUTOMATION ПРОЧЕЕ BUTTON FLOW TEST ===\n\n";

// Test 1: Check if callback is in confirmation handler list
echo "1. Checking if 'cat_ШМР_Автом_Проч_yes' is in confirmation handler list...\n";

$tabelHandlerFile = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Find the confirmation handler list
if (preg_match('/if\s*\(\$category\s*===\s*[\'"]cat_ТабЭлОб_Проч_yes[\'"].*?\{/s', $tabelHandlerFile, $matches)) {
    $handlerBlock = $matches[0];
    
    if (strpos($handlerBlock, 'cat_ШМР_Автом_Проч_yes') !== false) {
        echo "   ✅ Found 'cat_ШМР_Автом_Проч_yes' in confirmation handler list\n";
    } else {
        echo "   ❌ Missing 'cat_ШМР_Автом_Проч_yes' in confirmation handler list\n";
        echo "   🔍 Current handler list contains: " . substr($handlerBlock, 0, 200) . "...\n";
    }
} else {
    echo "   ⚠️  Could not find confirmation handler block\n";
}

// Test 2: Check callback mapping in extractCategoryFromCallback
echo "\n2. Checking callback mapping in extractCategoryFromCallback...\n";

if (preg_match('/shortFormats\s*=\s*\[.*?\];/s', $tabelHandlerFile, $matches)) {
    $shortFormatsBlock = $matches[0];
    
    if (strpos($shortFormatsBlock, 'cat_ШМР_Автом_Проч_yes') !== false) {
        echo "   ✅ Found 'cat_ШМР_Автом_Проч_yes' in shortFormats mapping\n";
    } else {
        echo "   ❌ Missing 'cat_ШМР_Автом_Проч_yes' in shortFormats mapping\n";
    }
} else {
    echo "   ⚠️  Could not find shortFormats block\n";
}

// Test 3: Check handleNewProcheeConfirmation method for automation handling
echo "\n3. Checking handleNewProcheeConfirmation method for automation handling...\n";

if (preg_match('/function handleNewProcheeConfirmation.*?elseif.*?cat_ШМР_Автом_Проч_yes.*?\{/s', $tabelHandlerFile, $matches)) {
    echo "   ✅ Found automation handling in handleNewProcheeConfirmation\n";
} else {
    echo "   ❌ Missing automation handling in handleNewProcheeConfirmation\n";
}

// Test 4: Check activity type mapping in getConfirmationMessage
echo "\n4. Checking activity type mapping in TabelEntry->getConfirmationMessage...\n";

$tabelEntryFile = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

if (strpos($tabelEntryFile, "'ШМР_Прочее_Автоматизация' => 'Прочее (Автоматизация)'") !== false) {
    echo "   ✅ Found user-friendly mapping for 'ШМР_Прочее_Автоматизация'\n";
} else {
    echo "   ❌ Missing user-friendly mapping for 'ШМР_Прочее_Автоматизация'\n";
}

// Test 5: Check isActivityExecution method
echo "\n5. Checking isActivityExecution method for automation callback...\n";

if (preg_match('/function isActivityExecution.*?cat_ШМР_Автом_Проч_yes/s', $tabelHandlerFile, $matches)) {
    echo "   ✅ Found 'cat_ШМР_Автом_Проч_yes' in isActivityExecution\n";
} else {
    echo "   ⚠️  'cat_ШМР_Автом_Проч_yes' not found in isActivityExecution (this may be normal)\n";
}

echo "\n=== EXPECTED FLOW FOR AUTOMATION ПРОЧЕЕ ===\n";
echo "1. User clicks: ШМР → Автоматизация → Прочее\n";
echo "2. System prompts for custom text input\n";
echo "3. User enters custom text (e.g., 'test work')\n";
echo "4. System shows confirmation: 'Прочее (Автоматизация), отправить запись в табель в проект XXX? Описание работы: test work'\n";
echo "5. User clicks 'Да' button (callback: cat_ШМР_Автом_Проч_yes)\n";
echo "6. System should call handleNewProcheeConfirmation and create tabel entry\n";

echo "\n=== DIAGNOSTICS COMPLETE ===\n";
echo "If the callback routing is properly configured but still failing,\n";
echo "the issue might be in the conversation state or callback data transformation.\n";
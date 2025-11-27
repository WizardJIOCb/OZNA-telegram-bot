<?php
/**
 * COMPREHENSIVE DEBUG: Trace the exact callback flow for "Да" button
 */

echo "=== COMPREHENSIVE CALLBACK DEBUGGING ===\n\n";

$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "STEP 1: Button Creation\n";
echo "======================\n";
echo "When user inputs custom text, system calls createConfirmationKeyboard('новое_прочее_yes')\n";
echo "MessageService creates button: ['text' => '✅ Да', 'callback_data' => 'новое_прочее_yes']\n\n";

echo "STEP 2: User Clicks 'Да'\n";
echo "========================\n";
echo "Telegram sends callback with data: 'новое_прочее_yes'\n\n";

echo "STEP 3: CallbackHandlerFactory Processing\n";
echo "=========================================\n";
echo "CallbackHandlerFactory receives: 'новое_прочее_yes'\n";
echo "Extracts category: 'новое_прочее_yes' (no prefix to strip)\n\n";

echo "STEP 4: Handler Selection\n";
echo "=========================\n";
echo "CallbackHandlerFactory checks which handler can handle 'новое_прочее_yes':\n\n";

// Test TabelHandler canHandle
if (strpos($tabelHandlerContent, "\$callbackData === 'новое_прочее_yes'") !== false) {
    echo "✅ TabelHandler::canHandle('новое_прочее_yes') = TRUE\n";
    echo "   TabelHandler will be selected to handle this callback\n\n";
} else {
    echo "❌ TabelHandler::canHandle('новое_прочее_yes') = FALSE\n";
    echo "   TabelHandler WILL NOT handle this callback - THIS IS THE PROBLEM!\n\n";
}

echo "STEP 5: TabelHandler Processing\n";
echo "===============================\n";
echo "TabelHandler::handle() called with category: 'новое_прочее_yes'\n\n";

echo "STEP 6: Routing in routeTabelCallback\n";
echo "=====================================\n";
if (strpos($tabelHandlerContent, "\$category === 'новое_прочее_yes'") !== false) {
    echo "✅ Found routing: if (\$category === 'новое_прочее_yes')\n";
    echo "   Will call handleNewProcheeConfirmation()\n\n";
} else {
    echo "❌ No routing found for 'новое_прочее_yes'\n";
    echo "   Callback will be ignored or go to default handler\n\n";
}

echo "STEP 7: Method Execution\n";
echo "========================\n";
if (strpos($tabelHandlerContent, 'function handleNewProcheeConfirmation') !== false) {
    echo "✅ handleNewProcheeConfirmation method exists\n";
    echo "   Will create tabel entry if called\n\n";
} else {
    echo "❌ handleNewProcheeConfirmation method missing\n\n";
}

echo "=== DETAILED ANALYSIS ===\n\n";

echo "1. Check if canHandle includes 'новое_прочее_yes':\n";
$canHandlePattern = "/\\\$callbackData === 'новое_прочее_yes'/";
if (preg_match($canHandlePattern, $tabelHandlerContent)) {
    echo "   ✅ FOUND in canHandle method\n";
} else {
    echo "   ❌ NOT FOUND in canHandle method - THIS BREAKS THE FLOW!\n";
}

echo "\n2. Check if routing includes 'новое_прочее_yes':\n";
$routingPattern = "/\\\$category === 'новое_прочее_yes'/";
if (preg_match($routingPattern, $tabelHandlerContent)) {
    echo "   ✅ FOUND in routing logic\n";
} else {
    echo "   ❌ NOT FOUND in routing logic\n";
}

echo "\n3. Check if method exists:\n";
if (strpos($tabelHandlerContent, 'handleNewProcheeConfirmation') !== false) {
    echo "   ✅ Method exists\n";
} else {
    echo "   ❌ Method missing\n";
}

echo "\n=== ROOT CAUSE ANALYSIS ===\n";

$hasCanHandle = strpos($tabelHandlerContent, "\$callbackData === 'новое_прочее_yes'") !== false;
$hasRouting = strpos($tabelHandlerContent, "\$category === 'новое_прочее_yes'") !== false;
$hasMethod = strpos($tabelHandlerContent, 'handleNewProcheeConfirmation') !== false;

if (!$hasCanHandle) {
    echo "🚨 CRITICAL ISSUE: TabelHandler cannot handle 'новое_прочее_yes' callback\n";
    echo "   The callback will be ignored by TabelHandler\n";
    echo "   Another handler might pick it up or it gets discarded\n\n";
    echo "🔧 FIX NEEDED: Add 'новое_прочее_yes' to canHandle method\n";
} elseif (!$hasRouting) {
    echo "🚨 ISSUE: TabelHandler can handle callback but has no routing\n";
    echo "   Callback will be handled but not routed correctly\n\n";
    echo "🔧 FIX NEEDED: Add routing for 'новое_прочее_yes'\n";
} elseif (!$hasMethod) {
    echo "🚨 ISSUE: Routing exists but method is missing\n";
    echo "   Will cause PHP fatal error when called\n\n";
    echo "🔧 FIX NEEDED: Implement handleNewProcheeConfirmation method\n";
} else {
    echo "✅ ALL COMPONENTS PRESENT: Flow should work correctly\n\n";
    echo "If button still doesn't work, check:\n";
    echo "   1. Web server restart (required for handler changes)\n";
    echo "   2. Database connection issues\n";
    echo "   3. User data validation (FIO, functional direction)\n";
    echo "   4. TabelEntry service issues\n";
}

echo "\n=== SUMMARY ===\n";
echo "Expected flow: Button Click → Callback → canHandle → Routing → Method → Database → Success\n";
echo "Check each step in the server logs to identify where the flow breaks.\n";

echo "\n✅ COMPREHENSIVE DEBUG COMPLETED\n";
?>
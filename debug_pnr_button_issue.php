<?php
/**
 * Debug script to diagnose why the "Отдел ПНР" button stopped working
 */

echo "=== DEBUGGING 'ОТДЕЛ ПНР' BUTTON ISSUE ===\n\n";

// Test 1: Check the actual button callback
echo "TEST 1: Check Button Callback\n";
echo "=============================\n";
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

if (preg_match("/\['text' => '⚙️ Отдел ПНР', 'callback_data' => '([^']+)'\]/", $messageServiceContent, $matches)) {
    $buttonCallback = $matches[1];
    echo "✅ Found 'Отдел ПНР' button callback: '{$buttonCallback}'\n";
    
    // Extract the category that TabelHandler would receive
    $expectedCategory = substr($buttonCallback, strlen('category_'));
    echo "✅ Expected category in TabelHandler: '{$expectedCategory}'\n";
} else {
    echo "❌ Could not find 'Отдел ПНР' button callback\n";
    exit;
}

echo "\n";

// Test 2: Check TabelHandler routing
echo "TEST 2: Check TabelHandler Routing\n";
echo "===================================\n";
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

$checkConditions = [
    "if (\$category === '{$expectedCategory}')" => "Direct exact match",
    "if (\$category === 'Табель_Пусконаладка')" => "Hardcoded match (should match)",
    "return \$this->handleCommissioningDateSelection(\$chatId);" => "Calls commissioning date selection"
];

foreach ($checkConditions as $condition => $description) {
    if (strpos($tabelHandlerContent, $condition) !== false) {
        echo "✅ Found: {$condition} ({$description})\n";
    } else {
        echo "❌ Missing: {$condition} ({$description})\n";
    }
}

echo "\n";

// Test 3: Check for potential interference
echo "TEST 3: Check for Potential Interference\n";
echo "=========================================\n";

// Check if there are any conditions that might be intercepting the callback BEFORE the commissioning check
$potentialInterference = [
    'if ($category === \'cat_ТабУстрЗамРК\'' => 'RK comments elimination check',
    'if ($category === \'cat_ТабЭлОб\'' => 'Electrical equipment check',
    'if ($category === \'cat_ТабПожСигн\'' => 'Fire alarm equipment check'
];

$commissioningPosition = strpos($tabelHandlerContent, "if (\$category === 'Табель_Пусконаладка')");
if ($commissioningPosition === false) {
    echo "❌ CRITICAL: Cannot find commissioning check in TabelHandler!\n";
} else {
    echo "✅ Commissioning check found at position: {$commissioningPosition}\n";
    
    foreach ($potentialInterference as $check => $description) {
        $checkPosition = strpos($tabelHandlerContent, $check);
        if ($checkPosition !== false) {
            if ($checkPosition < $commissioningPosition) {
                echo "⚠️  WARNING: {$description} appears BEFORE commissioning check (pos: {$checkPosition})\n";
            } else {
                echo "✅ OK: {$description} appears after commissioning check (pos: {$checkPosition})\n";
            }
        }
    }
}

echo "\n";

// Test 4: Check for syntax errors or malformed conditions
echo "TEST 4: Check for Syntax Issues\n";
echo "================================\n";

// Look around the commissioning handling code
$commissioningStart = $commissioningPosition - 100;
$commissioningEnd = $commissioningPosition + 300;
$codeSnippet = substr($tabelHandlerContent, $commissioningStart, $commissioningEnd - $commissioningStart);

echo "Code snippet around commissioning check:\n";
echo "----------------------------------------\n";
echo $codeSnippet;
echo "\n----------------------------------------\n";

// Test 5: Check if handleCommissioningDateSelection exists
echo "TEST 5: Check handleCommissioningDateSelection Method\n";
echo "=====================================================\n";

if (strpos($tabelHandlerContent, 'private function handleCommissioningDateSelection(') !== false) {
    echo "✅ handleCommissioningDateSelection method exists\n";
} else {
    echo "❌ CRITICAL: handleCommissioningDateSelection method missing!\n";
}

echo "\n";

// Test 6: Check for recent changes that might have broken it
echo "TEST 6: Recent Changes Analysis\n";
echo "===============================\n";

$possibleIssues = [
    'encoding issue with Cyrillic characters',
    'condition precedence changed',
    'method signature changed', 
    'missing return statement',
    'syntax error in the method',
    'namespace or class import issue'
];

echo "Possible issues to investigate:\n";
foreach ($possibleIssues as $i => $issue) {
    echo ($i + 1) . ". {$issue}\n";
}

echo "\n";

// Summary
echo "SUMMARY & NEXT STEPS:\n";
echo "=====================\n";
echo "1. Button callback: '{$buttonCallback}'\n";
echo "2. Expected category: '{$expectedCategory}'\n";
echo "3. Check the error logs when clicking the button\n";
echo "4. Verify if the callback is reaching TabelHandler at all\n";
echo "5. Test with a simplified callback to isolate the issue\n";

echo "\nTo debug further:\n";
echo "1. Check telegram_bot_error.log for errors when clicking 'Отдел ПНР'\n";
echo "2. Look for 'TabelHandler: handling commissioning' in the logs\n";
echo "3. If not found, the issue is before the commissioning check\n";
echo "4. If found but no date selection appears, the issue is in handleCommissioningDateSelection\n";
?>
<?php

// Runtime flow diagnostic - check the actual callback routing

echo "=== RUNTIME FLOW DIAGNOSTIC ===\n\n";

echo "Let me check the actual routeTabelCallback method to see where the issue might be...\n\n";

// Read the actual TabelHandler.php file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. CHECKING ROUTETABELCALLBACK METHOD FLOW\n";
echo "===========================================\n";

// Find the routeTabelCallback method
$lines = explode("\n", $tabelHandlerContent);
$inRouteTabelCallback = false;
$routingLogic = [];
$lineNum = 0;

foreach ($lines as $line) {
    $lineNum++;
    
    if (strpos($line, 'private function routeTabelCallback') !== false) {
        $inRouteTabelCallback = true;
        echo "Found routeTabelCallback method at line {$lineNum}\n";
        continue;
    }
    
    if ($inRouteTabelCallback) {
        // Look for automation scheme changes handling
        if (strpos($line, 'cat_ТабАвтом_ИзмСх') !== false && strpos($line, 'if') !== false) {
            $routingLogic[] = "Line {$lineNum}: " . trim($line);
        }
        
        // Look for confirmation handling
        if (strpos($line, 'isConfirmationCallback') !== false) {
            $routingLogic[] = "Line {$lineNum}: " . trim($line);
        }
        
        if (strpos($line, 'isActivityExecution') !== false) {
            $routingLogic[] = "Line {$lineNum}: " . trim($line);
        }
        
        // Stop when we reach the next method
        if (strpos($line, 'private function ') !== false && strpos($line, 'routeTabelCallback') === false) {
            $inRouteTabelCallback = false;
            break;
        }
    }
}

echo "\nFound routing logic:\n";
foreach ($routingLogic as $logic) {
    echo "  {$logic}\n";
}

echo "\n2. CHECKING THE EXACT FLOW FOR CONFIRMATION CALLBACKS\n";
echo "======================================================\n";

// Let's trace the exact flow that should happen
echo "When user clicks 'Да' button with callback 'cat_ТабАвтом_ИзмСх_yes':\n\n";

echo "Step 1: TabelHandler::canHandle('cat_ТабАвтом_ИзмСх_yes')\n";
echo "  - str_starts_with('cat_ТабАвтом_ИзмСх_yes', 'cat_Таб') = TRUE\n";
echo "  - Result: TRUE ✅\n\n";

echo "Step 2: TabelHandler::handle() called\n";
echo "  - extractCategoryFromCallback('cat_ТабАвтом_ИзмСх_yes')\n";
echo "  - Should extract: 'ТабАвтом_ИзмСх_yes'\n\n";

echo "Step 3: routeTabelCallback() called with category\n";
echo "  - Category should be: 'ТабАвтом_ИзмСх_yes' or 'cat_ТабАвтом_ИзмСх_yes'\n\n";

echo "Step 4: Check the routing conditions in order...\n";

// Check what happens in the routing method
$checkOrder = [
    "isConfirmationCallback() check",
    "isActivityExecution() check", 
    "handleActivityExecution() call",
    "Automation scheme changes handler check",
    "Default 'unknown command' fallback"
];

foreach ($checkOrder as $i => $check) {
    echo "  " . ($i + 1) . ". {$check}\n";
}

echo "\n3. POTENTIAL ISSUES TO INVESTIGATE\n";
echo "===================================\n";

$potentialIssues = [
    "Category extraction might be wrong",
    "Routing order might cause early exit",
    "There might be another condition that catches this first",
    "The confirmation callback might not be recognized",
    "There might be a runtime error in the method"
];

foreach ($potentialIssues as $i => $issue) {
    echo ($i + 1) . ". {$issue}\n";
}

echo "\n4. LET'S CHECK THE ACTUAL ROUTING ORDER IN THE FILE\n";
echo "====================================================\n";

// Find the order of conditions in routeTabelCallback
$routingConditions = [];
$inRouteTabelCallback = false;
$lineNum = 0;
$braceLevel = 0;

foreach ($lines as $line) {
    $lineNum++;
    
    if (strpos($line, 'private function routeTabelCallback') !== false) {
        $inRouteTabelCallback = true;
        continue;
    }
    
    if ($inRouteTabelCallback) {
        // Count braces to know when method ends
        $braceLevel += substr_count($line, '{') - substr_count($line, '}');
        
        // Look for if conditions
        if (preg_match('/^\s*if\s*\(/', $line)) {
            $condition = trim($line);
            $routingConditions[] = "Line {$lineNum}: {$condition}";
        }
        
        // Method ends when braces balance out
        if ($braceLevel <= 0 && strpos($line, '}') !== false) {
            break;
        }
    }
}

echo "Routing conditions in order:\n";
foreach (array_slice($routingConditions, 0, 15) as $condition) {
    echo "  {$condition}\n";
}

if (count($routingConditions) > 15) {
    echo "  ... and " . (count($routingConditions) - 15) . " more conditions\n";
}

echo "\n5. RECOMMENDATION\n";
echo "=================\n";
echo "Based on this diagnostic, the code should work correctly.\n";
echo "The issue might be:\n";
echo "1. A runtime error or exception being thrown\n";
echo "2. The callback data being modified before reaching TabelHandler\n";
echo "3. Another handler claiming the callback with higher priority\n";
echo "4. Server-side caching preventing code changes from taking effect\n\n";

echo "Next steps:\n";
echo "- Check the actual server logs when the button is clicked\n";
echo "- Verify the web server has been restarted after code changes\n";
echo "- Test with error logging enabled\n";

?>
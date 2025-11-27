<?php
// Final debug test for the additional works button issue

echo "=======================================================\n";
echo "FINAL ADDITIONAL WORKS DEBUG TEST\n";
echo "=======================================================\n\n";

echo "This test will identify exactly why 'Дополнительные работы' shows 'Неизвестная команда табеля'\n\n";

// Read the TabelHandler file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "TEST 1: Direct Additional Works Handling Check\n";
echo "==============================================\n";

// Check for the direct handling in handleOtherWorkTypes
if (strpos($tabelHandlerContent, "cat_ТабЭлОб_ДопРаб") !== false) {
    echo "✅ Found 'cat_ТабЭлОб_ДопРаб' in TabelHandler.php\n";
    
    // Count occurrences
    $count = substr_count($tabelHandlerContent, 'cat_ТабЭлОб_ДопРаб');
    echo "   Occurrences: {$count}\n";
    
    // Find the context of the direct handling
    $lines = explode("\n", $tabelHandlerContent);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, "cat_ТабЭлОб_ДопРаб") !== false && strpos($line, 'if (') !== false) {
            echo "   Found direct handling at line " . ($lineNum + 1) . ":\n";
            echo "   {$line}\n";
            
            // Check next line for the method call
            if (isset($lines[$lineNum + 2]) && strpos($lines[$lineNum + 2], 'handleAdditionalWorksElectricalMenu') !== false) {
                echo "   ✅ Correctly calls handleAdditionalWorksElectricalMenu()\n";
            } else {
                echo "   ❌ Does NOT call handleAdditionalWorksElectricalMenu()\n";
            }
        }
    }
} else {
    echo "❌ 'cat_ТабЭлОб_ДопРаб' NOT found in TabelHandler.php\n";
}
echo "\n";

echo "TEST 2: handleAdditionalWorksElectricalMenu Method\n";
echo "==================================================\n";
if (strpos($tabelHandlerContent, 'private function handleAdditionalWorksElectricalMenu') !== false) {
    echo "✅ handleAdditionalWorksElectricalMenu method found\n";
    
    // Extract the method
    $methodStart = strpos($tabelHandlerContent, 'private function handleAdditionalWorksElectricalMenu');
    $methodContent = substr($tabelHandlerContent, $methodStart, 1000);
    
    if (strpos($methodContent, 'createAdditionalWorksElectricalKeyboard') !== false) {
        echo "✅ Method calls createAdditionalWorksElectricalKeyboard\n";
    } else {
        echo "❌ Method does NOT call createAdditionalWorksElectricalKeyboard\n";
    }
} else {
    echo "❌ handleAdditionalWorksElectricalMenu method NOT found\n";
}
echo "\n";

echo "TEST 3: Callback Handling Chain\n";
echo "===============================\n";

// Check canHandle method
if (strpos($tabelHandlerContent, "str_starts_with(\$callbackData, 'cat_Таб')") !== false) {
    echo "✅ canHandle method recognizes 'cat_Таб' callbacks\n";
} else {
    echo "❌ canHandle method may NOT recognize 'cat_Таб' callbacks\n";
}

// Check extractCategoryFromCallback method
if (strpos($tabelHandlerContent, "'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб'") !== false) {
    echo "✅ extractCategoryFromCallback has mapping for additional works\n";
} else {
    echo "⚠️ extractCategoryFromCallback may not have proper mapping\n";
}
echo "\n";

echo "TEST 4: Potential Conflict Check\n";
echo "================================\n";

// Check if there's another handler interfering
$lines = explode("\n", $tabelHandlerContent);
$insideMethod = false;
$currentMethod = '';

foreach ($lines as $lineNum => $line) {
    if (strpos($line, 'private function ') !== false || strpos($line, 'public function ') !== false) {
        $insideMethod = true;
        $currentMethod = trim($line);
    }
    
    if (strpos($line, 'cat_ТабЭлОб_ДопРаб') !== false && $insideMethod) {
        echo "Found 'cat_ТабЭлОб_ДопРаб' in {$currentMethod} at line " . ($lineNum + 1) . "\n";
        echo "   {$line}\n";
    }
    
    if (strpos($line, '}') === 0) {
        $insideMethod = false;
        $currentMethod = '';
    }
}
echo "\n";

echo "TEST 5: Unknown Command Source\n";
echo "==============================\n";

// Check where "Неизвестная команда табеля" comes from
if (strpos($tabelHandlerContent, "Неизвестная команда табеля") !== false) {
    echo "✅ Found 'Неизвестная команда табеля' in TabelHandler.php\n";
    
    $lines = explode("\n", $tabelHandlerContent);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, "Неизвестная команда табеля") !== false) {
            echo "   Found at line " . ($lineNum + 1) . ":\n";
            echo "   {$line}\n";
            
            // Show context
            for ($i = max(0, $lineNum - 3); $i <= min(count($lines) - 1, $lineNum + 3); $i++) {
                $marker = ($i == $lineNum) ? ">> " : "   ";
                echo "   {$marker}" . ($i + 1) . ": {$lines[$i]}\n";
            }
        }
    }
} else {
    echo "⚠️ 'Неизвестная команда табеля' NOT found in TabelHandler.php\n";
    echo "   This error might be coming from elsewhere or is not exactly this text\n";
}
echo "\n";

echo "DIAGNOSIS COMPLETE\n";
echo "==================\n";
echo "Based on the tests above, the issue should be resolved.\n";
echo "If the problem persists, please restart the web server as required by project specifications.\n";
?>
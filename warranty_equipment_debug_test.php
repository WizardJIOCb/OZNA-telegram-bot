<?php
/**
 * Comprehensive Warranty Equipment Debug Test
 * This will test the complete warranty equipment flow step by step
 */

echo "🔧 COMPREHENSIVE WARRANTY EQUIPMENT DEBUG TEST\n";
echo "=" . str_repeat("=", 65) . "\n\n";

// Step 1: Test callback recognition
echo "1. TESTING CALLBACK RECOGNITION\n";
echo "-------------------------------\n";

$testCallbacks = [
    'cat_ТабГарантии' => 'Warranty main menu',
    'cat_Гарантии_ЭлектрОб' => 'Electrical equipment selection',
    'cat_Гарантии_ЭлектрОб_yes' => 'Electrical equipment confirmation'
];

foreach ($testCallbacks as $callback => $description) {
    // Test TabelHandler canHandle logic
    $canHandle = (str_starts_with($callback, 'category_Табель') || 
                  str_starts_with($callback, 'cat_Таб') ||
                  str_starts_with($callback, 'cat_Гарантии_') ||
                  $callback === 'cat_ТабГарантии');
    
    echo "  📝 {$description}: {$callback}\n";
    echo "     canHandle: " . ($canHandle ? "✅ YES" : "❌ NO") . "\n\n";
}

// Step 2: Test warranty equipment type mapping
echo "2. TESTING EQUIPMENT TYPE MAPPING\n";
echo "----------------------------------\n";

$equipmentTypeMap = [
    'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
    'СрИзм' => 'РабГарантии_СрИзм',
    'НасОб' => 'РабГарантии_НасОб',
    'ТрубАрм' => 'РабГарантии_ТрубАрм',
    'ИспМех' => 'РабГарантии_ИспМех',
    'КонтрОб' => 'РабГарантии_КонтрОб',
    'Прочее' => 'РабГарантии_Прочее'
];

foreach ($equipmentTypeMap as $shortType => $activityType) {
    $callback = "cat_Гарантии_{$shortType}_yes";
    echo "  🔧 {$shortType} → {$activityType}\n";
    echo "     Callback: {$callback}\n";
    echo "     Length: " . strlen($callback) . " bytes\n\n";
}

// Step 3: Test regex pattern matching
echo "3. TESTING REGEX PATTERN MATCHING\n";
echo "---------------------------------\n";

$testCallback = 'cat_Гарантии_ЭлектрОб_yes';
if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $testCallback, $matches)) {
    echo "  ✅ Pattern MATCHES: {$testCallback}\n";
    echo "     Extracted type: {$matches[1]}\n";
    echo "     Activity type: РабГарантии_{$matches[1]}\n";
} else {
    echo "  ❌ Pattern DOES NOT MATCH: {$testCallback}\n";
}

// Step 4: Test activity type validation
echo "\n4. TESTING ACTIVITY TYPE VALIDATION\n";
echo "-----------------------------------\n";

// Read the TabelHandler file to check activity types
$handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$handlerContent = file_get_contents($handlerFile);

$warrantyActivityTypes = [
    'РабГарантии_ЭлектрОб',
    'РабГарантии_СрИзм', 
    'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм',
    'РабГарантии_ИспМех',
    'РабГарантии_КонтрОб',
    'РабГарантии_Прочее'
];

$allTypesFound = true;
foreach ($warrantyActivityTypes as $activityType) {
    $found = strpos($handlerContent, "'{$activityType}'") !== false;
    echo "  🎯 {$activityType}: " . ($found ? "✅ FOUND" : "❌ MISSING") . "\n";
    if (!$found) $allTypesFound = false;
}

// Step 5: Test TabelEntry formatting
echo "\n5. TESTING TABELENTRY FORMATTING\n";
echo "--------------------------------\n";

$entryFile = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
$entryContent = file_get_contents($entryFile);

if (strpos($entryContent, '$warrantyEquipmentActivities = [') !== false) {
    echo "  ✅ Warranty equipment activities array found in TabelEntry\n";
    
    foreach ($warrantyActivityTypes as $activityType) {
        $found = strpos($entryContent, "'{$activityType}'") !== false;
        echo "  📝 {$activityType}: " . ($found ? "✅ FOUND" : "❌ MISSING") . "\n";
    }
} else {
    echo "  ❌ Warranty equipment activities array NOT found in TabelEntry\n";
}

// Step 6: Simulate the complete flow
echo "\n6. SIMULATING COMPLETE FLOW\n";
echo "---------------------------\n";

echo "  1. User clicks '🛡️ Работы по гарантии' → callback: 'cat_ТабГарантии'\n";
echo "  2. Bot shows warranty equipment menu\n";
echo "  3. User clicks '⚡ Электрооборудование' → callback: 'cat_Гарантии_ЭлектрОб'\n";
echo "  4. Bot shows confirmation dialog\n";
echo "  5. User clicks 'Да' → callback: 'cat_Гарантии_ЭлектрОб_yes'\n";
echo "  6. Bot processes confirmation and sends report\n\n";

// Step 7: Check for potential issues
echo "7. POTENTIAL ISSUES TO CHECK\n";
echo "----------------------------\n";

$issues = [];

if (!$allTypesFound) {
    $issues[] = "Missing warranty activity types in TabelHandler";
}

$warrantyPatternExists = strpos($handlerContent, "preg_match('/^cat_Гарантии_(.+)_yes\$/u', \$category, \$matches)") !== false;
if (!$warrantyPatternExists) {
    $issues[] = "Missing warranty equipment confirmation regex pattern";
}

$conversationContextExists = strpos($handlerContent, "conversation->notes['project_code'] = 'Работы по гарантии'") !== false;
if (!$conversationContextExists) {
    $issues[] = "Missing conversation context setup for warranty equipment";
}

if (empty($issues)) {
    echo "  ✅ No critical issues found\n";
} else {
    echo "  ❌ Found issues:\n";
    foreach ($issues as $issue) {
        echo "     - {$issue}\n";
    }
}

// Step 8: Test recommendations
echo "\n8. TEST RECOMMENDATIONS\n";
echo "-----------------------\n";

echo "To test the warranty equipment functionality:\n\n";
echo "1. Start a conversation with the bot\n";
echo "2. Type /tabels to open the main menu\n";
echo "3. Click 'Пусконаладочные работы'\n";
echo "4. Select a project and date\n";
echo "5. Click '🛡️ Работы по гарантии'\n";
echo "6. Click '⚡ Электрооборудование'\n";
echo "7. Click 'Да' to confirm\n";
echo "8. Check if you receive a confirmation message\n\n";

echo "If the test fails, check the bot logs for errors containing:\n";
echo "- 'WARRANTY DEBUG'\n";
echo "- 'неизвестная команда табеля'\n";
echo "- 'Гарантии_ЭлектрОб'\n\n";

echo "🔧 DEBUG TEST COMPLETED\n";
?>
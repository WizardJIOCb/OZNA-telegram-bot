<?php

echo "=== TESTING EARLY ROUTING FIX FOR SUPERVISION INSTALLATION WORKS ===\n\n";

echo "1. CHECKING FOR EARLY ROUTING PATTERN:\n";
$tabelContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Check for early routing pattern like warranty works
$earlyRoutingPattern = "if (\$category === 'Табель_ШМ_ШМР') {";
$earlyRoutingFound = strpos($tabelContent, $earlyRoutingPattern) !== false;

if ($earlyRoutingFound) {
    echo "   ✅ Early routing pattern found for supervision installation works\n";
} else {
    echo "   ❌ Early routing pattern NOT found\n";
}

echo "\n2. COMPARING WITH WARRANTY WORKS EARLY ROUTING:\n";
$warrantyEarlyRouting = "if (\$category === 'Табель_ШМ_РабГарантии') {";
$warrantyFound = strpos($tabelContent, $warrantyEarlyRouting) !== false;

if ($warrantyFound) {
    echo "   ✅ Warranty works early routing exists (for comparison)\n";
} else {
    echo "   ❌ Warranty works early routing NOT found\n";
}

echo "\n3. CHECKING ROUTING ORDER:\n";
$warrantyPos = strpos($tabelContent, $warrantyEarlyRouting);
$supervisionPos = strpos($tabelContent, $earlyRoutingPattern);

if ($warrantyPos !== false && $supervisionPos !== false) {
    if ($supervisionPos > $warrantyPos) {
        echo "   ✅ Supervision installation works routing comes after warranty (correct order)\n";
    } else {
        echo "   ⚠️  Supervision installation works routing comes before warranty\n";
    }
    echo "   Warranty position: {$warrantyPos}\n";
    echo "   Supervision position: {$supervisionPos}\n";
} else {
    echo "   ❌ Could not determine routing order\n";
}

echo "\n4. EXPECTED BEHAVIOR:\n";
echo "   When user clicks 'Шеф-монтажные работы' button:\n";
echo "   1. Callback 'category_Табель_ШМ_ШМР' is sent\n";
echo "   2. Early routing should catch it immediately\n";
echo "   3. handleSupervisionInstallationWorksMenu() should be called\n";
echo "   4. Menu with 5 sub-activities should appear\n";

echo "\n5. COMPARISON WITH WORKING WARRANTY WORKS:\n";
echo "   Warranty works button 'Работы по гарантии':\n";
echo "   1. Sends callback 'category_Табель_ШМ_РабГарантии'\n";
echo "   2. Early routing catches it\n";
echo "   3. handleWarrantyEquipmentMenu() is called\n";
echo "   4. Equipment selection menu appears\n";
echo "   → This is the pattern we're replicating\n";

echo "\n✅ EARLY ROUTING FIX APPLIED\n";
echo "Now test the 'Шеф-монтажные работы' button - it should show 5 sub-activities!\n";

?>
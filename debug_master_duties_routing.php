<?php
echo "🔍 DEBUGGING MASTER DUTIES ROUTING ISSUE\n";
echo "==========================================\n\n";

// Test callback that's actually sent by the supervision project activity keyboard
$actualCallback = 'category_Табель_ИsполнениеОбязМастера';
echo "Actual button callback: '{$actualCallback}'\n";
echo "Callback length: " . strlen($actualCallback) . " bytes\n";
echo "Callback bytes: " . bin2hex($actualCallback) . "\n\n";

// Check if this matches the conditions in line 2125
$line2125_condition1 = ($actualCallback === 'Табель_ИсполнениеОбязМастера');
$line2125_condition2 = ($actualCallback === 'cat_ТабИсполнОбяз');
echo "Line 2125 conditions:\n";
echo "  category === 'Табель_ИсполнениеОбязМастера': " . ($line2125_condition1 ? 'TRUE' : 'FALSE') . "\n";
echo "  category === 'cat_ТабИсполнОбяз': " . ($line2125_condition2 ? 'TRUE' : 'FALSE') . "\n";
echo "  Combined (OR): " . (($line2125_condition1 || $line2125_condition2) ? 'TRUE' : 'FALSE') . "\n\n";

// Check if this matches the handler in line 2833
$line2833_condition = ($actualCallback === 'Табель_ИсполнениеОбязМастера');
echo "Line 2833 handler condition:\n";
echo "  category === 'Табель_ИsполнениеОбязМастера': " . ($line2833_condition ? 'TRUE' : 'FALSE') . "\n\n";

// The actual issue: The button sends 'category_Табель_ИsполнениеОбязМастера'
// But handlers are looking for 'Табель_ИsполнениеОбязМастера' (without 'category_' prefix)

echo "❌ PROBLEM IDENTIFIED:\n";
echo "The button sends: 'category_Табель_ИsполнениеОбязМастера'\n";
echo "But handlers check: 'Табель_ИsполнениеОбязМастера' (without prefix)\n\n";

echo "🔧 SOLUTION:\n";
echo "The callback needs to be processed through extractCategoryFromCallback() first\n";
echo "OR the handler condition needs to include the full callback format\n\n";

// Test extractCategoryFromCallback logic
echo "📋 Extract Category Logic Test:\n";
$mapping = [
    'category_Табель_ИsполнениеОбязМастера' => 'Табель_ИsполнениеОбязМастера',
    'cat_ТабИsполнОбяз' => 'Табель_ИsполнениеОбязМастера',
    'cat_ТабИsполнОбяз_yes' => 'Табель_ИsполнениеОбязМастера_yes'
];

if (isset($mapping[$actualCallback])) {
    $extractedCategory = $mapping[$actualCallback];
    echo "Extracted category: '{$extractedCategory}'\n";
    
    $finalHandler = ($extractedCategory === 'Табель_ИsполнениеОбязМастера');
    echo "Handler match: " . ($finalHandler ? 'TRUE ✅' : 'FALSE ❌') . "\n";
} else {
    echo "No mapping found for: '{$actualCallback}' ❌\n";
    echo "Need to add mapping to extractCategoryFromCallback!\n";
}
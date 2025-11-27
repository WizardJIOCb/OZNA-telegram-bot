<?php
/**
 * Deep warranty equipment flow analysis
 */

echo "=== DEEP WARRANTY EQUIPMENT FLOW ANALYSIS ===\n";

$handlerFile = 'CustomCommands/Handlers/TabelHandler.php';
$content = file_get_contents($handlerFile);

// Test the complete flow path
echo "1. Testing complete warranty flow path...\n";

// Step 1: Main warranty menu
$mainMenuPattern = "cat_ТабГарантии.*handleWarrantyEquipmentMenu";
if (preg_match('/' . preg_quote("cat_ТабГарантии") . '.*' . preg_quote("handleWarrantyEquipmentMenu") . '/s', $content)) {
    echo "   ✅ Main warranty menu routes to handleWarrantyEquipmentMenu\n";
} else {
    echo "   ❌ Main warranty menu routing issue\n";
}

// Step 2: Individual equipment handlers
echo "\n2. Checking individual equipment handlers...\n";
$equipmentTypes = [
    'ЭлектрОб' => 'Электрооборудование',
    'СрИзм' => 'Средства измерений', 
    'НасОб' => 'Насосное оборудование',
    'ТрубАрм' => 'Трубопроводная арматура',
    'ИспМех' => 'Исполнительные механизмы',
    'КонтрОб' => 'Контроллерное оборудование',
    'Прочее' => 'Прочее оборудование'
];

foreach ($equipmentTypes as $type => $description) {
    $handlerPattern = "Табель_РабГарантии_{$type}";
    if (strpos($content, $handlerPattern) !== false) {
        echo "   ✅ {$description} handler exists\n";
    } else {
        echo "   ❌ {$description} handler missing\n";
    }
}

// Step 3: Confirmation callback handling
echo "\n3. Checking confirmation callback handling...\n";
foreach ($equipmentTypes as $type => $description) {
    $callbackPattern = "cat_Гарантии_{$type}_yes";
    if (strpos($content, $callbackPattern) !== false) {
        echo "   ✅ {$description} confirmation callback exists\n";
    } else {
        echo "   ❌ {$description} confirmation callback missing\n";
    }
}

// Step 4: Activity types
echo "\n4. Checking activity types in activityTypes array...\n";
foreach ($equipmentTypes as $type => $description) {
    $activityType = "РабГарантии_{$type}";
    if (strpos($content, "'{$activityType}'") !== false) {
        echo "   ✅ {$description} activity type exists\n";
    } else {
        echo "   ❌ {$description} activity type missing\n";
    }
}

// Step 5: Check the complete flow from button to execution
echo "\n5. Checking complete flow for Electrical Equipment...\n";

// Button callback: category_Табель_РабГарантии_ЭлектрОб
echo "   Checking button callback 'category_Табель_РабГарантии_ЭлектрОб'...\n";
if (strpos($content, 'Табель_РабГарантии_ЭлектрОб') !== false) {
    echo "     ✅ Handler exists\n";
} else {
    echo "     ❌ Handler missing\n";
}

// Confirmation callback: cat_Гарантии_ЭлектрОб_yes
echo "   Checking confirmation callback 'cat_Гарантии_ЭлектрОб_yes'...\n";
if (strpos($content, 'cat_Гарантии_ЭлектрОб_yes') !== false) {
    echo "     ✅ Confirmation handler exists\n";
} else {
    echo "     ❌ Confirmation handler missing\n";
}

// Activity execution pattern matching
echo "   Checking activity execution pattern matching...\n";
if (preg_match('/cat_Гарантии_\(.+\)_yes/', $content)) {
    echo "     ✅ Activity execution pattern exists\n";
} else {
    echo "     ❌ Activity execution pattern missing\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";
<?php

echo "🔧 SIMPLE WARRANTY BUTTON DIAGNOSTIC\n";
echo "===================================\n\n";

// Read the TabelHandler file directly to check warranty functionality
$handlerFile = 'CustomCommands/Handlers/TabelHandler.php';

if (!file_exists($handlerFile)) {
    echo "❌ ERROR: TabelHandler.php not found!\n";
    exit;
}

$content = file_get_contents($handlerFile);

echo "📋 CHECKING WARRANTY BUTTON RECOGNITION\n";
echo "--------------------------------------\n";

// Check if warranty button patterns exist in canHandle method
$warrantyPatterns = [
    'cat_ТабГарантии',
    'Табель_РаботыПоГарантии',
    'cat_ТабГарант',
    'warranty equipment'
];

foreach ($warrantyPatterns as $pattern) {
    $found = strpos($content, $pattern) !== false;
    echo "  🔍 Pattern '{$pattern}': " . ($found ? "✅ FOUND" : "❌ NOT FOUND") . "\n";
}

echo "\n📋 CHECKING WARRANTY EQUIPMENT HANDLING\n";
echo "--------------------------------------\n";

// Check if warranty equipment handling exists
$warrantyMethods = [
    'handleWarrantyEquipmentMenu',
    'WARRANTY DEBUG',
    'WARRANTY EXECUTION DEBUG',
    'РабГарантии_ЭлектрОб'
];

foreach ($warrantyMethods as $method) {
    $found = strpos($content, $method) !== false;
    echo "  🔍 Method/Debug '{$method}': " . ($found ? "✅ FOUND" : "❌ NOT FOUND") . "\n";
}

echo "\n📋 CHECKING WARRANTY ACTIVITY TYPES\n";
echo "----------------------------------\n";

// Check warranty activity types in activityTypes array
$warrantyActivityTypes = [
    'РабГарантии_ЭлектрОб',
    'РабГарантии_СрИзм', 
    'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм',
    'РабГарантии_ИспМех',
    'РабГарантии_КонтрОб',
    'РабГарантии_Прочее'
];

foreach ($warrantyActivityTypes as $activityType) {
    $found = strpos($content, "'{$activityType}'") !== false;
    echo "  🎯 Activity type '{$activityType}': " . ($found ? "✅ FOUND" : "❌ NOT FOUND") . "\n";
}

echo "\n📋 CHECKING MESSAGESERVICE WARRANTY KEYBOARD\n";
echo "--------------------------------------------\n";

$messageServiceFile = 'CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $messageContent = file_get_contents($messageServiceFile);
    
    $warrantyKeyboardMethods = [
        'createWarrantyEquipmentKeyboard',
        'Работы по гарантии',
        '🛡️ Работы по гарантии'
    ];
    
    foreach ($warrantyKeyboardMethods as $method) {
        $found = strpos($messageContent, $method) !== false;
        echo "  🔍 Method/Text '{$method}': " . ($found ? "✅ FOUND" : "❌ NOT FOUND") . "\n";
    }
} else {
    echo "  ❌ MessageService.php not found\n";
}

echo "\n🎯 DIAGNOSTIC SUMMARY\n";
echo "====================\n";

// Check if all critical components exist
$criticalComponents = [
    'cat_ТабГарантии' => strpos($content, 'cat_ТабГарантии') !== false,
    'handleWarrantyEquipmentMenu' => strpos($content, 'handleWarrantyEquipmentMenu') !== false,
    'WARRANTY DEBUG' => strpos($content, 'WARRANTY DEBUG') !== false,
    'РабГарантии_ЭлектрОб' => strpos($content, 'РабГарантии_ЭлектрОб') !== false
];

$allPresent = true;
$missing = [];

foreach ($criticalComponents as $component => $present) {
    if (!$present) {
        $allPresent = false;
        $missing[] = $component;
    }
}

if ($allPresent) {
    echo "✅ ALL CRITICAL WARRANTY COMPONENTS ARE PRESENT\n\n";
    echo "💡 Since the code components exist, the issue is likely:\n";
    echo "1. 🔄 SERVER NOT RESTARTED after the warranty fixes\n";
    echo "2. 🌐 WEBHOOK/CONNECTIVITY issues preventing button responses\n";
    echo "3. 📍 WRONG MENU LOCATION - warranty button might be in a different place\n";
    echo "4. 👤 USER ACCESS LEVEL - warranty button might be restricted\n";
    echo "\n🔧 RECOMMENDED ACTION:\n";
    echo "1. Restart your bot server/webhook\n";
    echo "2. Check if you see '🛡️ Работы по гарантии' button in ПНР department\n";
    echo "3. Test if other buttons work to confirm bot connectivity\n";
    echo "4. Check your user access level\n";
} else {
    echo "❌ MISSING CRITICAL COMPONENTS:\n";
    foreach ($missing as $component) {
        echo "  - {$component}\n";
    }
    echo "\n🔧 RECOMMENDED ACTION:\n";
    echo "The warranty functionality code is incomplete. Please check the recent edits.\n";
}

echo "\n📝 QUICK TEST INSTRUCTIONS:\n";
echo "===========================\n";
echo "1. Send /start to your bot\n";
echo "2. Click 'Табель' button\n";
echo "3. Look for date selection, then work type selection\n";
echo "4. In work types, look for 'ПНР' or 'Пусконаладка'\n";
echo "5. In ПНР menu, look for '🛡️ Работы по гарантии' button\n";
echo "6. If you see it, click it - it should show equipment menu\n";
echo "7. Click 'Электрооборудование' - it should show confirmation\n";
echo "8. Click 'Да' - it should generate a report\n";
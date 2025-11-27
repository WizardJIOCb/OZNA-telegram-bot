<?php
// Test warranty equipment system implementation

echo "=== Testing Warranty Equipment System ===\n";

// Test 1: Check if warranty equipment activity types are in the activityTypes array
$tabelHandlerPath = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
$content = file_get_contents($tabelHandlerPath);

echo "\n1. Testing warranty equipment activity types in activityTypes array:\n";
$warrantyActivityTypes = [
    'РабГарантии_ЭлектрОб',
    'РабГарантии_СрИзм', 
    'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм',
    'РабГарантии_ИспМех',
    'РабГарантии_КонтрОб',
    'РабГарантии_Прочее'
];

foreach ($warrantyActivityTypes as $type) {
    if (strpos($content, "'$type'") !== false) {
        echo "✅ $type found in activityTypes\n";
    } else {
        echo "❌ $type NOT found in activityTypes\n";
    }
}

// Test 2: Check if warranty callbacks are in canHandle method
echo "\n2. Testing warranty equipment callbacks in canHandle method:\n";
$warrantyCallbacks = [
    'cat_ТабГарантии',
    'cat_ТабГарант'
];

foreach ($warrantyCallbacks as $callback) {
    if (strpos($content, $callback) !== false) {
        echo "✅ $callback found in canHandle method\n";
    } else {
        echo "❌ $callback NOT found in canHandle method\n";
    }
}

// Test 3: Check if warranty equipment handler method exists
echo "\n3. Testing warranty equipment handler method:\n";
if (strpos($content, 'private function handleWarrantyEquipmentCallback') !== false) {
    echo "✅ handleWarrantyEquipmentCallback method found\n";
} else {
    echo "❌ handleWarrantyEquipmentCallback method NOT found\n";
}

// Test 4: Check if warranty equipment activity mappings exist in TabelEntry.php
$tabelEntryPath = 'c:\ozna_bot\CustomCommands\Services\TabelEntry.php';
if (file_exists($tabelEntryPath)) {
    $entryContent = file_get_contents($tabelEntryPath);
    echo "\n4. Testing warranty equipment activity mappings in TabelEntry.php:\n";
    
    $warrantyMappings = [
        'РабГарантии_ЭлектрОб' => '⚡ Работы по гарантии (Электрооборудование)',
        'РабГарантии_СрИзм' => '📎 Работы по гарантии (Средства измерений)',
        'РабГарантии_НасОб' => '💧 Работы по гарантии (Насосное оборудование)',
        'РабГарантии_ТрубАрм' => '🔧 Работы по гарантии (Трубопроводная арматура)',
        'РабГарантии_ИспМех' => '⚙️ Работы по гарантии (Исполнительные механизмы)',
        'РабГарантии_КонтрОб' => '💻 Работы по гарантии (Контроллерное оборудование)', 
        'РабГарантии_Прочее' => '📦 Работы по гарантии (Прочее оборудование)'
    ];
    
    foreach ($warrantyMappings as $key => $mapping) {
        if (strpos($entryContent, "'$key'") !== false && strpos($entryContent, $mapping) !== false) {
            echo "✅ $key mapping found\n";
        } else {
            echo "❌ $key mapping NOT found\n";
        }
    }
} else {
    echo "❌ TabelEntry.php file not found\n";
}

// Test 5: Check if warranty equipment keyboard method exists in MessageService.php
$messageServicePath = 'c:\ozna_bot\CustomCommands\Services\MessageService.php';
if (file_exists($messageServicePath)) {
    $messageContent = file_get_contents($messageServicePath);
    echo "\n5. Testing warranty equipment keyboard method in MessageService.php:\n";
    
    if (strpos($messageContent, 'public function createWarrantyEquipmentKeyboard') !== false) {
        echo "✅ createWarrantyEquipmentKeyboard method found\n";
    } else {
        echo "❌ createWarrantyEquipmentKeyboard method NOT found\n";
    }
} else {
    echo "❌ MessageService.php file not found\n";
}

echo "\n=== Test Complete ===\n";
?>
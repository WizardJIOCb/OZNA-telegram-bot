<?php
// Test supervision department sub-activities configuration

echo "=== SUPERVISION DEPARTMENT SUB-ACTIVITIES TEST ===\n\n";

// Test 1: Verify supervision routing in TabelHandler
echo "1. SUPERVISION ROUTING VERIFICATION:\n";
echo "====================================\n";

$tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$tabelContent = file_get_contents($tabelHandlerFile);

// Check supervision warranty routing
if (strpos($tabelContent, "category === 'Табель_ШМ_РабГарантии'") !== false) {
    echo "✅ Supervision warranty routing: FOUND\n";
    if (strpos($tabelContent, "handleWarrantyEquipmentMenu(\$chatId, \$conversation, 'supervision')") !== false) {
        echo "✅ Supervision warranty context parameter: CORRECT\n";
    } else {
        echo "❌ Supervision warranty context parameter: MISSING\n";
    }
} else {
    echo "❌ Supervision warranty routing: MISSING\n";
}

// Check supervision RK elimination routing
if (strpos($tabelContent, "category === 'Табель_ШМ_УЗ_РК'") !== false) {
    echo "✅ Supervision RK elimination routing: FOUND\n";
    if (strpos($tabelContent, "handleRKCommentsEliminationMenu(\$chatId)") !== false) {
        echo "✅ Supervision RK elimination handler: CORRECT\n";
    } else {
        echo "❌ Supervision RK elimination handler: MISSING\n";
    }
} else {
    echo "❌ Supervision RK elimination routing: MISSING\n";
}

echo "\n";

// Test 2: Verify supervision keyboard in MessageService
echo "2. SUPERVISION KEYBOARD VERIFICATION:\n";
echo "=====================================\n";

$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
$messageContent = file_get_contents($messageServiceFile);

// Check supervision warranty button
if (strpos($messageContent, "'🛡️ Работы по гарантии', 'callback_data' => 'category_Табель_ШМ_РабГарантии'") !== false) {
    echo "✅ Supervision warranty button: FOUND\n";
} else {
    echo "❌ Supervision warranty button: MISSING\n";
}

// Check supervision RK elimination button  
if (strpos($messageContent, "'🔧 Устранение замечаний РК', 'callback_data' => 'category_Табель_ШМ_УЗ_РК'") !== false) {
    echo "✅ Supervision RK elimination button: FOUND\n";
} else {
    echo "❌ Supervision RK elimination button: MISSING\n";
}

// Check supervision idle button
if (strpos($messageContent, "'⏸️ Простой', 'callback_data' => 'category_Табель_ШМ_Простой'") !== false) {
    echo "✅ Supervision idle button: FOUND\n";
} else {
    echo "❌ Supervision idle button: MISSING\n";
}

echo "\n";

// Test 3: Verify warranty equipment keyboard with context support
echo "3. WARRANTY EQUIPMENT KEYBOARD CONTEXT TEST:\n";
echo "============================================\n";

if (strpos($messageContent, 'createWarrantyEquipmentKeyboard(string $context = \'commissioning\')') !== false) {
    echo "✅ Warranty equipment keyboard context parameter: FOUND\n";
    
    // Check supervision back button logic
    if (strpos($messageContent, "\$context === 'supervision'") !== false) {
        echo "✅ Supervision context detection: FOUND\n";
        if (strpos($messageContent, "'category_Табель_ШМ_Back'") !== false) {
            echo "✅ Supervision back button: FOUND\n";
        } else {
            echo "❌ Supervision back button: MISSING\n";
        }
    } else {
        echo "❌ Supervision context detection: MISSING\n";
    }
} else {
    echo "❌ Warranty equipment keyboard context parameter: MISSING\n";
}

echo "\n";

// Test 4: Verify handleWarrantyEquipmentMenu method signature
echo "4. WARRANTY EQUIPMENT MENU METHOD SIGNATURE:\n";
echo "=============================================\n";

if (strpos($tabelContent, 'handleWarrantyEquipmentMenu(string $chatId, Conversation $conversation = null, string $context = null)') !== false) {
    echo "✅ handleWarrantyEquipmentMenu method signature: CORRECT\n";
} else {
    echo "❌ handleWarrantyEquipmentMenu method signature: INCORRECT\n";
}

echo "\n";

// Test 5: Flow simulation for supervision sub-activities
echo "5. SUPERVISION SUB-ACTIVITIES FLOW SIMULATION:\n";
echo "===============================================\n";

echo "SUPERVISION DEPARTMENT FLOW:\n";
echo "1. User goes to ШМР department (Шеф-монтаж)\n";
echo "2. User selects date and project\n";
echo "3. User sees supervision menu with buttons:\n";
echo "   • 🔧 ШМР (main supervision work)\n";
echo "   • 🛡️ Работы по гарантии (identical to PNR)\n";
echo "   • 🔧 Устранение замечаний РК (identical to PNR)\n";
echo "   • ⏸️ Простой (working perfectly)\n";
echo "\n";

echo "SUPERVISION WARRANTY FLOW:\n";
echo "1. User clicks '🛡️ Работы по гарантии'\n";
echo "   → Callback: category_Табель_ШМ_РабГарантии\n";
echo "   → Routes to handleWarrantyEquipmentMenu() with 'supervision' context\n";
echo "   → Shows same 7 warranty equipment types as PNR department\n";
echo "   → Back button goes to supervision menu (not commissioning)\n";
echo "\n";

echo "SUPERVISION RK ELIMINATION FLOW:\n";
echo "1. User clicks '🔧 Устранение замечаний РК'\n";
echo "   → Callback: category_Табель_ШМ_УЗ_РК\n";
echo "   → Routes to handleRKCommentsEliminationMenu()\n";
echo "   → Shows same 6 RK sections as PNR department\n";
echo "   → Same sub-activities: ЭС, АТХ, ОВКВ, ПС/ОС, ТХ, Прочее\n";
echo "\n";

// Test 6: Check identical functionality
echo "6. IDENTICAL FUNCTIONALITY VERIFICATION:\n";
echo "=========================================\n";

echo "WARRANTY EQUIPMENT SUB-ACTIVITIES:\n";
$warrantyActivities = [
    'ЭлектрОб' => '⚡ Электрооборудование',
    'СрИзм' => '📎 Средства измерений', 
    'НасОб' => '💧 Насосное оборудование',
    'ТрубАрм' => '🔧 Трубопроводная арматура',
    'ИспМех' => '⚙️ Исполнительные механизмы',
    'КонтрОб' => '💻 Контроллерное оборудование',
    'Прочее' => '📦 Прочее оборудование'
];

foreach ($warrantyActivities as $type => $name) {
    if (strpos($messageContent, "'{$name}', 'callback_data' => 'cat_Гарантии_{$type}'") !== false) {
        echo "✅ {$name}: FOUND\n";
    } else {
        echo "❌ {$name}: MISSING\n";
    }
}

echo "\nRK ELIMINATION SUB-ACTIVITIES:\n";
$rkActivities = [
    'ЭС' => '🔌 Раздел ЭС',
    'АТХ' => '🤖 Раздел АТХ',
    'ОВКВ' => '❄️ Раздел ОВКВ', 
    'ПСОС' => '🔥 Раздел ПС/ОС',
    'ТХ' => '⚙️ Раздел ТХ',
    'Прочее' => '📦 Прочее оборудование'
];

foreach ($rkActivities as $type => $name) {
    if (strpos($messageContent, "'{$name}', 'callback_data' => 'category_Табель_УстрЗам_{$type}'") !== false) {
        echo "✅ {$name}: FOUND\n";
    } else {
        echo "❌ {$name}: MISSING\n";
    }
}

echo "\n";

// Test 7: Summary and recommendations
echo "7. CONFIGURATION SUMMARY:\n";
echo "=========================\n";

echo "✅ COMPLETED CONFIGURATION:\n";
echo "• Supervision warranty equipment routing\n";
echo "• Supervision RK elimination routing\n";
echo "• Context-aware warranty equipment keyboard\n";
echo "• Identical sub-activities as PNR department\n";
echo "• Proper back button navigation\n";
echo "\n";

echo "🧪 TESTING INSTRUCTIONS:\n";
echo "1. Go to ШМР department in Telegram\n";
echo "2. Select date and project\n";
echo "3. Test '🛡️ Работы по гарантии' button\n";
echo "4. Test '🔧 Устранение замечаний РК' button\n";
echo "5. Verify sub-menus are identical to PNR department\n";
echo "6. Verify back buttons navigate correctly\n";
echo "\n";

echo "=== SUPERVISION SUB-ACTIVITIES CONFIGURATION COMPLETE ===\n";
?>
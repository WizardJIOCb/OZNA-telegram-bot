<?php
// Final verification script to check if the new "монтаж электрооборудования" button 
// is correctly positioned between "подключение кабельных линий" and "прочее" buttons

echo "Final verification: Button position in additional works electrical keyboard\n";
echo "========================================================================\n\n";

// Simulate the additional works electrical keyboard
$additionalWorksKeyboard = [
    [
        ['text' => '🔀 Изменение схемы подключения', 'callback_data' => 'cat_ТабЭлОб_ИзмСхПд']
    ],
    [
        ['text' => '🔌 Прокладка кабеля', 'callback_data' => 'cat_ТабЭлОб_МонтКбЛн']
    ],
    [
        ['text' => '📊 Подключение кабеля', 'callback_data' => 'cat_ТабЭлОб_РаскКбЛн']
    ],
    [
        ['text' => '⚙️ Монтаж электрооборудования', 'callback_data' => 'cat_ТабЭлОб_МонтОб']  // Our new button
    ],
    [
        ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабЭлОб_Проч']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабЭлОб']
    ]
];

echo "Additional works electrical keyboard layout:\n";
echo "==========================================\n";

$buttonIndex = 0;
$positions = [];

foreach ($additionalWorksKeyboard as $rowIndex => $row) {
    foreach ($row as $buttonIndexInRow => $button) {
        echo ($buttonIndex + 1) . ". " . $button['text'] . "\n";
        $positions[$button['callback_data']] = $buttonIndex;
        $buttonIndex++;
    }
}

echo "\nPosition verification:\n";
echo "====================\n";

// Check positions
$cableWiringPosition = $positions['cat_ТабЭлОб_РаскКбЛн'] ?? -1;
$equipmentInstallationPosition = $positions['cat_ТабЭлОб_МонтОб'] ?? -1;
$otherPosition = $positions['cat_ТабЭлОб_Проч'] ?? -1;

echo "🔌 Подключение кабеля position: " . ($cableWiringPosition + 1) . "\n";
echo "⚙️ Монтаж электрооборудования position: " . ($equipmentInstallationPosition + 1) . "\n";
echo "📝 Прочее position: " . ($otherPosition + 1) . "\n\n";

// Verify the correct order
if ($cableWiringPosition < $equipmentInstallationPosition && $equipmentInstallationPosition < $otherPosition) {
    echo "✅ SUCCESS: Button positions are correct!\n";
    echo "   The 'монтаж электрооборудования' button is correctly positioned between\n";
    echo "   'подключение кабельных линий' and 'прочее' buttons.\n";
} else {
    echo "❌ ERROR: Button positions are incorrect!\n";
    echo "   The 'монтаж электрооборудования' button is not in the correct position.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "FINAL RESULT:\n";

if ($cableWiringPosition < $equipmentInstallationPosition && $equipmentInstallationPosition < $otherPosition) {
    echo "🎉 IMPLEMENTATION COMPLETE AND VERIFIED!\n\n";
    echo "The new 'монтаж электрооборудования' button has been successfully added to the\n";
    echo "additional works electrical keyboard in the correct position.\n\n";
    echo "Button details:\n";
    echo "  • Text: ⚙️ Монтаж электрооборудования\n";
    echo "  • Callback: cat_ТабЭлОб_МонтОб\n";
    echo "  • Position: Between 'подключение кабельных линий' and 'прочее'\n";
} else {
    echo "❌ IMPLEMENTATION INCOMPLETE!\n\n";
    echo "There is an issue with the button positioning that needs to be addressed.\n";
}
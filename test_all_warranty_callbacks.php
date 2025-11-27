<?php

// Test all warranty equipment callback data lengths
$warrantyEquipmentTypes = [
    'ЭлектрОб',
    'СрИзм',
    'НасОб',
    'ТрубАрм',
    'ИспМех',
    'КонтрОб',
    'Прочее'
];

echo "Testing warranty equipment callback data lengths:\n";
echo "===============================================\n";

foreach ($warrantyEquipmentTypes as $equipmentType) {
    $callbackData = "cat_Гарантии_{$equipmentType}_yes";
    $utf8Length = strlen($callbackData);
    
    echo "Equipment: $equipmentType\n";
    echo "  Callback: $callbackData\n";
    echo "  Length: $utf8Length bytes\n";
    echo "  Within limit: " . ($utf8Length <= 64 ? "YES" : "NO") . "\n\n";
}

?>
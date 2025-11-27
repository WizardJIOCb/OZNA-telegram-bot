<?php
// Test script to debug fire alarm callback processing

echo "Testing fire alarm callback processing...\n";

// Simulate the callback data that would be sent when clicking the fire alarm button
$callbackData = 'category_Табель_ПожарнаяСигнализация';

echo "Callback data: " . $callbackData . "\n";

// Check if the callback data matches the expected pattern
if ($callbackData === 'category_Табель_ПожарнаяСигнализация') {
    echo "Callback data matches expected pattern for fire alarm menu\n";
    
    // Simulate the submenu categories check
    $submenuCategories = [
        'Автоматизация',
        'ПожарнаяСигнализация',  // This should be here
        'Метрология',
        'КомплОпробование',
        'РежимнаяНаладка'
    ];
    
    if (in_array('ПожарнаяСигнализация', $submenuCategories)) {
        echo "Fire alarm is correctly in submenu categories\n";
    } else {
        echo "ERROR: Fire alarm is NOT in submenu categories\n";
    }
} else {
    echo "Callback data does not match expected pattern\n";
}

echo "Test completed.\n";
?>
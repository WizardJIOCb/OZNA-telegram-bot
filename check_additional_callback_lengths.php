<?php
// Check the length of additional callback_data values that might be too long

$callbackDataList = [
    'category_Табель_РаботыПоГрафику_ТехнологическоеОборудование',
    'category_Табель_ФормированиеИД_ТехнологическоеОборудование',
    'category_Табель_ДополнительныеРаботы_ТехнологическоеОборудование',
    'category_Табель_ПодтвердитьДостижениеВехи_ТехнологическоеОборудование',
    'category_Табель_ДемонтажРевизияАрматуры_ТехнологическоеОборудование',
    'category_Табель_ДемонтажРевизияТехнОборудования_ТехнологическоеОборудование',
    'category_Табель_Прочее_ТехнологическоеОборудование',
    'category_Табель_ТехнологическоеОборудование'
];

echo "Checking additional callback_data lengths:\n";
echo "========================================\n";

foreach ($callbackDataList as $callbackData) {
    $length = strlen($callbackData);
    $status = $length <= 64 ? "OK" : "TOO LONG";
    echo sprintf("%-70s | Length: %2d | %s\n", $callbackData, $length, $status);
}

echo "\nTelegram limit: 64 bytes\n";
echo "Any callback_data over this limit will cause BUTTON_DATA_INVALID error\n";
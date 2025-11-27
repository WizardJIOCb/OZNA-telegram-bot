<?php
// Check the length of all callback_data values with our final shortened names

$callbackDataList = [
    // Commissioning works keyboard
    'category_Табель_Электрооборудование',
    'category_Табель_Автоматизация',
    'category_Табель_ПожарнойСигнализации',
    'category_Табель_ОтоплениеИВентиляции',
    'category_Табель_ТехОб',  // Shortened from ТехнологическоеОборудование
    'category_Табель_Метрология',
    'category_Табель_КомплОпробование',  // Shortened from КомплексноеОпробование
    'category_Табель_РежимнаяНаладка',
    'category_Табель_Пусконаладка_Back',
    
    // Technological equipment keyboard
    'category_Табель_РаботыПоГрафику_ТехОб',  // Shortened from ТехнОборудование
    'category_Табель_ФормированиеИД_ТехОб',   // Shortened from ТехнОборудование
    'category_Табель_ДопРаботы_ТехОб',  // Shortened from ДополнительныеРаботы_ТехОб
    'category_Табель_ПодтвДостВехи_ТехОб',  // Shortened from ПодтвердитьДостижениеВехи_ТехОб
    'category_Табель_ПусконаладочныеРаботы',
    
    // Additional works - technological equipment
    'category_Табель_ДемРевАрм_ТехОб',  // Shortened from ДемонтажРевизияАрматуры_ТехОб
    'category_Табель_ДемРевТехнОб_ТехОб',  // Shortened from ДемонтажРевизияТехнОб_ТехОб
    'category_Табель_Прочее_ТехОб',  // Shortened from ТехнОборудование
    'category_Табель_ТехОб',  // Shortened from ТехнологическоеОборудование
    
    // Automation equipment keyboard
    'category_Табель_РабПоГр_Автоматизация',
    'category_Табель_ФормИД_Автоматизация',
    'category_Табель_ДопРаб_Автоматизация',
    'category_Табель_ПодтВех_Автоматизация',
    'category_Табель_ПусконаладочныеРаботы',
    
    // Additional works - automation equipment
    'category_Табель_ИзмСх_Автоматизация',
    'category_Табель_МонтКб_Автоматизация',
    'category_Табель_РаскКб_Автоматизация',
    'category_Табель_МонтАСУ_Автоматизация',
    'category_Табель_УдалПО_Автоматизация',
    'category_Табель_Прочее_Автоматизация',
    'category_Табель_Автоматизация'
];

echo "Checking final callback_data lengths:\n";
echo "==================================\n";

foreach ($callbackDataList as $callbackData) {
    $length = strlen($callbackData);
    $status = $length <= 64 ? "OK" : "TOO LONG";
    echo sprintf("%-50s | Length: %2d | %s\n", $callbackData, $length, $status);
}

echo "\nTelegram limit: 64 bytes\n";
echo "Any callback_data over this limit will cause BUTTON_DATA_INVALID error\n";
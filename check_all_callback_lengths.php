<?php
// Check the length of all callback_data values that we've updated

$callbackDataList = [
    // Commissioning works keyboard
    'category_Табель_Электрооборудование',
    'category_Табель_Автоматизация',
    'category_Табель_ПожарнойСигнализации',
    'category_Табель_ОтоплениеИВентиляции',
    'category_Табель_ТехнОборудование',
    'category_Табель_Метрология',
    'category_Табель_КомплОпробование',
    'category_Табель_РежимнаяНаладка',
    'category_Табель_Пусконаладка_Back',
    
    // Technological equipment keyboard
    'category_Табель_РаботыПоГрафику_ТехнОборудование',
    'category_Табель_ФормированиеИД_ТехнОборудование',
    'category_Табель_ДополнительныеРаботы_ТехнОборудование',
    'category_Табель_ПодтвердитьДостижениеВехи_ТехнОборудование',
    'category_Табель_ПусконаладочныеРаботы',
    
    // Additional works - technological equipment
    'category_Табель_ДемонтажРевизияАрматуры_ТехнОборудование',
    'category_Табель_ДемонтажРевизияТехнОборудования_ТехнОборудование',
    'category_Табель_Прочее_ТехнОборудование',
    'category_Табель_ТехнОборудование',
    
    // Automation equipment keyboard
    'category_Табель_РаботыПоГрафику_Автоматизация',
    'category_Табель_ФормированиеИД_Автоматизация',
    'category_Табель_ДопРаботы_Автоматизация',
    'category_Табель_ПодтвДостВехи_Автоматизация',
    'category_Табель_ПусконаладочныеРаботы',
    
    // Additional works - automation equipment
    'category_Табель_ИзмСхемПодкл_Автоматизация',
    'category_Табель_МонтКабЛиний_Автоматизация',
    'category_Табель_РасклКабЛиний_Автоматизация',
    'category_Табель_МонтОбАСУТП_Автоматизация',
    'category_Табель_УдалПНРПО_Автоматизация',
    'category_Табель_Прочее_Автоматизация',
    'category_Табель_Автоматизация'
];

echo "Checking all updated callback_data lengths:\n";
echo "========================================\n";
<?php
$callbacks = [
    'category_Табель_РаботыПоГрафику_ЭлектрОб',
    'category_Табель_ФормированиеИД_ЭлектрОб',
    'category_Табель_ДопРаботы_ЭлектрОб',
    'category_Табель_ПодтвДостВехи_ЭлектрОб',
    'category_Табель_ИзменСхемПодкл_ЭлектрОб',
    'category_Табель_МонтажКабЛиний_ЭлектрОб',
    'category_Табель_РасклКабЛиний_ЭлектрОб',
    'category_Табель_Прочее_ЭлектрОб',
    'category_Табель_ЭлектрОб'
];

foreach ($callbacks as $callback) {
    echo $callback . ' | Length: ' . strlen($callback) . "\n";
}
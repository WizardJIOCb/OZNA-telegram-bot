<?php
$callbacks = [
    'category_Табель_РаботыПоГрафику_Электрооборудование',
    'category_Табель_ФормированиеИД_Электрооборудование',
    'category_Табель_ДополнительныеРаботы_Электрооборудование',
    'category_Табель_ПодтвердитьДостижениеВехи_Электрооборудование'
];

foreach ($callbacks as $callback) {
    echo $callback . ' | Length: ' . strlen($callback) . "\n";
}
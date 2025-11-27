<?php
$callbacks = [
    'category_Табель_РабПоГраф_ЭлектрОб',
    'category_Табель_ФормИД_ЭлектрОб',
    'category_Табель_ДопРаб_ЭлектрОб',
    'category_Табель_ПодтВехи_ЭлектрОб',
    'category_Табель_ИзмСхем_ЭлектрОб',
    'category_Табель_МонтКаб_ЭлектрОб',
    'category_Табель_РасклКаб_ЭлектрОб',
    'category_Табель_Прочее_ЭлектрОб',
    'category_Табель_ЭлектрОб'
];

foreach ($callbacks as $callback) {
    echo $callback . ' | Length: ' . strlen($callback) . "\n";
}
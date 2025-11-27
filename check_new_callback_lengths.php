<?php
$callbacks = [
    'category_Табель_Подготовка',
    'category_Табель_Дорога',
    'category_Табель_Инструктаж',
    'category_Табель_АвансовыйОтчет'
];

foreach ($callbacks as $callback) {
    echo $callback . ' | Length: ' . strlen($callback) . "\n";
}
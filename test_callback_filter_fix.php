<?php

echo "=== ТЕСТИРОВАНИЕ ИСПРАВЛЕНИЯ ФИЛЬТРА КОЛБЕКОВ ===\n";
echo "Время: " . date('Y-m-d H:i:s') . "\n\n";

// Тестируем логику фильтра из CallbackqueryCommand.php
function testCallbackFilter($callback_data) {
    echo "Тестирование колбека: '{$callback_data}'\n";
    
    $shouldPass = !(
        strpos($callback_data, 'category_') !== 0 && 
        strpos($callback_data, 'adm_req_') !== 0 && 
        strpos($callback_data, 'acc_lvl_') !== 0 && 
        strpos($callback_data, 'cat_Таб') !== 0 &&
        strpos($callback_data, 'cat_ШМР_') !== 0 &&
        strpos($callback_data, 'cat_РЁРњР') !== 0 &&
        strpos($callback_data, 'РЁРњР') === false &&  // CRITICAL FIX: Allow any callback containing corrupted pattern
        strpos($callback_data, 'cat_supervision_') !== 0 &&
        strpos($callback_data, 'proj_ctrl_') !== 0 &&
        strpos($callback_data, 'proj_list_') !== 0 &&
        strpos($callback_data, 'cat_Гарантии') !== 0
    );
    
    echo "   Результат: " . ($shouldPass ? "✅ ПРОХОДИТ" : "❌ БЛОКИРУЕТСЯ") . "\n";
    
    // Дополнительная диагностика
    if (strpos($callback_data, 'РЁРњР') !== false) {
        echo "   ✅ Содержит корруптированный паттерн 'РЁРњР'\n";
    }
    
    echo "\n";
    return $shouldPass;
}

echo "1. ТЕСТИРОВАНИЕ КОРРУПТИРОВАННОГО КОЛБЕКА:\n";
$corruptedCallback = 'cat_РЁРњР _РПѕРґРіР Р°Р±';
testCallbackFilter($corruptedCallback);

echo "2. ТЕСТИРОВАНИЕ ПРАВИЛЬНОГО КОЛБЕКА:\n";
$correctCallback = 'cat_ШМР_АналФунд_yes';
testCallbackFilter($correctCallback);

echo "3. ТЕСТИРОВАНИЕ ДРУГИХ КОРРУПТИРОВАННЫХ ВАРИАНТОВ:\n";
testCallbackFilter('РЁРњР _РПѕРґРіР Р°Р±');
testCallbackFilter('cat_РЁРњР');

echo "РЕЗУЛЬТАТ:\n";
echo "==========\n";

$corruptedShouldPass = testCallbackFilter($corruptedCallback);
if ($corruptedShouldPass) {
    echo "✅ ИСПРАВЛЕНИЕ РАБОТАЕТ! Корруптированный колбек теперь будет проходить через фильтр\n";
    echo "✅ Это позволит TabelHandler обработать его и применить исправление кодировки\n";
} else {
    echo "❌ ИСПРАВЛЕНИЕ НЕ РАБОТАЕТ! Нужна дополнительная настройка фильтра\n";
}

echo "\nСледующий шаг: Протестируйте кнопку 'Проверка монтажной площадки' в боте!\n";
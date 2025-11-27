<?php
/**
 * ПРОВЕРКА ВСЕХ ДЛИННЫХ КОЛЛБЕКОВ В СИСТЕМЕ
 * Ищем все места где могут быть коллбеки длиннее 64 байт
 */

echo "🔍 ПРОВЕРКА ВСЕХ ДЛИННЫХ КОЛЛБЕКОВ В СИСТЕМЕ\n";
echo "============================================\n\n";

// Функция для проверки длины коллбека
function checkCallbackLength($callback, $context = '') {
    $length = strlen($callback);
    $status = $length <= 64 ? "✅ OK" : "❌ СЛИШКОМ ДЛИННЫЙ";
    echo "  → '{$callback}' = {$length} байт {$status}";
    if ($context) {
        echo " ({$context})";
    }
    echo "\n";
    return $length <= 64;
}

echo "📋 ПРОВЕРЯЕМ MESSAGESERVICE.PHP\n";
echo "==============================\n";

$messageServiceContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\MessageService.php');

// Найдем все коллбеки в файле
preg_match_all("/'callback_data' => '([^']+)'/", $messageServiceContent, $matches);

$longCallbacks = [];
$allGood = true;

echo "Найдено коллбеков: " . count($matches[1]) . "\n";

foreach ($matches[1] as $callback) {
    $length = strlen($callback);
    if ($length > 64) {
        $longCallbacks[] = $callback;
        $allGood = false;
        echo "❌ ПРОБЛЕМА: '{$callback}' = {$length} байт\n";
    }
}

if ($allGood) {
    echo "✅ Все коллбеки в MessageService.php в порядке!\n";
} else {
    echo "🚨 НАЙДЕНЫ ДЛИННЫЕ КОЛЛБЕКИ:\n";
    foreach ($longCallbacks as $callback) {
        echo "  → {$callback} (" . strlen($callback) . " байт)\n";
    }
}

echo "\n📋 ПРОВЕРЯЕМ TABELHANDLER.PHP\n";
echo "============================\n";

$tabelContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Найдем все коллбеки в файле
preg_match_all("/createConfirmationKeyboard\(\"([^\"]+)\"\)/", $tabelContent, $confirmationMatches);

echo "Найдено confirmation коллбеков: " . count($confirmationMatches[1]) . "\n";

$longConfirmations = [];
foreach ($confirmationMatches[1] as $callback) {
    $length = strlen($callback);
    if ($length > 64) {
        $longConfirmations[] = $callback;
        echo "❌ ДЛИННЫЙ CONFIRMATION: '{$callback}' = {$length} байт\n";
    }
}

if (empty($longConfirmations)) {
    echo "✅ Все confirmation коллбеки в порядке!\n";
}

echo "\n📋 СПЕЦИАЛЬНАЯ ПРОВЕРКА ШЕФЕРСКИХ РАБОТ\n";
echo "======================================\n";

// Проверим конкретные коллбеки шеф-монтажных работ
$supervisionCallbacks = [
    // Основное меню
    'category_Табель_ШМ_ШМР' => 'Основная кнопка шеф-монтажных работ',
    
    // Субактивности (короткие)
    'cat_ШМР_РабПоГр' => 'Работы по графику',
    'cat_ШМР_ПодгПоезд' => 'Подготовка к поездке', 
    'cat_ШМР_Дорога' => 'Дорога',
    'cat_ШМР_Инстр' => 'Инструктаж',
    'cat_ШМР_СдачаОтч' => 'Сдача авансового отчета',
    
    // Подтверждения (короткие)
    'cat_ШМР_РабПоГр_yes' => 'Подтверждение работ по графику',
    'cat_ШМР_ПодгПоезд_yes' => 'Подтверждение подготовки к поездке',
    'cat_ШМР_Дорога_yes' => 'Подтверждение дороги',
    'cat_ШМР_Инстр_yes' => 'Подтверждение инструктажа',
    'cat_ШМР_СдачаОтч_yes' => 'Подтверждение сдачи отчета'
];

echo "Проверяем все коллбеки шеф-монтажных работ:\n";
$supervisionProblems = [];

foreach ($supervisionCallbacks as $callback => $description) {
    $length = strlen($callback);
    $status = $length <= 64 ? "✅" : "❌";
    echo "  {$status} {$description}: '{$callback}' = {$length} байт\n";
    
    if ($length > 64) {
        $supervisionProblems[] = $callback;
    }
}

echo "\n📋 ПОИСК СТАРЫХ ДЛИННЫХ КОЛЛБЕКОВ В КОДЕ\n";
echo "=======================================\n";

// Ищем старые длинные коллбеки в TabelHandler
$oldLongPatterns = [
    'category_Табель_ШМ_РабПоГрафику_yes',
    'category_Табель_ШМ_ПодготовкаКПоездке_yes', 
    'category_Табель_ШМ_Дорога_yes',
    'category_Табель_ШМ_Инструктаж_yes',
    'category_Табель_ШМ_СдачаАвансовогоОтчета_yes'
];

$foundOldCallbacks = [];
foreach ($oldLongPatterns as $pattern) {
    if (strpos($tabelContent, $pattern) !== false) {
        $foundOldCallbacks[] = $pattern;
        echo "❌ НАЙДЕН СТАРЫЙ ДЛИННЫЙ КОЛЛБЕК: {$pattern}\n";
    }
}

if (empty($foundOldCallbacks)) {
    echo "✅ Старые длинные коллбеки не найдены!\n";
}

echo "\n🎯 ИТОГОВЫЙ ОТЧЕТ\n";
echo "================\n";

if (empty($longCallbacks) && empty($longConfirmations) && empty($supervisionProblems) && empty($foundOldCallbacks)) {
    echo "🎉 ВСЕ КОЛЛБЕКИ В ПОРЯДКЕ!\n";
    echo "Все коллбеки короче 64 байт и используют правильный формат.\n";
} else {
    echo "🚨 НАЙДЕНЫ ПРОБЛЕМЫ:\n";
    
    if (!empty($longCallbacks)) {
        echo "\nДлинные коллбеки в MessageService:\n";
        foreach ($longCallbacks as $cb) {
            echo "  → {$cb}\n";
        }
    }
    
    if (!empty($longConfirmations)) {
        echo "\nДлинные confirmation коллбеки:\n";
        foreach ($longConfirmations as $cb) {
            echo "  → {$cb}\n";
        }
    }
    
    if (!empty($supervisionProblems)) {
        echo "\nПроблемы с шеф-монтажными работами:\n";
        foreach ($supervisionProblems as $cb) {
            echo "  → {$cb}\n";
        }
    }
    
    if (!empty($foundOldCallbacks)) {
        echo "\nНайдены старые длинные коллбеки:\n";
        foreach ($foundOldCallbacks as $cb) {
            echo "  → {$cb}\n";
        }
    }
}

echo "\n💡 РЕКОМЕНДАЦИИ\n";
echo "==============\n";
echo "1. Все коллбеки должны быть короче 64 байт\n";
echo "2. Используйте короткий формат: cat_ШМР_* вместо category_Табель_ШМ_*\n";
echo "3. Проверьте все места где создаются confirmation коллбеки\n";
echo "4. Убедитесь что нет старых длинных коллбеков в коде\n";

?>
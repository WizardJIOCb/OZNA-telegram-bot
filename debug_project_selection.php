<?php
// Диагностика проблемы после выбора проекта в ШМР

echo "=== ДИАГНОСТИКА ВЫБОРА ПРОЕКТА ДЛЯ ШМР ===\n\n";

// 1. Проверяем последние логи после выбора проекта
echo "1. ПРОВЕРКА ПОСЛЕДНИХ ЛОГОВ\n";
echo "============================\n";

$logFile = 'c:\ozna_bot\telegram_bot_error.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -15);
    
    echo "Последние 15 записей в логе (после выбора проекта):\n";
    foreach ($lastLines as $i => $line) {
        echo sprintf("%2d. %s", 15-count($lastLines)+$i+1, trim($line)) . "\n";
    }
} else {
    echo "❌ Лог файл не найден\n";
}

echo "\n";

// 2. Проверяем логику обработки проекта в TabelHandler
echo "2. ПРОВЕРКА ЛОГИКИ ОБРАБОТКИ ПРОЕКТА\n";
echo "=====================================\n";

$tabelHandlerFile = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    $content = file_get_contents($tabelHandlerFile);
    
    // Ищем метод handleProjectSelection
    if (strpos($content, 'function handleProjectSelection') !== false) {
        echo "✅ Метод handleProjectSelection найден\n";
        
        // Ищем обработку для supervision (is_supervision)
        if (strpos($content, 'is_supervision') !== false) {
            echo "✅ Логика для supervision найдена\n";
            
            // Ищем что происходит после выбора проекта для supervision
            if (strpos($content, 'createSupervisionKeyboard') !== false) {
                echo "✅ Вызов createSupervisionKeyboard найден\n";
            } else {
                echo "❌ Вызов createSupervisionKeyboard НЕ НАЙДЕН!\n";
            }
        } else {
            echo "❌ Логика для supervision НЕ НАЙДЕНА!\n";
        }
    } else {
        echo "❌ Метод handleProjectSelection НЕ НАЙДЕН!\n";
    }
} else {
    echo "❌ Файл TabelHandler.php не найден!\n";
}

echo "\n";

// 3. Проверяем MessageService для createSupervisionKeyboard
echo "3. ПРОВЕРКА MESSAGESERVICE ДЛЯ SUPERVISION\n";
echo "==========================================\n";

$messageServiceFile = 'c:\ozna_bot\CustomCommands\Services\MessageService.php';
if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    
    // Ищем метод createSupervisionKeyboard
    if (strpos($content, 'function createSupervisionKeyboard') !== false) {
        echo "✅ Метод createSupervisionKeyboard найден\n";
        
        // Проверяем содержимое клавиатуры ШМР
        if (strpos($content, 'Шеф-монтажные работы') !== false) {
            echo "✅ Кнопка 'Шеф-монтажные работы' найдена\n";
        }
        if (strpos($content, 'category_Табель_ШМР') !== false) {
            echo "✅ Callback 'category_Табель_ШМР' найден\n";
        }
    } else {
        echo "❌ Метод createSupervisionKeyboard НЕ НАЙДЕН!\n";
        echo "   Это может быть причиной проблемы!\n";
    }
} else {
    echo "❌ Файл MessageService.php не найден!\n";
}

echo "\n";

// 4. Диагноз и решение
echo "4. ДИАГНОЗ И ПЛАН ИСПРАВЛЕНИЯ\n";
echo "==============================\n";

echo "АНАЛИЗ ПРОБЛЕМЫ:\n";
echo "После выбора проекта для ШМР система должна показать меню активностей ШМР:\n";
echo "- 🔧 Шеф-монтажные работы\n";
echo "- 🛠 Устранение замечаний РК\n";
echo "- 🛡 Работы по гарантии\n";
echo "- ⏸ Простой\n\n";

echo "ВОЗМОЖНЫЕ ПРИЧИНЫ:\n";
echo "1. Отсутствует метод createSupervisionKeyboard в MessageService\n";
echo "2. Неправильная логика в handleProjectSelection для supervision\n";
echo "3. Ошибка в runtime при создании меню\n\n";

echo "СЛЕДУЮЩИЕ ДЕЙСТВИЯ:\n";
echo "1. Проверим/создадим метод createSupervisionKeyboard\n";
echo "2. Проверим логику handleProjectSelection\n";
echo "3. Протестируем исправления\n";

?>
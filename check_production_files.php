<?php
// ENHANCED PRODUCTION DEBUGGING FOR MASTER DUTIES BUTTON

echo "=== ENHANCED DEBUGGING: КНОПКА 'ИСПОЛНЕНИЕ ОБЯЗАННОСТЕЙ МАСТЕРА' ===\n\n";

echo "🔍 ПРОВЕРЯЕМ РЕАЛЬНЫЕ ФАЙЛЫ ПРОДАКШЕНА\n";
echo "=====================================\n\n";

// Step 1: Check if the actual files contain the required code
echo "1️⃣ ПРОВЕРКА РЕАЛЬНЫХ ФАЙЛОВ:\n";
echo "-----------------------------\n";

$tabelHandlerFile = 'CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    $content = file_get_contents($tabelHandlerFile);
    
    // Check 1: Mapping in extractCategoryFromCallback
    if (strpos($content, "'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера'") !== false) {
        echo "✅ Callback mapping найден в файле\n";
    } else {
        echo "❌ Callback mapping ОТСУТСТВУЕТ в файле\n";
        echo "🚨 КРИТИЧЕСКАЯ ПРОБЛЕМА: Mapping не добавлен в файл!\n\n";
    }
    
    // Check 2: Handler condition in handleOtherWorkTypes
    if (strpos($content, "if (\$category === 'Табель_ИсполнениеОбязМастера' || \$category === 'cat_ТабИсполнОбяз')") !== false) {
        echo "✅ Handler condition найден в файле\n";
    } else {
        echo "❌ Handler condition ОТСУТСТВУЕТ в файле\n";
        echo "🚨 КРИТИЧЕСКАЯ ПРОБЛЕМА: Handler не добавлен в файл!\n\n";
    }
    
    // Check 3: Activity type in activityTypes array
    if (strpos($content, "'ИсполнениеОбязМастера'") !== false) {
        echo "✅ Activity type найден в файле\n";
    } else {
        echo "❌ Activity type ОТСУТСТВУЕТ в файле\n";
        echo "🚨 КРИТИЧЕСКАЯ ПРОБЛЕМА: Activity type отсутствует в массиве!\n\n";
    }
    
    // Check 4: Project activity callback mapping in handleActivityExecution
    if (strpos($content, "'ТабИсполнОбяз' => 'ИсполнениеОбязМастера'") !== false) {
        echo "✅ Project activity mapping найден в файле\n";
    } else {
        echo "❌ Project activity mapping ОТСУТСТВУЕТ в файле\n";
        echo "🚨 КРИТИЧЕСКАЯ ПРОБЛЕМА: Project activity mapping отсутствует!\n\n";
    }
    
    // Check 5: Log statements for debugging
    if (strpos($content, "🎯 MASTER DUTIES: Handler triggered!") !== false) {
        echo "✅ Debug logs найдены в файле\n";
    } else {
        echo "⚠️ Debug logs отсутствуют (это нормально если не добавлены)\n";
    }
    
} else {
    echo "❌ ФАЙЛ TabelHandler.php НЕ НАЙДЕН!\n";
    echo "🚨 КРИТИЧЕСКАЯ ПРОБЛЕМА: Путь к файлу неверный!\n\n";
    exit;
}

echo "\n2️⃣ ПРОВЕРКА MessageService:\n";
echo "---------------------------\n";

$messageServiceFile = 'CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    
    // Check for the button definition
    if (strpos($content, "'callback_data' => 'cat_ТабИсполнОбяз'") !== false) {
        echo "✅ Button definition найден в MessageService\n";
    } else {
        echo "❌ Button definition ОТСУТСТВУЕТ в MessageService\n";
        echo "🚨 ПРОБЛЕМА: Кнопка не создается!\n\n";
    }
} else {
    echo "❌ ФАЙЛ MessageService.php НЕ НАЙДЕН!\n";
}

echo "\n3️⃣ ПРОВЕРКА TabelEntry:\n";
echo "-----------------------\n";

$tabelEntryFile = 'CustomCommands/Services/TabelEntry.php';
if (file_exists($tabelEntryFile)) {
    $content = file_get_contents($tabelEntryFile);
    
    // Check for the activity mapping
    if (strpos($content, "'ИсполнениеОбязМастера' => '👷 Исполнение обязанностей мастера'") !== false) {
        echo "✅ Activity display text найден в TabelEntry\n";
    } else {
        echo "❌ Activity display text ОТСУТСТВУЕТ в TabelEntry\n";
        echo "🚨 ПРОБЛЕМА: Текст активности отсутствует!\n\n";
    }
} else {
    echo "❌ ФАЙЛ TabelEntry.php НЕ НАЙДЕН!\n";
}

echo "\n4️⃣ ОБЩИЕ РЕКОМЕНДАЦИИ:\n";
echo "======================\n";

echo "🔧 ЕСЛИ ВСЕ ПРОВЕРКИ ПРОШЛИ УСПЕШНО:\n";
echo "1. Перезапустите веб-сервер: sudo systemctl restart apache2 или nginx\n";
echo "2. Перезапустите PHP-FPM: sudo systemctl restart php8.1-fpm\n";
echo "3. Очистите opcache: в админ-панели или через код\n";
echo "4. Начните новый разговор с ботом (очистите состояние)\n\n";

echo "🐛 ЕСЛИ ЕСТЬ ОШИБКИ:\n";
echo "1. Скопируйте недостающий код из других файлов диагностики\n";
echo "2. Добавьте временные логи для отслеживания\n";
echo "3. Проверьте права доступа к файлам\n\n";

echo "📝 ТЕСТОВАЯ ПОСЛЕДОВАТЕЛЬНОСТЬ:\n";
echo "1. Откройте бота\n";
echo "2. Отправьте /start\n";
echo "3. Выберите 'Отдел ШМР'\n";
echo "4. Выберите любую дату\n";
echo "5. Выберите любой проект\n";
echo "6. Нажмите 'Проектная деятельность'\n";
echo "7. Нажмите 'исполнение обязанностей мастера'\n";
echo "8. Должно появиться сообщение подтверждения с кнопками 'Да'/'Нет'\n";
echo "9. Нажмите 'Да'\n";
echo "10. Должен создаться отчет в табеле\n\n";

echo "✅ ДИАГНОСТИКА ЗАВЕРШЕНА!\n";
?>
<?php

echo "=== БЫСТРАЯ ПРОВЕРКА: Файлы обновлены на сервере? ===\n\n";

$files = [
    'ProjectHandler.php' => __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php',
    'GenericmessageCommand.php' => __DIR__ . '/CustomCommands/GenericmessageCommand.php',
    'DatabaseService.php' => __DIR__ . '/CustomCommands/Services/DatabaseService.php'
];

$criticalStrings = [
    'ProjectHandler.php' => [
        'proj_report_simple' => 'Кнопка "Простой отчёт"',
        'showSimpleReportStartDateMenu' => 'Метод выбора даты начала',
        'DEBUG_SIMPLE_REPORT' => 'Отладочное логирование',
        "startsWithProjectReport = str_starts_with(\$callbackData, 'proj_report_')" => 'Проверка proj_report_ в canHandle'
    ],
    'GenericmessageCommand.php' => [
        'isAwaitingSimpleReportDate' => 'Проверка ожидания даты',
        'handleSimpleReportDateInput' => 'Обработка ввода даты'
    ],
    'DatabaseService.php' => [
        'getSimpleReportEntries' => 'Метод получения данных для простого отчёта'
    ]
];

$allOk = true;

foreach ($files as $fileName => $filePath) {
    echo "📄 Проверка файла: {$fileName}\n";
    
    if (!file_exists($filePath)) {
        echo "   ❌ ФАЙЛ НЕ НАЙДЕН: {$filePath}\n\n";
        $allOk = false;
        continue;
    }
    
    $content = file_get_contents($filePath);
    $fileOk = true;
    
    echo "   Размер файла: " . number_format(strlen($content)) . " байт\n";
    echo "   Последнее изменение: " . date('Y-m-d H:i:s', filemtime($filePath)) . "\n";
    
    if (isset($criticalStrings[$fileName])) {
        echo "   Проверка критических строк:\n";
        foreach ($criticalStrings[$fileName] as $search => $description) {
            if (strpos($content, $search) !== false) {
                echo "      ✅ {$description}\n";
            } else {
                echo "      ❌ ОТСУТСТВУЕТ: {$description}\n";
                $fileOk = false;
                $allOk = false;
            }
        }
    }
    
    if ($fileOk) {
        echo "   ✅ Файл в порядке\n";
    } else {
        echo "   ⚠️ Файл требует обновления!\n";
    }
    
    echo "\n";
}

echo "=== ИТОГ ===\n\n";

if ($allOk) {
    echo "✅ Все файлы обновлены правильно!\n\n";
    echo "Если кнопка не работает, проблема может быть:\n";
    echo "1. Файлы не загружены на сервер (проверьте на сервере)\n";
    echo "2. PHP-FPM нужно перезапустить (sudo systemctl restart php-fpm)\n";
    echo "3. Есть кеш PHP OPcache (перезапуск PHP-FPM тоже поможет)\n\n";
} else {
    echo "❌ Некоторые файлы не обновлены!\n\n";
    echo "Нужно:\n";
    echo "1. Загрузить файлы на сервер\n";
    echo "2. Перезапустить PHP-FPM\n\n";
}

echo "📋 КОМАНДЫ ДЛЯ СЕРВЕРА:\n\n";
echo "# Перезапуск PHP-FPM (выберите подходящую версию):\n";
echo "sudo systemctl restart php-fpm\n";
echo "# или\n";
echo "sudo systemctl restart php8.3-fpm\n";
echo "# или\n";
echo "sudo systemctl restart php8.2-fpm\n\n";

echo "# Проверка логов в реальном времени:\n";
echo "tail -f /var/log/nginx/error.log | grep -E 'DEBUG_SIMPLE_REPORT|ProjectHandler'\n\n";

echo "# Очистка OPcache (если используется):\n";
echo "# Создайте файл clear_cache.php с содержимым:\n";
echo "<?php opcache_reset(); echo 'Cache cleared'; ?>\n";
echo "# И откройте его в браузере\n\n";

?>

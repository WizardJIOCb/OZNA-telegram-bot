<?php
// Мониторинг логов в реальном времени для диагностики ШМР

echo "=== МОНИТОРИНГ ЛОГОВ ДЛЯ ШМР ===\n\n";

$logFile = 'c:\ozna_bot\telegram_bot_error.log';

if (!file_exists($logFile)) {
    echo "❌ Лог файл не найден: {$logFile}\n";
    exit;
}

// Получаем текущий размер файла
$currentSize = filesize($logFile);
echo "Текущий размер лог файла: {$currentSize} байт\n";
echo "Мониторим новые записи...\n";
echo "Теперь нажмите кнопку 'Отдел ШМР' в боте!\n";
echo "========================================\n\n";

// Ждем новые записи
$checkCount = 0;
$maxChecks = 30; // 30 секунд ожидания

while ($checkCount < $maxChecks) {
    sleep(1);
    $checkCount++;
    
    $newSize = filesize($logFile);
    
    if ($newSize > $currentSize) {
        echo "🎯 НОВЫЕ ЗАПИСИ ОБНАРУЖЕНЫ!\n";
        echo "===========================\n";
        
        // Читаем только новые строки
        $handle = fopen($logFile, 'r');
        fseek($handle, $currentSize);
        $newContent = fread($handle, $newSize - $currentSize);
        fclose($handle);
        
        $newLines = explode("\n", trim($newContent));
        
        foreach ($newLines as $i => $line) {
            if (!empty(trim($line))) {
                echo sprintf("📝 %s\n", trim($line));
                
                // Проверяем ключевые события
                if (strpos($line, 'SUPERVISION') !== false) {
                    echo "   🔧 ШМР событие обнаружено!\n";
                }
                if (strpos($line, 'createSupervisionKeyboard') !== false) {
                    echo "   ✅ Создание клавиатуры ШМР!\n";
                }
                if (strpos($line, 'is_supervision') !== false) {
                    echo "   🏗️ Проверка флага supervision!\n";
                }
                if (strpos($line, 'handleProjectSelection') !== false) {
                    echo "   📂 Обработка выбора проекта!\n";
                }
            }
        }
        
        $currentSize = $newSize;
        echo "\nПродолжаем мониторинг...\n";
        echo "========================\n\n";
    } else {
        echo "⏳ Ожидание {$checkCount}/{$maxChecks} сек...\r";
    }
}

echo "\n\n";
echo "Мониторинг завершен.\n";
echo "Если новых записей не было, возможно:\n";
echo "1. Веб-сервер не запущен\n";
echo "2. Бот не обрабатывает запросы\n";
echo "3. Логирование отключено\n";

?>
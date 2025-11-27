<?php

echo "=== СОЗДАНИЕ СКРИПТА ДЛЯ ПРОВЕРКИ ЛОГОВ ===\n";
echo "Время: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем скрипт для проверки логов
$logChecker = '<?php

echo "=== ПРОВЕРКА ЛОГОВ TELEGRAM БОТА ===\\n";
echo "Время: " . date("Y-m-d H:i:s") . "\\n\\n";

// Проверяем лог-файлы
$logFiles = [
    "/Applications/XAMPP/xamppfiles/logs/php_error_log",
    "/var/log/apache2/error.log",
    "/var/log/nginx/error.log",
    "error.log",
    "php_error.log",
    "../logs/error.log",
    "../error.log"
];

$found = false;

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "✅ Найден лог-файл: $logFile\\n";
        $found = true;
        
        // Читаем последние строки
        $lines = file($logFile);
        $recentLines = array_slice($lines, -50); // Последние 50 строк
        
        echo "\\nПоследние записи с отладкой проверки монтажной площадки:\\n";
        echo "=================================================\\n";
        
        foreach ($recentLines as $line) {
            if (strpos($line, "SITE INSPECTION") !== false || 
                strpos($line, "АналФунд") !== false ||
                strpos($line, "ПроверкаМонтажнойПлощадки") !== false ||
                strpos($line, "createTabelMessage") !== false) {
                echo $line;
            }
        }
        
        echo "\\n=================================================\\n";
        break;
    }
}

if (!$found) {
    echo "❌ Лог-файлы не найдены в стандартных местах\\n";
    echo "Попробуйте найти error.log в корне проекта или спросите администратора\\n";
    
    // Проверяем текущую директорию
    $files = scandir(".");
    foreach ($files as $file) {
        if (strpos($file, "log") !== false) {
            echo "Возможный лог-файл: $file\\n";
        }
    }
}

echo "\\nИНСТРУКЦИЯ:\\n";
echo "============\\n";
echo "1. Нажмите кнопку \'Проверка монтажной площадки\' в боте\\n";
echo "2. Запустите этот скрипт снова: php check_logs.php\\n";
echo "3. Найдите строки с \'SITE INSPECTION DEBUG\' чтобы увидеть какие параметры попадают в createTabelMessage\\n";
echo "4. Если не видите логи, проверьте другие лог-файлы или обратитесь к администратору\\n";
';

file_put_contents('check_logs.php', $logChecker);
echo "✅ Создан файл check_logs.php\n\n";

echo "ПЛАН ДИАГНОСТИКИ:\n";
echo "==================\n";
echo "1. Запустите: php check_logs.php\n";
echo "2. Нажмите кнопку 'Проверка монтажной площадки' в Telegram боте\n";
echo "3. Снова запустите: php check_logs.php\n";
echo "4. Найдите в логах строки с '🚨🚨🚨 SITE INSPECTION DEBUG:'\n";
echo "5. Посмотрите, какой activityType попадает в createTabelMessage\n\n";

echo "ВОЗМОЖНЫЕ ПРИЧИНЫ:\n";
echo "==================\n";
echo "1. Кодировка все еще корруптится и activityType != 'ПроверкаМонтажнойПлощадки'\n";
echo "2. Используется другой путь в коде, который обходит наше исправление\n";
echo "3. Кэширование кода или другие проблемы с обновлением\n\n";

echo "После анализа логов, мы сможем точно определить проблему!\n";
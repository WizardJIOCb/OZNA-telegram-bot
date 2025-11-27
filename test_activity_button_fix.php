<?php
/**
 * Диагностика кнопки "Моя активность"
 */

echo "=== ДИАГНОСТИКА КНОПКИ 'МОЯ АКТИВНОСТЬ' ===\n\n";

// Проверяем, загружается ли файл без ошибок
echo "1. Проверка синтаксиса ProjectHandler.php...\n";
$syntaxCheck = shell_exec('php -l CustomCommands\Handlers\ProjectHandler.php 2>&1');
echo $syntaxCheck;

if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "✅ Синтаксис файла корректный\n\n";
} else {
    echo "❌ ПРОБЛЕМА: Ошибки синтаксиса в файле!\n\n";
    exit(1);
}

// Проверяем, существует ли метод showMyActivityMenu
echo "2. Проверка наличия нового метода...\n";
$fileContent = file_get_contents('CustomCommands\Handlers\ProjectHandler.php');

if (strpos($fileContent, 'showMyActivityMenu') !== false) {
    echo "✅ Метод showMyActivityMenu найден\n";
} else {
    echo "❌ ПРОБЛЕМА: Метод showMyActivityMenu не найден!\n";
}

if (strpos($fileContent, 'getUserActivityStatistics') !== false) {
    echo "✅ Метод getUserActivityStatistics найден\n";
} else {
    echo "❌ ПРОБЛЕМА: Метод getUserActivityStatistics не найден!\n";
}

echo "\n3. Проверка изменения в обработке callback...\n";
if (strpos($fileContent, "return \$this->showMyActivityMenu(\$chatId, \$userId);") !== false) {
    echo "✅ Вызов нового метода найден\n";
} else {
    echo "❌ ПРОБЛЕМА: Вызов нового метода не найден!\n";
}

echo "\n4. Проверка, что заглушка удалена...\n";
if (strpos($fileContent, "showDevelopmentPlaceholder(\$chatId, 'Моя активность')") !== false) {
    echo "❌ ПРОБЛЕМА: Старая заглушка всё ещё присутствует!\n";
    echo "Это может быть причиной проблемы.\n";
} else {
    echo "✅ Старая заглушка удалена\n";
}

echo "\n=== ВОЗМОЖНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ ===\n";
echo "1. Веб-сервер не был перезапущен после изменений\n";
echo "2. Кеш PHP не обновился\n";
echo "3. Ошибки в коде препятствуют загрузке\n";
echo "4. Webhook не получает обновления\n\n";

echo "=== РЕКОМЕНДАЦИИ ДЛЯ ИСПРАВЛЕНИЯ ===\n";
echo "1. Перезапустите веб-сервер\n";
echo "2. Очистите кеш PHP (если используется)\n";
echo "3. Проверьте логи сервера на наличие ошибок\n";
echo "4. Попробуйте нажать кнопку снова\n\n";

echo "Диагностика завершена.\n";
?>
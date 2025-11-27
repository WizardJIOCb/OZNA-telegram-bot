<?php

echo "🔍 ДИАГНОСТИКА ПРОБЛЕМЫ С КНОПКОЙ\n";
echo "=======================================\n\n";

// 1. Проверяем какие файлы используются
echo "📁 Проверяем основные файлы:\n\n";

// StartCommand
$startPath = __DIR__ . '/CustomCommands/StartCommand.php';
$startContent = file_get_contents($startPath);
echo "1. StartCommand.php:\n";
if (strpos($startContent, "'text' => 'Дорога'") !== false) {
    echo "   ✅ Кнопка 'Дорога' найдена\n";
} else {
    echo "   ❌ Кнопка 'Дорога' НЕ найдена\n";
}

if (strpos($startContent, "Мобилизация/демобилизация") !== false) {
    echo "   ❌ Старый текст 'Мобилизация/демобилизация' все еще есть!\n";
} else {
    echo "   ✅ Старый текст удален\n";
}

// CallbackqueryCommand_Original_Backup
$backupPath = __DIR__ . '/CustomCommands/CallbackqueryCommand_Original_Backup.php';
$backupContent = file_get_contents($backupPath);
echo "\n2. CallbackqueryCommand_Original_Backup.php:\n";
if (strpos($backupContent, "'text' => 'Дорога'") !== false) {
    echo "   ✅ Кнопка 'Дорога' найдена\n";
} else {
    echo "   ❌ Кнопка 'Дорога' НЕ найдена\n";
}

if (strpos($backupContent, '<b>Дорога</b>, отправить запись') !== false) {
    echo "   ✅ Текст подтверждения 'Дорога' найден\n";
} else {
    echo "   ❌ Текст подтверждения 'Дорога' НЕ найден\n";
}

if (strpos($backupContent, '/TabelMobile') !== false) {
    echo "   ✅ Поддержка /TabelMobile добавлена\n";
} else {
    echo "   ❌ Поддержка /TabelMobile НЕ добавлена\n";
}

echo "\n=======================================\n";
echo "🤔 ВОЗМОЖНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ:\n\n";

echo "1. Кэширование Telegram:\n";
echo "   - Telegram кэширует inline клавиатуры\n";
echo "   - Попробуйте удалить диалог с ботом и начать заново\n";
echo "   - Или подождите 10-15 минут\n\n";

echo "2. Может использоваться другой файл:\n";
echo "   - Проверьте, нет ли других файлов CallbackqueryCommand\n";
echo "   - Возможно бот читает из другой папки\n\n";

echo "3. Проблема с webhook:\n";
echo "   - Возможно нужно перезапустить webhook\n\n";

echo "=======================================\n";
echo "🎯 ЧТО ПОПРОБОВАТЬ:\n\n";
echo "1. В Telegram:\n";
echo "   - Удалить диалог с ботом\n";
echo "   - Найти бота заново и написать /start\n\n";
echo "2. Если не поможет:\n";
echo "   - Скажи мне результат, я найду другое решение\n\n";
echo "=======================================\n";

?>
<?php
/**
 * Точное удаление дубликатов из MessageService.php
 */

$file = __DIR__ . '/CustomCommands/Services/MessageService.php';
$backup = $file . '.backup_precise_' . date('Ymd_His');

echo "=== ТОЧНОЕ УДАЛЕНИЕ ДУБЛИКАТОВ ===\n\n";

copy($file, $backup);
echo "✅ Backup: $backup\n\n";

$lines = file($file);
echo "Всего строк: " . count($lines) . "\n\n";

// Удалить линии с ДУБЛИКАТАМИ (НЕ оригиналами!)
$linesToDelete = [];

// Дубликат #1: ensureUtf8Encoding (линии 134-147) - старая версия без типов
for ($i = 133; $i <= 146; $i++) {  // 0-based
    $linesToDelete[] = $i;
}

// Дубликат #2: convertToUtf8 (линии 149-161) - не используется
for ($i = 148; $i <= 160; $i++) {
    $linesToDelete[] = $i;
}

// Дубликат #3: initializeBotIfNeeded (линии 163-177) - дубликат
for ($i = 161; $i <= 176; $i++) {
    $linesToDelete[] = $i;
}

// Дубликат #4: sendMessageWithReplyKeyboard (линии 196-257) - дубликат
for ($i = 195; $i <= 256; $i++) {
    $linesToDelete[] = $i;
}

// Дубликат #5: sendMessageWithReplyKeyboard (линии 221+) - третий дубликат
// ВНИМАНИЕ: этот уже включён в предыдущий диапазон!

echo "Удаляю линии: " . count($linesToDelete) . "\n";
echo "Диапазоны: 134-147, 149-161, 163-177, 196-257\n\n";

// Создать новый контент
$newLines = [];
for ($i = 0; $i < count($lines); $i++) {
    if (!in_array($i, $linesToDelete)) {
        $newLines[] = $lines[$i];
    }
}

file_put_contents($file, implode('', $newLines));

echo "✅ Файл сохранён\n";
echo "✅ Удалено строк: " . count($linesToDelete) . "\n\n";

// Проверка синтаксиса
exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $code);

if ($code === 0) {
    echo "✅ СИНТАКСИС ПРАВИЛЬНЫЙ!\n\n";
    echo "Теперь загрузите файл на сервер:\n";
    echo "  Локальный: $file\n";
    echo "  Сервер: /var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Services/MessageService.php\n";
} else {
    echo "❌ ОШИБКА СИНТАКСИСА:\n";
    foreach ($output as $line) {
        echo "  $line\n";
    }
    echo "\nВосстанавливаю из backup...\n";
    copy($backup, $file);
    echo "✅ Восстановлено\n";
}

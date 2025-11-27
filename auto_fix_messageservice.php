<?php
/**
 * АВТОМАТИЧЕСКОЕ ИСПРАВЛЕНИЕ MessageService.php
 * Удаляет дублирующиеся функции
 */

$file = __DIR__ . '/CustomCommands/Services/MessageService.php';
$backup = __DIR__ . '/CustomCommands/Services/MessageService.php.backup_auto_' . date('Y-m-d_His');

echo "=== АВТОМАТИЧЕСКОЕ ИСПРАВЛЕНИЕ MessageService.php ===\n\n";

// Создать backup
copy($file, $backup);
echo "✅ Backup создан: {$backup}\n";

// Читать файл
$content = file_get_contents($file);
$lines = explode("\n", $content);

echo "📊 Всего строк: " . count($lines) . "\n\n";

// Определить строки для удаления (дубликаты)
$linesToRemove = [];

// ДУБЛИКАТ 1: ensureUtf8Encoding на линии 133-147 (старая версия с неправильной сигнатурой)
for ($i = 132; $i <= 146; $i++) {
    $linesToRemove[] = $i;
}

// ДУБЛИКАТ 2: convertToUtf8 на линии 149-161 (не используется)
for ($i = 148; $i <= 161; $i++) {
    $linesToRemove[] = $i;
}

// ДУБЛИКАТ 3: initializeBotIfNeeded на линии 162-177 (дубликат)
for ($i = 161; $i <= 176; $i++) {
    $linesToRemove[] = $i;
}

// ДУБЛИКАТ 4: sendMessageWithReplyKeyboard на линии 195-257 (первый дубликат)
for ($i = 194; $i <= 256; $i++) {
    $linesToRemove[] = $i;
}

// ДУБЛИКАТ 5: ensureUtf8Encoding на линии 258-269 (это ПРАВИЛЬНАЯ версия, НЕ удаляем)
// НЕ УДАЛЯЕМ - это правильная версия!

// Создать новый контент без удаленных строк
$newLines = [];
for ($i = 0; $i < count($lines); $i++) {
    if (!in_array($i, $linesToRemove)) {
        $newLines[] = $lines[$i];
    }
}

// Записать исправленный файл
$newContent = implode("\n", $newLines);
file_put_contents($file, $newContent);

echo "✅ Удалено " . count($linesToRemove) . " строк (дубликаты)\n";
echo "✅ Файл сохранен\n\n";

// Проверить синтаксис
echo "🔍 Проверка синтаксиса PHP...\n";
exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Синтаксис правильный!\n\n";
    echo "=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
    echo "\n📝 Следующие шаги:\n";
    echo "   1. Перезапустите webhook: php reset_webhook.php\n";
    echo "   2. Проверьте бота командой /start\n";
} else {
    echo "❌ ОШИБКА СИНТАКСИСА:\n";
    foreach ($output as $line) {
        echo "   " . $line . "\n";
    }
    echo "\n⚠️  Восстанавливаю из backup...\n";
    copy($backup, $file);
    echo "✅ Файл восстановлен из backup\n";
}

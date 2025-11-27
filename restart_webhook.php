<?php
// Simple webhook restart and cache clearing script

echo "=== ПЕРЕЗАПУСК WEBHOOK И ОЧИСТКА КЕША ===\n";

// 1. Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache очищен\n";
} else {
    echo "ℹ️ OPcache недоступен\n";
}

// 2. Create restart flag
$restartFlag = __DIR__ . '/restart_required.flag';
file_put_contents($restartFlag, date('Y-m-d H:i:s') . " - ProjectHandler updated\n");
echo "✓ Флаг перезапуска создан\n";

// 3. Touch hook.php to update modification time
touch(__DIR__ . '/hook.php');
echo "✓ hook.php обновлён\n";

// 4. Clear any temp files
$tempFiles = glob(__DIR__ . '/temp_*.php');
foreach ($tempFiles as $tempFile) {
    unlink($tempFile);
}
echo "✓ Временные файлы очищены\n";

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
echo "1. Перезапустите веб-сервер (Apache/Nginx)\n";
echo "2. Проверьте webhook URL в настройках Telegram бота\n";
echo "3. Протестируйте кнопку 'Проекты' в Telegram\n";
echo "\nГотово!\n";
?>
<?php
/**
 * ФИНАЛЬНАЯ ПРОВЕРКА И ИНСТРУКЦИИ
 */

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║    БОТ ИСПРАВЛЕН - ФИНАЛЬНЫЕ ШАГИ                     ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// 1. Проверить MessageService.php
echo "1️⃣  Проверка MessageService.php:\n";
exec("php -l " . __DIR__ . "/CustomCommands/Services/MessageService.php 2>&1", $output, $code);
if ($code === 0) {
    echo "   ✅ Синтаксис правильный\n\n";
} else {
    echo "   ❌ ОШИБКА СИНТАКСИСА:\n";
    foreach ($output as $line) {
        echo "      $line\n";
    }
    echo "\n";
    exit(1);
}

// 2. Проверить PHP extensions
echo "2️⃣  Проверка PHP расширений:\n";
$phpIni = php_ini_loaded_file();
echo "   PHP INI: $phpIni\n";

$iniContent = file_get_contents($phpIni);
$opensslEnabled = strpos($iniContent, 'extension=openssl') !== false && strpos($iniContent, ';extension=openssl') === false;
$curlEnabled = strpos($iniContent, 'extension=curl') !== false && strpos($iniContent, ';extension=curl') === false;

echo "   OpenSSL в php.ini: " . ($opensslEnabled ? "✅ Включен" : "❌ ВЫКЛЮЧЕН") . "\n";
echo "   cURL в php.ini: " . ($curlEnabled ? "✅ Включен" : "❌ ВЫКЛЮЧЕН") . "\n\n";

// 3. Текущее состояние загрузки
echo "3️⃣  Текущее состояние загрузки расширений:\n";
echo "   OpenSSL загружен: " . (extension_loaded('openssl') ? "✅ ДА" : "❌ НЕТ") . "\n";
echo "   cURL загружен: " . (extension_loaded('curl') ? "✅ ДА" : "❌ НЕТ") . "\n\n";

// 4. Инструкции
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║  ⚠️  КРИТИЧЕСКИ ВАЖНО  ⚠️                              ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

if (!extension_loaded('openssl') || !extension_loaded('curl')) {
    echo "🔴 РАСШИРЕНИЯ НЕ ЗАГРУЖЕНЫ!\n\n";
    echo "Необходимо ПЕРЕЗАПУСТИТЬ ВЕБ-СЕРВЕР:\n\n";
    echo "Для Apache:\n";
    echo "   net stop Apache2.4\n";
    echo "   net start Apache2.4\n\n";
    echo "Для IIS:\n";
    echo "   iisreset\n\n";
    echo "После перезапуска запустите снова:\n";
    echo "   php " . basename(__FILE__) . "\n\n";
} else {
    echo "✅ ВСЕ РАСШИРЕНИЯ ЗАГРУЖЕНЫ!\n\n";
    echo "Следующие шаги:\n";
    echo "   1. Перезапустить webhook:\n";
    echo "      php reset_webhook.php\n\n";
    echo "   2. Проверить бота:\n";
    echo "      Откройте Telegram и нажмите /start\n\n";
    echo "   3. Если бот не отвечает, проверьте логи:\n";
    echo "      Get-Content telegram_bot_error.log -Tail 20\n\n";
}

echo "════════════════════════════════════════════════════════\n";

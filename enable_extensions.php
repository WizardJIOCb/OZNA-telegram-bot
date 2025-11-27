<?php
/**
 * Включение OpenSSL и cURL в php.ini
 */

$phpIni = php_ini_loaded_file();

if (!$phpIni || !file_exists($phpIni)) {
    die("ERROR: php.ini not found!\n");
}

echo "=== ВКЛЮЧЕНИЕ PHP EXTENSIONS ===\n\n";
echo "PHP INI: $phpIni\n\n";

// Создать backup
$backup = $phpIni . '.backup_' . date('Ymd_His');
copy($phpIni, $backup);
echo "✅ Backup: $backup\n\n";

// Читать php.ini
$content = file_get_contents($phpIni);

// Включить extensions
$changed = false;

if (strpos($content, ';extension=openssl') !== false) {
    $content = str_replace(';extension=openssl', 'extension=openssl', $content);
    echo "✅ Enabled: extension=openssl\n";
    $changed = true;
} elseif (strpos($content, 'extension=openssl') !== false) {
    echo "✓  Already enabled: extension=openssl\n";
} else {
    echo "⚠️  Not found in php.ini: extension=openssl\n";
}

if (strpos($content, ';extension=curl') !== false) {
    $content = str_replace(';extension=curl', 'extension=curl', $content);
    echo "✅ Enabled: extension=curl\n";
    $changed = true;
} elseif (strpos($content, 'extension=curl') !== false) {
    echo "✓  Already enabled: extension=curl\n";
} else {
    echo "⚠️  Not found in php.ini: extension=curl\n";
}

if ($changed) {
    file_put_contents($phpIni, $content);
    echo "\n✅ php.ini updated!\n\n";
    echo "⚠️  ВАЖНО: Перезапустите веб-сервер!\n";
    echo "   Apache: net stop Apache2.4 && net start Apache2.4\n";
    echo "   IIS: iisreset\n";
} else {
    echo "\n✓  Никаких изменений не требуется\n";
}

echo "\n=== ЗАВЕРШЕНО ===\n";

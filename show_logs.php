<?php
header('Content-Type: text/plain; charset=utf-8');

$logFile = __DIR__ . '/telegram_bot_error.log';

if (!file_exists($logFile)) {
    echo "Файл лога не найден\n";
    exit;
}

$content = file_get_contents($logFile);

if (empty($content)) {
    echo "Лог пустой\n";
    exit;
}

// Показываем последние 50 строк
$lines = explode("\n", $content);
$lines = array_slice($lines, -50);

foreach ($lines as $line) {
    echo $line . "\n";
}

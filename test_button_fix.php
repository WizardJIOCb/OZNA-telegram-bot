<?php

echo "🧪 Тестируем исправление кнопки 'Дорога'...\n";
echo "=====================================\n\n";

// Проверяем StartCommand
$startCommandPath = __DIR__ . '/CustomCommands/StartCommand.php';
$startContent = file_get_contents($startCommandPath);

echo "📁 Проверяем StartCommand.php:\n";
if (strpos($startContent, "'text' => 'Дорога'") !== false) {
    echo "✅ StartCommand: кнопка 'Дорога' найдена\n";
} else {
    echo "❌ StartCommand: кнопка 'Дорога' НЕ найдена\n";
}

if (strpos($startContent, "Мобилизация/демобилизация") !== false) {
    echo "❌ StartCommand: старый текст 'Мобилизация/демобилизация' ВСЁ ЕЩЁ есть!\n";
} else {
    echo "✅ StartCommand: старый текст удален\n";
}

// Проверяем UploadCommand
$uploadCommandPath = __DIR__ . '/CustomCommands/UploadCommand.php';
$uploadContent = file_get_contents($uploadCommandPath);

echo "\n📁 Проверяем UploadCommand.php:\n";
if (strpos($uploadContent, "'text' => 'Дорога'") !== false) {
    echo "✅ UploadCommand: кнопка 'Дорога' найдена\n";
} else {
    echo "❌ UploadCommand: кнопка 'Дорога' НЕ найдена\n";
}

if (strpos($uploadContent, "Мобилизация/демобилизация") !== false) {
    echo "❌ UploadCommand: старый текст 'Мобилизация/демобилизация' ВСЁ ЕЩЁ есть!\n";
} else {
    echo "✅ UploadCommand: старый текст удален\n";
}

// Проверяем MessageService
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
$messageContent = file_get_contents($messageServicePath);

echo "\n📁 Проверяем MessageService.php:\n";
if (strpos($messageContent, "'text' => 'Дорога'") !== false) {
    echo "✅ MessageService: кнопка 'Дорога' найдена\n";
} else {
    echo "❌ MessageService: кнопка 'Дорога' НЕ найдена\n";
}

echo "\n=====================================\n";
echo "Проверка завершена!\n";
echo "Если все ✅, но в Telegram кнопка не изменилась,\n";
echo "попробуйте:\n";
echo "1. Перезапустить бота\n";
echo "2. Очистить диалог с ботом\n"; 
echo "3. Начать новый диалог командой /start\n";
echo "=====================================\n";

?>
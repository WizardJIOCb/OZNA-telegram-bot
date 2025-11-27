<?php

/**
 * Скрипт для очистки доступа админов и заявок
 */

echo "Начинаем очистку доступа админов и заявок...\n";

// 1. Удалим Близнецова из списка супер-админов
$adminServiceFile = __DIR__ . '/CustomCommands\Services\AdminService.php';
if (file_exists($adminServiceFile)) {
    $content = file_get_contents($adminServiceFile);
    
    // Удаляем Близнецова (1090118386) из списка супер-админов
    $pattern = '/\s*1090118386\s*,?\s*\/\/\s*Близи\s*/';
    $replacement = '';
    $content = preg_replace($pattern, $replacement, $content);
    
    // Также удаляем Олега если он есть
    $pattern2 = '/\s*444234398\s*,?\s*\/\/\s*Олег\s*olegpysh\s*/';
    $content = preg_replace($pattern2, $replacement, $content);
    
    file_put_contents($adminServiceFile, $content);
    echo "✅ Удалили Близнецова и Олега из списка супер-админов\n";
} else {
    echo "❌ Файл AdminService.php не найден\n";
}

// 2. Очистим файл заявок на доступ
$accessRequestsFile = __DIR__ . '/data/access_requests.json';
if (file_exists($accessRequestsFile)) {
    // Создаем пустой массив заявок
    $emptyRequests = [];
    file_put_contents($accessRequestsFile, json_encode($emptyRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ Очистили файл заявок на доступ\n";
} else {
    echo "❌ Файл access_requests.json не найден\n";
}

// 3. Проверим текущий список супер-админов
echo "\nТекущий список супер-админов:\n";
if (file_exists($adminServiceFile)) {
    $content = file_get_contents($adminServiceFile);
    // Найдем все ID супер-админов
    preg_match_all('/(\d+)\s*,?\s*\/\/\s*([^\/\n\r]+)/', $content, $matches);
    if (!empty($matches[1])) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            echo "  ID: {$matches[1][$i]} - {$matches[2][$i]}\n";
        }
    } else {
        echo "  Список пуст или не найден\n";
    }
}

echo "\n✅ Очистка завершена!\n";
echo "Теперь можно протестировать бота в Telegram\n";
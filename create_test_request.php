<?php
// Простой скрипт для создания тестовой заявки на доступ

// Создаем тестовую заявку
$testRequest = [
    [
        'user_id' => 123456789,
        'user_name' => 'Тестовый пользователь',
        'user_telegram_name' => 'Test User',
        'user_fio' => 'Иванов Иван Иванович',
        'chat_id' => '987654321',
        'timestamp' => time()
    ]
];

// Сохраняем в файл
$filePath = __DIR__ . '/data/access_requests.json';
$jsonData = json_encode($testRequest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($filePath, $jsonData);

echo "Тестовая заявка успешно создана!\n";
echo "Файл: " . $filePath . "\n";
echo "Содержимое файла:\n";
echo $jsonData . "\n";
?>
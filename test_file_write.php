<?php
// Тестовый скрипт для проверки записи в файл
$data = [
    [
        'user_id' => 12345,
        'user_name' => 'Test User',
        'chat_id' => '67890',
        'timestamp' => time()
    ]
];

$jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$result = file_put_contents(__DIR__ . '/data/access_requests.json', $jsonData);

if ($result !== false) {
    echo "Успешно записано $result байт в файл\n";
    echo "Содержимое файла:\n";
    echo file_get_contents(__DIR__ . '/data/access_requests.json');
} else {
    echo "Ошибка записи в файл\n";
    $error = error_get_last();
    if ($error) {
        echo "Ошибка: " . $error['message'] . "\n";
    }
}
?>
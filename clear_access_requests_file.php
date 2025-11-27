<?php
/**
 * Script to clear access requests file
 */

$filePath = __DIR__ . '/data/access_requests.json';

echo "=== Очистка файла с заявками на доступ ===\n\n";

// Проверяем наличие файла
if (!file_exists($filePath)) {
    echo "Файл {$filePath} не найден.\n";
    exit(1);
}

// Получаем размер файла
$fileSize = filesize($filePath);
echo "Размер файла: {$fileSize} байт\n";

// Читаем содержимое файла
echo "Чтение содержимого файла...\n";
$data = file_get_contents($filePath);

// Проверяем, является ли содержимое корректным JSON
$jsonData = json_decode($data, true);

echo "Структура данных в файле:\n";
if (is_array($jsonData)) {
    echo "Тип данных: массив\n";
    echo "Количество элементов: " . count($jsonData) . "\n";
    
    // Показываем структуру первого элемента (если есть)
    if (count($jsonData) > 0) {
        echo "Структура первого элемента:\n";
        $firstElement = $jsonData[0];
        foreach ($firstElement as $key => $value) {
            echo "  - {$key}: " . (is_string($value) ? $value : gettype($value)) . "\n";
        }
    }
} else {
    echo "Тип данных: " . gettype($jsonData) . "\n";
    echo "Ошибка JSON: " . json_last_error_msg() . "\n";
}

// Спрашиваем пользователя, хочет ли он очистить файл
echo "\nХотите очистить файл? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim($line) == 'y' || trim($line) == 'Y') {
    echo "Очистка файла...\n";
    
    // Создаем пустой массив и сохраняем его в файл
    $emptyData = [];
    $jsonData = json_encode($emptyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // Удаляем BOM если есть
    if (substr($jsonData, 0, 3) === "\xEF\xBB\xBF") {
        $jsonData = substr($jsonData, 3);
    }
    
    // Сохраняем пустой массив в файл
    if (file_put_contents($filePath, $jsonData) !== false) {
        echo "Файл успешно очищен.\n";
        echo "Новый размер файла: " . filesize($filePath) . " байт\n";
    } else {
        echo "Ошибка при очистке файла.\n";
    }
} else {
    echo "Очистка отменена.\n";
}

echo "\n=== Очистка файла завершена ===\n";

?>
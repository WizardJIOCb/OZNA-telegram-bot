<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

echo "=== БЫСТРЫЙ ТЕСТ: ШИФР ПРОЕКТА ===\n\n";

// Тестируем на Муфаззалове
$userId = 5124845625;

echo "Тестовый пользователь: Муфаззалов (ID: $userId)\n\n";

// Получаем шифр проекта
$projectCode = $personnelService->getCurrentProjectCode($userId);

echo "Результат:\n";
echo "Шифр проекта: " . ($projectCode ?: '(не найден)') . "\n\n";

// Проверяем последние записи
$conn = $dbService->getConnection();
$query = "SELECT dt, message FROM ozna_tabel WHERE user_id = ? ORDER BY dt DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

echo "Последние 5 записей:\n";
echo str_repeat("-", 70) . "\n";

while ($row = $result->fetch_assoc()) {
    // Извлекаем проект из сообщения
    preg_match('/Проект <b>(.+?)<\/b>/', $row['message'], $matches);
    $project = $matches[1] ?? 'не найден';
    
    preg_match('/запись в табель:\s*<b>(.+?)<\/b>/', $row['message'], $matches);
    $activity = $matches[1] ?? 'Неизвестно';
    
    echo substr($row['dt'], 0, 10) . " | Проект: $project | $activity\n";
}

echo str_repeat("-", 70) . "\n";

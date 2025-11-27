<?php

/**
 * Тестовый скрипт для проверки функциональности суперадминов в базе данных
 */

require_once 'config.php';
require_once 'CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "Тестирование функциональности суперадминов в базе данных\n";
echo "=====================================================\n\n";

try {
    // Создаем экземпляр DatabaseService
    $dbService = new DatabaseService();
    
    // Тестируемый пользователь ID (замените на реальный ID для тестирования)
    $testUserId = 230379668; // Пример ID пользователя
    
    echo "1. Проверка текущего статуса суперадмина для пользователя {$testUserId}:\n";
    $isSuperAdmin = $dbService->isUserSuperAdmin($testUserId);
    echo "   Статус суперадмина: " . ($isSuperAdmin ? "Да" : "Нет") . "\n\n";
    
    echo "2. Получение списка всех суперадминов:\n";
    $superAdmins = $dbService->getAllSuperAdmins();
    echo "   Суперадмины: " . implode(", ", $superAdmins) . "\n\n";
    
    echo "3. Назначение пользователя {$testUserId} суперадмином:\n";
    $result = $dbService->setSuperAdmin($testUserId, true);
    echo "   Результат назначения: " . ($result ? "Успешно" : "Ошибка") . "\n\n";
    
    echo "4. Повторная проверка статуса суперадмина для пользователя {$testUserId}:\n";
    $isSuperAdmin = $dbService->isUserSuperAdmin($testUserId);
    echo "   Статус суперадмина: " . ($isSuperAdmin ? "Да" : "Нет") . "\n\n";
    
    echo "5. Получение обновленного списка всех суперадминов:\n";
    $superAdmins = $dbService->getAllSuperAdmins();
    echo "   Суперадмины: " . implode(", ", $superAdmins) . "\n\n";
    
    echo "6. Сброс статуса суперадмина для пользователя {$testUserId}:\n";
    $result = $dbService->setSuperAdmin($testUserId, false);
    echo "   Результат сброса: " . ($result ? "Успешно" : "Ошибка") . "\n\n";
    
    echo "7. Финальная проверка статуса суперадмина для пользователя {$testUserId}:\n";
    $isSuperAdmin = $dbService->isUserSuperAdmin($testUserId);
    echo "   Статус суперадмина: " . ($isSuperAdmin ? "Да" : "Нет") . "\n\n";
    
    echo "Тестирование завершено успешно!\n";
    
} catch (Exception $e) {
    echo "Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo "Код ошибки: " . $e->getCode() . "\n";
}
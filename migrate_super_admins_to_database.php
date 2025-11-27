<?php

/**
 * Скрипт миграции существующих суперадминов из хардкода в базу данных
 */

require_once 'config.php';
require_once 'CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "Миграция существующих суперадминов из хардкода в базу данных\n";
echo "============================================================\n\n";

try {
    // Создаем экземпляр DatabaseService
    $dbService = new DatabaseService();
    
    // Список суперадминов из старого хардкода
    $oldSuperAdmins = [
        230379668,  // Ваш ID (из StartCommand.php)
        5261465966  // Боря
        // 1090118386  // Близи (удален из суперадминов)
        // 444234398   // Олег olegpysh (удален из суперадминов)
    ];
    
    echo "Список суперадминов для миграции:\n";
    foreach ($oldSuperAdmins as $adminId) {
        echo "  - {$adminId}\n";
    }
    echo "\n";
    
    // Мигрируем каждого суперадмина в базу данных
    foreach ($oldSuperAdmins as $adminId) {
        echo "Назначение пользователя {$adminId} суперадмином... ";
        $result = $dbService->setSuperAdmin($adminId, true);
        echo ($result ? "Успешно" : "Ошибка") . "\n";
    }
    
    echo "\n";
    echo "Проверка миграции:\n";
    $superAdmins = $dbService->getAllSuperAdmins();
    echo "Суперадмины в базе данных: " . implode(", ", $superAdmins) . "\n\n";
    
    echo "Миграция завершена успешно!\n";
    echo "Теперь можно удалить хардкод списка суперадминов из AdminService.php\n";
    
} catch (Exception $e) {
    echo "Ошибка при миграции: " . $e->getMessage() . "\n";
    echo "Код ошибки: " . $e->getCode() . "\n";
}
<?php

/**
 * Скрипт для назначения пользователя суперадмином
 */

require_once 'config.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Назначение пользователя суперадмином\n";
echo "================================\n\n";

try {
    // Создаем экземпляры сервисов
    $messageService = new MessageService();
    $dbService = new DatabaseService();
    
    // Данные пользователя
    $userId = 5261465966; // Ваш user ID
    $userFio = "Богданов Борис Сергеевич";
    
    echo "Пользователь ID: {$userId}\n";
    echo "ФИО: {$userFio}\n\n";
    
    // Получаем текущие данные пользователя
    echo "1. Получение текущих данных пользователя...\n";
    $userData = $dbService->getUserData($userId);
    echo "Текущие данные: " . json_encode($userData, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Обновляем ФИО пользователя
    echo "2. Обновление ФИО пользователя...\n";
    $userData['fio'] = $userFio;
    
    // Назначаем уровень доступа суперадмина
    echo "3. Назначение уровня доступа суперадмина...\n";
    $userData['access_level'] = DatabaseService::ACCESS_LEVEL_SUPER_ADMIN;
    $userData['is_super_admin'] = true;
    
    // Обновляем данные пользователя в базе данных
    echo "4. Сохранение данных в базе данных...\n";
    $success = $dbService->updateUserData($userId, $userData);
    
    if ($success) {
        echo "✅ Успешно! Пользователь назначен суперадмином.\n\n";
        
        // Проверяем обновленные данные
        echo "5. Проверка обновленных данных...\n";
        $updatedUserData = $dbService->getUserData($userId);
        echo "Обновленные данные: " . json_encode($updatedUserData, JSON_UNESCAPED_UNICODE) . "\n\n";
        
        // Проверяем уровень доступа
        $accessLevel = $dbService->getUserAccessLevel($userId);
        $isSuperAdmin = $dbService->isUserSuperAdmin($userId);
        
        echo "Уровень доступа: {$accessLevel}\n";
        echo "Суперадмин: " . ($isSuperAdmin ? "Да" : "Нет") . "\n\n";
        
        echo "Готово! Теперь у вас должен быть доступ к админке.\n";
    } else {
        echo "❌ Ошибка при обновлении данных пользователя.\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Код ошибки: " . $e->getCode() . "\n";
}
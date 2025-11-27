<?php

/**
 * БЫСТРОЕ РЕШЕНИЕ: Предоставляем доступ к кнопке "Проекты" конкретному пользователю
 * 
 * ИНСТРУКЦИЯ:
 * 1. Узнайте Telegram ID пользователя, который жалуется на нерабочую кнопку
 * 2. Замените 123456789 на реальный ID пользователя
 * 3. Запустите этот скрипт: php fix_projects_button.php
 */

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// ========================================
// ЗАМЕНИТЕ НА РЕАЛЬНЫЙ ID ПОЛЬЗОВАТЕЛЯ:
// ========================================
$userId = 123456789; // ← ЗДЕСЬ УКАЖИТЕ ID ПОЛЬЗОВАТЕЛЯ

echo "=== ИСПРАВЛЕНИЕ КНОПКИ ПРОЕКТЫ ДЛЯ ПОЛЬЗОВАТЕЛЯ {$userId} ===\n\n";

try {
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "1. Проверяем текущий статус пользователя...\n";
    $currentLevel = $adminService->getUserAccessLevel($userId);
    $levelName = $adminService->getAccessLevelName($currentLevel);
    
    echo "   Текущий уровень доступа: {$currentLevel} ({$levelName})\n";
    
    if ($currentLevel >= 1) {
        echo "   ✓ Пользователь уже имеет доступ к кнопке 'Проекты'\n";
        echo "   ℹ️  Если кнопка всё ещё не работает, возможно есть другие проблемы.\n\n";
        
        // Проверяем, что кнопка действительно есть в меню
        $keyboard = $messageService->createMainMenuKeyboard($userId);
        $hasProjectsButton = false;
        
        foreach ($keyboard as $row) {
            foreach ($row as $button) {
                if (isset($button['text']) && strpos($button['text'], 'Проекты') !== false) {
                    $hasProjectsButton = true;
                    echo "   ✓ Кнопка 'Проекты' присутствует в меню пользователя\n";
                    echo "   ✓ Callback данные: {$button['callback_data']}\n";
                    break 2;
                }
            }
        }
        
        if (!$hasProjectsButton) {
            echo "   ✗ СТРАННО: Кнопка 'Проекты' не найдена в меню, хотя уровень доступа достаточный\n";
        }
        
    } else {
        echo "   ❌ Пользователь имеет уровень 'Гость' - кнопка 'Проекты' не отображается\n\n";
        
        echo "2. Устанавливаем уровень доступа 'Пользователь'...\n";
        $success = $adminService->updateUserAccessLevel($userId, 1);
        
        if ($success) {
            echo "   ✅ УСПЕШНО! Уровень доступа изменён на 'Пользователь'\n\n";
            
            // Проверяем результат
            $newLevel = $adminService->getUserAccessLevel($userId);
            echo "3. Проверяем результат...\n";
            echo "   ✓ Новый уровень доступа: {$newLevel} (Пользователь)\n";
            
            // Тестируем меню
            $keyboard = $messageService->createMainMenuKeyboard($userId);
            $hasProjectsButton = false;
            
            foreach ($keyboard as $row) {
                foreach ($row as $button) {
                    if (isset($button['text']) && strpos($button['text'], 'Проекты') !== false) {
                        $hasProjectsButton = true;
                        echo "   ✅ Кнопка 'Проекты' теперь доступна!\n";
                        echo "   ✓ Callback данные: {$button['callback_data']}\n";
                        break 2;
                    }
                }
            }
            
            if (!$hasProjectsButton) {
                echo "   ❌ ОШИБКА: Кнопка всё ещё не появилась\n";
            }
            
        } else {
            echo "   ❌ ОШИБКА: Не удалось изменить уровень доступа\n";
        }
    }
    
    echo "\n=== ИНСТРУКЦИИ ДЛЯ ПОЛЬЗОВАТЕЛЯ ===\n";
    echo "Сообщите пользователю:\n";
    echo "1. Отправьте команду /start боту\n";
    echo "2. Проверьте главное меню - кнопка 'Проекты' должна появиться\n";
    echo "3. При нажатии на кнопку должно открыться меню проектов\n\n";
    
    echo "Если проблема не решилась:\n";
    echo "- Проверьте, что webhook бота работает\n";
    echo "- Убедитесь, что нет ошибок в логах\n";
    echo "- Попробуйте перезапустить бота\n";
    
} catch (Exception $e) {
    echo "❌ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
}

echo "\n=== ГОТОВО ===\n";
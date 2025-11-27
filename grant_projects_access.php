<?php

/**
 * Быстрое решение проблемы: предоставление доступа к кнопке "Проекты"
 * Этот скрипт позволяет быстро повысить уровень доступа пользователя
 */

echo "=== ПРЕДОСТАВЛЕНИЕ ДОСТУПА К КНОПКЕ ПРОЕКТЫ ===\n\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

try {
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "Введите Telegram ID пользователя, которому нужно предоставить доступ к кнопке 'Проекты':\n";
    echo "(Если не знаете ID, попросите пользователя отправить любое сообщение боту и проверьте логи)\n\n";
    
    // Список пользователей без доступа из предыдущей диагностики
    echo "Пользователи без доступа к кнопке 'Проекты' (уровень 0 - Гость):\n";
    $guestUsers = [
        1094892131, 1207207617, 1216736577, 1218016377, 1221067449, 1235068093,
        1240803842, 1249648593, 1375765439, 1408467736, 1495808905, 1536064077,
        1765926037, 1771845406, 1849993688, 1969336012, 2032636476, 2099481062,
        2128308870, 2178802169, 2261673041, 2473449915, 2529736072, 2535952068,
        2653765779, 2698772966, 2735985697, 2795950966, 2819507804, 2986998073,
        3064968055, 3094866968, 3134012966, 3247473506, 3309778644, 3450095509,
        3512421635, 3617859436, 3675869513, 3760717949, 3804089717, 3923893690,
        3997063067, 4062522069, 4169064805, 4276467204, 4356767435, 4441567508,
        4480726916, 4589609804, 4742967436, 4805678091, 4856439650, 4982067517,
        5024567892, 5086743291, 5128469737, 5196723048, 5261465966, 5349870216,
        5452136790, 5478651293, 5546781429, 5598465942, 5606381230, 5614471995,
        5661501287, 5667781632, 5697306786, 5722042635, 6095261415, 6645564928,
        6689630295, 7015322155
    ];
    
    foreach ($guestUsers as $userId) {
        echo "  ID: {$userId}\n";
    }
    
    echo "\nВведите ID пользователя (или 'all' для всех): ";
    $handle = fopen("php://stdin", "r");
    $input = trim(fgets($handle));
    fclose($handle);
    
    if ($input === 'all') {
        echo "\nПредоставляем доступ всем пользователям-гостям...\n";
        
        foreach ($guestUsers as $userId) {
            $currentLevel = $adminService->getUserAccessLevel($userId);
            if ($currentLevel == 0) {
                $success = $adminService->updateUserAccessLevel($userId, 1);
                if ($success) {
                    echo "✓ Пользователю {$userId} установлен уровень доступа 1 (Пользователь)\n";
                } else {
                    echo "✗ Ошибка установки уровня доступа для пользователя {$userId}\n";
                }
            } else {
                echo "! Пользователь {$userId} уже имеет уровень доступа {$currentLevel}\n";
            }
        }
        
    } elseif (is_numeric($input)) {
        $userId = (int)$input;
        
        echo "\nПроверяем текущий уровень доступа пользователя {$userId}...\n";
        $currentLevel = $adminService->getUserAccessLevel($userId);
        $levelName = $adminService->getAccessLevelName($currentLevel);
        
        echo "Текущий уровень: {$currentLevel} ({$levelName})\n";
        
        if ($currentLevel >= 1) {
            echo "✓ Пользователь уже имеет доступ к кнопке 'Проекты'\n";
            echo "  Если кнопка всё ещё не работает, проблема может быть в другом.\n";
        } else {
            echo "\nУстанавливаем уровень доступа 1 (Пользователь)...\n";
            $success = $adminService->updateUserAccessLevel($userId, 1);
            
            if ($success) {
                echo "✓ УСПЕШНО! Пользователю {$userId} установлен уровень доступа 1\n";
                echo "✓ Теперь кнопка 'Проекты' должна появиться в главном меню\n";
                
                // Проверяем, что изменения применились
                $newLevel = $adminService->getUserAccessLevel($userId);
                echo "✓ Подтверждение: новый уровень доступа = {$newLevel}\n";
                
                // Тестируем создание меню
                $keyboard = $messageService->createMainMenuKeyboard($userId);
                $hasProjectsButton = false;
                
                foreach ($keyboard as $row) {
                    foreach ($row as $button) {
                        if (isset($button['text']) && strpos($button['text'], 'Проекты') !== false) {
                            $hasProjectsButton = true;
                            break 2;
                        }
                    }
                }
                
                if ($hasProjectsButton) {
                    echo "✓ ПОДТВЕРЖДЕНО: Кнопка 'Проекты' теперь видна в меню пользователя\n";
                } else {
                    echo "✗ ВНИМАНИЕ: Кнопка 'Проекты' всё ещё не видна. Возможно, есть другие проблемы.\n";
                }
                
            } else {
                echo "✗ ОШИБКА: Не удалось установить уровень доступа\n";
            }
        }
        
    } else {
        echo "Некорректный ввод. Завершение работы.\n";
        exit(1);
    }
    
    echo "\n=== ДОПОЛНИТЕЛЬНАЯ ИНФОРМАЦИЯ ===\n";
    echo "Уровни доступа:\n";
    echo "  0 - Гость (нет доступа к 'Проекты')\n";
    echo "  1 - Пользователь (есть доступ к 'Проекты')\n";
    echo "  2 - Супервайзер (есть доступ к 'Проекты' + дополнительные функции)\n";
    echo "  3 - Администратор (полный доступ)\n";
    echo "  4 - Суперадминистратор (максимальный доступ)\n\n";
    
    echo "Если проблема не решилась:\n";
    echo "1. Убедитесь, что пользователь перезапустил бота (/start)\n";
    echo "2. Проверьте логи ошибок бота\n";
    echo "3. Убедитесь, что CallbackqueryCommand.php содержит ProjectHandler\n";
    echo "4. Проверьте, что нет конфликтов с другими обработчиками\n";
    
} catch (Exception $e) {
    echo "✗ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трассировка:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== ЗАВЕРШЕНО ===\n";
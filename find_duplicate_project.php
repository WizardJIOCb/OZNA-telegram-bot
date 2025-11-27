<?php
/**
 * Найти проект, который вызывает дублирование отчетов
 */

echo "=== ПОИСК ПРОЕКТА С ДУБЛИРОВАНИЕМ ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    echo "Анализируем всех пользователей с проектами на контроле...\n\n";
    
    $allUsers = $databaseService->getAllUsers();
    
    // Настройки групп для функциональных направлений
    $fdGroups = [
        'Пусконаладка' => [
            'group_id' => '-1001400593888',
            'thread_id' => '338'
        ],
        'Шеф-монтаж' => [
            'group_id' => '-1001865074576',
            'thread_id' => '14627'
        ],
        'Мобильная бригада' => [
            'group_id' => '-1002722176147',
            'thread_id' => '2'
        ]
    ];
    
    $problemsFound = [];
    
    foreach ($allUsers as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'];
        $fio = $userData['fio'] ?? 'Неизвестно';
        $fd = $userData['functional_direction'] ?? 'НЕ УКАЗАНО';
        
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (empty($controlProjects)) {
            continue;
        }
        
        echo "Пользователь: {$fio}\n";
        echo "  ID: {$userId}\n";
        echo "  ФН: {$fd}\n";
        echo "  Проектов на контроле: " . count($controlProjects) . "\n\n";
        
        foreach ($controlProjects as $projectCode => $settings) {
            echo "  Проект: {$projectCode}\n";
            echo "    Group ID: " . ($settings['group_id'] ?? 'НЕ НАСТРОЕНО') . "\n";
            echo "    Chat ID: " . ($settings['chat_id'] ?? 'НЕ НАСТРОЕНО') . "\n";
            
            // Проверяем на потенциальное дублирование
            $projectGroupId = $settings['group_id'] ?? '';
            $projectChatId = $settings['chat_id'] ?? '';
            
            // Проверяем для каждого ФН
            foreach ($fdGroups as $fdName => $fdSettings) {
                if ($projectGroupId === $fdSettings['group_id']) {
                    echo "    ⚠️ СОВПАДЕНИЕ с группой ФН '{$fdName}'!\n";
                    
                    // Проверяем детали
                    if (empty($projectChatId) || $projectChatId === 'personal') {
                        echo "    ❌ ДУБЛИРОВАНИЕ! Chat ID пустой или 'personal' - попадет в основную ленту группы\n";
                        echo "    📍 ПРИЧИНА: Отчет попадет и в тему ФН, и в основную ленту той же группы\n";
                        
                        $problemsFound[] = [
                            'user' => $fio,
                            'user_id' => $userId,
                            'fd' => $fd,
                            'project' => $projectCode,
                            'group_id' => $projectGroupId,
                            'chat_id' => $projectChatId,
                            'issue' => "Отчет попадает в группу {$fdName} дважды"
                        ];
                    } elseif ($projectChatId === $fdSettings['thread_id']) {
                        echo "    ❌ ДУБЛИРОВАНИЕ! Thread ID совпадает - попадет в ту же тему дважды\n";
                        echo "    📍 ПРИЧИНА: Отчет попадет в тему '{$projectChatId}' дважды\n";
                        
                        $problemsFound[] = [
                            'user' => $fio,
                            'user_id' => $userId,
                            'fd' => $fd,
                            'project' => $projectCode,
                            'group_id' => $projectGroupId,
                            'chat_id' => $projectChatId,
                            'issue' => "Отчет попадает в тему {$projectChatId} дважды"
                        ];
                    } else {
                        echo "    ✅ Нет дублирования - Thread ID ({$projectChatId}) отличается от ФН ({$fdSettings['thread_id']})\n";
                    }
                }
            }
            
            // Специальная проверка на Мобильную бригаду
            if ($projectGroupId === '-1002722176147') {
                echo "    🎯 Использует группу Мобильной бригады!\n";
                if ($fd !== 'Мобильная бригада') {
                    echo "    ⚠️ ВНИМАНИЕ: Пользователь не из Мобильной бригады, но использует их группу!\n";
                }
            }
            
            echo "\n";
        }
        
        echo "---\n\n";
    }
    
    echo "\n=== НАЙДЕННЫЕ ПРОБЛЕМЫ ===\n\n";
    
    if (empty($problemsFound)) {
        echo "✅ Проблем с дублированием не найдено!\n";
        echo "Все проекты настроены корректно.\n";
    } else {
        echo "❌ Найдено проблем: " . count($problemsFound) . "\n\n";
        
        foreach ($problemsFound as $index => $problem) {
            echo "Проблема #" . ($index + 1) . ":\n";
            echo "  Пользователь: {$problem['user']} (ID: {$problem['user_id']})\n";
            echo "  ФН пользователя: {$problem['fd']}\n";
            echo "  Проблемный проект: {$problem['project']}\n";
            echo "  Настройки:\n";
            echo "    Group ID: {$problem['group_id']}\n";
            echo "    Chat ID: " . ($problem['chat_id'] ?: 'ПУСТО') . "\n";
            echo "  Проблема: {$problem['issue']}\n";
            echo "\n";
        }
        
        echo "\n🔧 РЕКОМЕНДАЦИИ ПО ИСПРАВЛЕНИЮ:\n\n";
        echo "Для каждого проблемного проекта нужно:\n";
        echo "1. Либо изменить Group ID на другую группу\n";
        echo "2. Либо указать корректный Chat ID (thread_id), отличный от thread_id ФН\n";
        echo "3. Либо удалить проект из контроля, если он больше не актуален\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== АНАЛИЗ ЗАВЕРШЕН ===\n";
?>

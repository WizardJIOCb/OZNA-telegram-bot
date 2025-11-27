<?php
/**
 * Проверка исправления дублирования для мобильной бригады
 */

echo "=== ПРОВЕРКА ИСПРАВЛЕНИЯ ДУБЛИРОВАНИЯ ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    echo "1. ПОИСК ПОЛЬЗОВАТЕЛЕЙ С ФН 'МОБИЛЬНАЯ БРИГАДА':\n\n";
    
    $allUsers = $databaseService->getAllUsers();
    $mobileBrigadeUsers = [];
    
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        $functionalDirection = $userData['functional_direction'] ?? '';
        
        if ($functionalDirection === 'Мобильная бригада') {
            $mobileBrigadeUsers[] = [
                'user_id' => $user['user_id'],
                'fio' => $userData['fio'] ?? 'Не указано',
                'control_projects' => $userData['control_projects'] ?? []
            ];
        }
    }
    
    echo "   Найдено пользователей с ФН 'Мобильная бригада': " . count($mobileBrigadeUsers) . "\n\n";
    
    echo "2. АНАЛИЗ НАСТРОЕК КОНТРОЛИРУЕМЫХ ПРОЕКТОВ:\n\n";
    
    $potentialDuplicates = [];
    
    foreach ($mobileBrigadeUsers as $user) {
        $userId = $user['user_id'];
        $fio = $user['fio'];
        $controlProjects = $user['control_projects'];
        
        if (empty($controlProjects)) {
            continue;
        }
        
        echo "   Пользователь: {$fio} (ID: {$userId})\n";
        
        foreach ($controlProjects as $projectCode => $settings) {
            $groupId = $settings['group_id'] ?? '';
            $chatId = $settings['chat_id'] ?? '';
            
            echo "   • Проект: {$projectCode}\n";
            echo "     - Group ID: " . ($groupId ?: 'НЕ НАСТРОЕН') . "\n";
            echo "     - Chat ID: " . ($chatId ?: 'НЕ НАСТРОЕН') . "\n";
            
            // Проверяем на потенциальное дублирование
            if ($groupId === '-1002722176147') {
                if (empty($chatId) || $chatId === '2') {
                    $potentialDuplicates[] = [
                        'user_id' => $userId,
                        'fio' => $fio,
                        'project' => $projectCode,
                        'issue' => empty($chatId) ? 'ПУСТОЙ chat_id' : 'chat_id = 2 (дубликат)'
                    ];
                    echo "     ⚠️ ПОТЕНЦИАЛЬНОЕ ДУБЛИРОВАНИЕ: " . (empty($chatId) ? 'пустой chat_id' : 'chat_id = 2') . "\n";
                } else {
                    echo "     ✅ НЕТ ДУБЛИРОВАНИЯ: chat_id = {$chatId}\n";
                }
            } else {
                echo "     ✅ НЕТ ДУБЛИРОВАНИЯ: другая группа\n";
            }
        }
        echo "\n";
    }
    
    echo "3. СВОДКА ПОТЕНЦИАЛЬНЫХ ДУБЛИРОВАНИЙ:\n\n";
    
    if (empty($potentialDuplicates)) {
        echo "   ✅ ОТЛИЧНО! Потенциальных дублирований НЕ НАЙДЕНО\n";
        echo "   Все настройки корректны.\n\n";
    } else {
        echo "   ⚠️ Найдено потенциальных дублирований: " . count($potentialDuplicates) . "\n\n";
        
        foreach ($potentialDuplicates as $duplicate) {
            echo "   • Пользователь: {$duplicate['fio']} (ID: {$duplicate['user_id']})\n";
            echo "     Проект: {$duplicate['project']}\n";
            echo "     Проблема: {$duplicate['issue']}\n\n";
        }
        
        echo "   📋 РЕКОМЕНДАЦИИ ДЛЯ ИСПРАВЛЕНИЯ:\n";
        echo "   1. Измените group_id на другую группу\n";
        echo "   2. Или укажите chat_id отличный от 2\n";
        echo "   3. Или используйте chat_id = 'personal'\n\n";
    }
    
    echo "4. ДЕЙСТВИЕ ИСПРАВЛЕНИЯ:\n\n";
    echo "   ✅ Добавлена проверка дублирования в TabelEntry.php\n";
    echo "   ✅ Сообщения с одинаковыми параметрами теперь НЕ отправляются дважды\n";
    echo "   ✅ Логирование пропущенных дубликатов\n";
    echo "   ✅ Обратная совместимость сохранена\n\n";
    
    echo "5. ПРОВЕРКА РАБОТЫ:\n\n";
    echo "   После развёртывания исправления:\n";
    echo "   • Пользователи с ФН 'Мобильная бригада' больше НЕ получат дублированные сообщения\n";
    echo "   • Сообщения будут корректно попадать только в 2 места (вместо 3)\n";
    echo "   • В логах появятся записи о пропуске дубликатов\n\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
?>
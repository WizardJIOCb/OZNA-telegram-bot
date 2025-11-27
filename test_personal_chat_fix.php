<?php
/**
 * Тест исправленной логики отправки отчетов по проектам на контроле
 */

echo "=== ТЕСТ ИСПРАВЛЕНИЯ: Личный чат НЕ должен отправлять в группу ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    echo "Проверяем текущее состояние проекта ОИ834 у Калимуллина...\n\n";
    
    // ID Калимуллина
    $userId = 230379668;
    
    $userData = $databaseService->getUserData($userId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (!isset($controlProjects['ОИ834'])) {
        echo "❌ Проект ОИ834 не найден в контроле у пользователя\n";
        exit(1);
    }
    
    $settings = $controlProjects['ОИ834'];
    
    echo "Текущие настройки проекта ОИ834:\n";
    echo "  Group ID: " . ($settings['group_id'] ?? 'НЕ УКАЗАНО') . "\n";
    echo "  Chat ID: " . ($settings['chat_id'] ?? 'НЕ УКАЗАНО') . "\n\n";
    
    echo "=== СИМУЛЯЦИЯ ОТПРАВКИ ===\n\n";
    
    // Симулируем новую логику
    $groupId = $settings['group_id'] ?? '';
    $chatId = $settings['chat_id'] ?? '';
    
    echo "Проверка логики:\n\n";
    
    if (!empty($chatId) && $chatId === 'personal') {
        echo "✅ Chat ID = 'personal'\n";
        echo "   → Отчет будет отправлен ТОЛЬКО в личный чат пользователя {$userId}\n";
        echo "   → Group ID '{$groupId}' ИГНОРИРУЕТСЯ\n";
        echo "   → НЕТ отправки в группу!\n\n";
        echo "РЕЗУЛЬТАТ: Отчет попадет ТОЛЬКО в 1 место - личный чат\n";
    } elseif (!empty($groupId)) {
        echo "✅ Group ID указан: {$groupId}\n";
        if (!empty($chatId)) {
            echo "   → Отчет будет отправлен в группу {$groupId}, тему {$chatId}\n";
        } else {
            echo "   → Отчет будет отправлен в группу {$groupId} (основная лента)\n";
        }
        echo "\nРЕЗУЛЬТАТ: Отчет попадет в группу\n";
    } else {
        echo "❌ Ни Group ID, ни Chat ID не настроены\n";
        echo "   → Отчет НЕ будет отправлен через систему контроля проектов\n";
    }
    
    echo "\n=== ПРОВЕРКА ДУБЛИРОВАНИЯ ===\n\n";
    
    // Пользователь Калимуллин - Мобильная бригада
    $userFD = $userData['functional_direction'] ?? 'НЕ УКАЗАНО';
    echo "ФН пользователя (супервайзера): {$userFD}\n";
    
    $fdGroups = [
        'Мобильная бригада' => ['group_id' => '-1002722176147', 'thread_id' => '2']
    ];
    
    if ($userFD === 'Мобильная бригада') {
        $fdSettings = $fdGroups['Мобильная бригада'];
        echo "Группа ФН: {$fdSettings['group_id']}, Тема: {$fdSettings['thread_id']}\n\n";
        
        if ($chatId === 'personal') {
            echo "✅ НЕТ ДУБЛИРОВАНИЯ!\n";
            echo "   Причина: При chat_id='personal' отчет идет только в личку,\n";
            echo "   НЕ в группу {$groupId}, поэтому не пересекается с отправкой по ФН\n";
        } elseif ($groupId === $fdSettings['group_id']) {
            if (empty($chatId)) {
                echo "⚠️ МОЖЕТ БЫТЬ ДУБЛИРОВАНИЕ\n";
                echo "   Отчет попадет в ту же группу, но в основную ленту\n";
            } elseif ($chatId === $fdSettings['thread_id']) {
                echo "❌ ДУБЛИРОВАНИЕ!\n";
                echo "   Отчет попадет в ту же тему дважды\n";
            } else {
                echo "✅ НЕТ ДУБЛИРОВАНИЯ\n";
                echo "   Разные темы: {$chatId} vs {$fdSettings['thread_id']}\n";
            }
        } else {
            echo "✅ НЕТ ДУБЛИРОВАНИЯ\n";
            echo "   Разные группы\n";
        }
    }
    
    echo "\n=== РЕКОМЕНДАЦИЯ ===\n\n";
    
    if ($chatId === 'personal' && !empty($groupId)) {
        echo "ℹ️ У проекта ОИ834 указан и Group ID и Chat ID='personal'\n";
        echo "   После исправления Group ID будет игнорироваться\n";
        echo "   Это правильное поведение!\n\n";
        echo "   Если вы хотите получать отчеты только в личный чат - всё ОК\n";
        echo "   Если вы хотите получать в группу - нужно изменить Chat ID на номер темы\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
?>

<?php

/**
 * Тест реального отображения меню "Проекты на контроле"
 */

echo "=== ТЕСТ РЕАЛЬНОГО МЕНЮ ПРОЕКТОВ НА КОНТРОЛЕ ===\n\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;

try {
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    // Находим пользователя с проектами на контроле
    $testUserId = 4225504; // Петров Алексей Владимирович
    
    echo "1. Проверяем данные пользователя {$testUserId}...\n";
    
    $userData = $databaseService->getUserData($testUserId);
    $controlProjects = $userData['control_projects'] ?? [];
    $userLevel = $adminService->getUserAccessLevel($testUserId);
    
    echo "Уровень доступа: {$userLevel}\n";
    echo "Проекты на контроле: " . json_encode($controlProjects, JSON_UNESCAPED_UNICODE) . "\n";
    echo "Количество проектов: " . count($controlProjects) . "\n";
    
    echo "\n2. Эмулируем создание меню...\n";
    
    // Эмулируем логику из showControlProjectsMenu
    $allProjects = $adminService->getAllProjects();
    echo "Всего проектов в системе: " . count($allProjects) . "\n";
    
    // Создаём клавиатуру по той же логике
    $keyboard = [];
    
    if (!empty($allProjects)) {
        $keyboard[] = [
            ['text' => '➕ Добавить проект на контроль', 'callback_data' => 'proj_ctrl_add']
        ];
        echo "✓ Добавлена кнопка 'Добавить проект'\n";
    }
    
    if (!empty($controlProjects)) {
        $keyboard[] = [
            ['text' => '➖ Удалить проект с контроля', 'callback_data' => 'proj_ctrl_remove']
        ];
        echo "✓ Добавлена кнопка 'Удалить проект'\n";
        
        $keyboard[] = [
            ['text' => '⚙️ Настроить уведомления', 'callback_data' => 'proj_ctrl_settings']
        ];
        echo "✓ Добавлена кнопка 'Настроить уведомления'\n";
    } else {
        echo "✗ Кнопки 'Удалить' и 'Настройки' НЕ добавлены (нет проектов)\n";
    }
    
    $keyboard[] = [
        ['text' => '📋 Список проектов на контроле', 'callback_data' => 'proj_ctrl_list']
    ];
    echo "✓ Добавлена кнопка 'Список проектов'\n";
    
    $keyboard[] = [
        ['text' => '⬅️ Назад к моим проектам', 'callback_data' => 'category_Проекты_МоиПроекты']
    ];
    echo "✓ Добавлена кнопка 'Назад'\n";
    
    echo "\n3. Итоговая клавиатура:\n";
    foreach ($keyboard as $rowIndex => $row) {
        echo "Ряд " . ($rowIndex + 1) . ":\n";
        foreach ($row as $button) {
            echo "  [{$button['text']}] -> {$button['callback_data']}\n";
        }
    }
    
    echo "\n4. Проверяем реальный вызов метода showControlProjectsMenu...\n";
    
    // Создаём ProjectHandler и пытаемся вызвать метод
    $projectHandler = new ProjectHandler($adminService, $messageService);
    
    // Используем рефлексию для вызова приватного метода
    $reflection = new ReflectionClass($projectHandler);
    $method = $reflection->getMethod('showControlProjectsMenu');
    $method->setAccessible(true);
    
    echo "Вызываем showControlProjectsMenu для пользователя {$testUserId}...\n";
    
    try {
        $result = $method->invoke($projectHandler, '-1001234567890', $testUserId);
        
        if ($result && isset($result['ok']) && $result['ok']) {
            echo "✓ Метод выполнен успешно\n";
            
            if (isset($result['reply_markup'])) {
                echo "Клавиатура отправлена:\n";
                $sentKeyboard = json_decode($result['reply_markup'], true);
                if (isset($sentKeyboard['inline_keyboard'])) {
                    foreach ($sentKeyboard['inline_keyboard'] as $rowIndex => $row) {
                        echo "  Ряд " . ($rowIndex + 1) . ":\n";
                        foreach ($row as $button) {
                            echo "    [{$button['text']}] -> {$button['callback_data']}\n";
                        }
                    }
                }
            }
            
        } else {
            echo "✗ Метод вернул ошибку\n";
            if (isset($result['description'])) {
                echo "Описание: {$result['description']}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "✗ Исключение при вызове метода: " . $e->getMessage() . "\n";
    }
    
    echo "\n5. Проверяем обработку callback данных через handleControlProjectsCallback...\n";
    
    // Тестируем каждый callback
    $testCallbacks = ['proj_ctrl_add', 'proj_ctrl_remove', 'proj_ctrl_list', 'proj_ctrl_settings'];
    
    $handleMethod = $reflection->getMethod('handleControlProjectsCallback');
    $handleMethod->setAccessible(true);
    
    foreach ($testCallbacks as $testCallback) {
        echo "Тестируем callback: {$testCallback}\n";
        
        // Создаём простые мок объекты
        $mockQuery = new class($testUserId, $testCallback) {
            private $userId, $callbackData;
            public function __construct($userId, $callbackData) { 
                $this->userId = $userId; 
                $this->callbackData = $callbackData; 
            }
            public function getMessage() { 
                return new class() { 
                    public function getChat() { 
                        return new class() { 
                            public function getId() { return '-1001234567890'; } 
                        }; 
                    } 
                }; 
            }
            public function getFrom() { 
                return new class($this->userId) { 
                    private $userId;
                    public function __construct($userId) { $this->userId = $userId; }
                    public function getId() { return $this->userId; } 
                }; 
            }
            public function getData() { return $this->callbackData; }
        };
        
        $mockConversation = new class() {
            public $notes = [];
            public function update() {}
        };
        
        try {
            $result = $handleMethod->invoke($projectHandler, $mockQuery, $testCallback, $mockConversation);
            
            if ($result && isset($result['ok']) && $result['ok']) {
                echo "  ✓ Callback {$testCallback} обработан успешно\n";
            } else {
                echo "  ✗ Callback {$testCallback} вернул ошибку\n";
                if (isset($result['description'])) {
                    echo "    Описание: {$result['description']}\n";
                }
            }
        } catch (Exception $e) {
            echo "  ✗ Исключение при обработке {$testCallback}: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЁН ===\n";
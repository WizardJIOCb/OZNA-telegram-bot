<?php

/**
 * Тест симуляции нажатия кнопки "Проекты"
 * Эмулируем обработку callback данных
 */

echo "=== ТЕСТ СИМУЛЯЦИИ НАЖАТИЯ КНОПКИ ПРОЕКТЫ ===\n\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

// Создаем мок объекты для тестирования
class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "ОТПРАВКА СООБЩЕНИЯ:\n";
        echo "Chat ID: {$chatId}\n";
        echo "Текст: {$text}\n";
        if ($keyboard) {
            echo "Клавиатура:\n";
            foreach ($keyboard as $rowIndex => $row) {
                echo "  Ряд " . ($rowIndex + 1) . ":\n";
                foreach ($row as $button) {
                    echo "    [{$button['text']}] -> {$button['callback_data']}\n";
                }
            }
        }
        echo "\n";
        return ['ok' => true];
    }
}

class MockAdminService {
    public function getUserAccessLevel($userId) {
        // Возвращаем уровень пользователя (1 = обычный пользователь)
        return 1;
    }
    
    public function getAllProjects() {
        return ['ОИ834', 'ОИ651', 'БКНС115'];
    }
}

class MockCallbackQuery {
    public function getMessage() {
        return new MockMessage();
    }
    
    public function getFrom() {
        return new MockUser();
    }
    
    public function getData() {
        return 'category_Проекты';
    }
}

class MockMessage {
    public function getChat() {
        return new MockChat();
    }
}

class MockChat {
    public function getId() {
        return 123456789;
    }
}

class MockUser {
    public function getId() {
        return 987654321;
    }
}

class MockConversation {
    public $notes = [];
}

try {
    echo "1. Создаём мок объекты...\n";
    $messageService = new MockMessageService();
    $adminService = new MockAdminService();
    
    echo "2. Создаём ProjectHandler...\n";
    $projectHandler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler(
        $adminService, 
        $messageService
    );
    
    echo "3. Тестируем canHandle для 'category_Проекты'...\n";
    $canHandle = $projectHandler->canHandle('category_Проекты');
    echo "Результат canHandle: " . ($canHandle ? "✓ ДА" : "✗ НЕТ") . "\n\n";
    
    if ($canHandle) {
        echo "4. Тестируем обработку callback...\n";
        $callbackQuery = new MockCallbackQuery();
        $conversation = new MockConversation();
        
        echo "Вызываем handle метод...\n\n";
        $result = $projectHandler->handle($callbackQuery, 'Проекты', $conversation);
        
        echo "Результат обработки: ";
        if (is_array($result) && isset($result['ok'])) {
            echo "✓ УСПЕШНО\n";
        } else {
            echo "✗ ОШИБКА\n";
            var_dump($result);
        }
    } else {
        echo "4. ✗ ProjectHandler НЕ МОЖЕТ обработать 'category_Проекты'\n";
        echo "Проблема в методе canHandle!\n";
    }
    
    echo "\n5. Дополнительные тесты callback данных...\n";
    $testCallbacks = [
        'category_Проекты',
        'category_Проекты_МоиПроекты',
        'category_Проекты_МояАктивность',
        'proj_list_add',
        'category_Табель', // не должен обрабатываться
        'category_Админка' // не должен обрабатываться
    ];
    
    foreach ($testCallbacks as $callback) {
        $canHandleTest = $projectHandler->canHandle($callback);
        $should = (strpos($callback, 'Проекты') !== false || strpos($callback, 'proj_') === 0);
        
        if ($canHandleTest === $should) {
            echo "✓ '{$callback}': " . ($canHandleTest ? "обрабатывается" : "не обрабатывается") . " (правильно)\n";
        } else {
            echo "✗ '{$callback}': ожидалось " . ($should ? "обработка" : "игнорирование") . 
                 ", получено " . ($canHandleTest ? "обработка" : "игнорирование") . "\n";
        }
    }
    
    echo "\n=== ТЕСТ ЗАВЕРШЁН ===\n";
    
} catch (Exception $e) {
    echo "✗ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трассировка:\n" . $e->getTraceAsString() . "\n";
}

?>
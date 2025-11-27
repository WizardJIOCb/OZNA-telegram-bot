<?php
/**
 * Тест системы контроля доступа к отчётам по проектам
 * Проверяет, что кнопка "📋 Отчёт по проекту" доступна только пользователям с уровнем 2+
 */

// Подключаем необходимые классы
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;

echo "🔍 ТЕСТ: Контроль доступа к отчётам по проектам\n";
echo "================================================\n\n";

// Создаем мок-объекты для тестирования
class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "📤 Отправка сообщения в чат $chatId:\n";
        echo "💬 Текст: " . strip_tags($text) . "\n";
        
        if ($keyboard) {
            echo "⌨️ Клавиатура:\n";
            foreach ($keyboard as $row) {
                foreach ($row as $button) {
                    echo "  - [{$button['text']}]\n";
                }
            }
        }
        echo "\n";
        return true;
    }
}

class MockAdminService {
    private $userLevels = [];
    
    public function setUserAccessLevel($userId, $level) {
        $this->userLevels[$userId] = $level;
    }
    
    public function getUserAccessLevel($userId) {
        return $this->userLevels[$userId] ?? DatabaseService::ACCESS_LEVEL_GUEST;
    }
    
    public function getAllProjects() {
        return ['TEST001', 'TEST002', 'TEST003'];
    }
}

// Создаем тестовые сервисы
$mockMessageService = new MockMessageService();
$mockAdminService = new MockAdminService();

// Создаем ProjectHandler с мок-сервисами
$projectHandler = new ProjectHandler($mockAdminService, $mockMessageService);

// Тестируем разные уровни доступа
$testUsers = [
    100001 => ['level' => DatabaseService::ACCESS_LEVEL_GUEST, 'name' => 'Гость (уровень 0)'],
    100002 => ['level' => DatabaseService::ACCESS_LEVEL_USER, 'name' => 'Пользователь (уровень 1)'],
    100003 => ['level' => DatabaseService::ACCESS_LEVEL_SUPERVISOR, 'name' => 'Супервайзер (уровень 2)'],
    100004 => ['level' => DatabaseService::ACCESS_LEVEL_ADMIN, 'name' => 'Администратор (уровень 3)'],
    100005 => ['level' => DatabaseService::ACCESS_LEVEL_SUPER_ADMIN, 'name' => 'Суперадминистратор (уровень 4)']
];

foreach ($testUsers as $userId => $userData) {
    echo "👤 Тест для пользователя: {$userData['name']}\n";
    echo "─────────────────────────────────────────────\n";
    
    // Устанавливаем уровень доступа
    $mockAdminService->setUserAccessLevel($userId, $userData['level']);
    
    // Используем Reflection для вызова приватного метода
    $reflectionClass = new ReflectionClass($projectHandler);
    $method = $reflectionClass->getMethod('showProjectsMainMenu');
    $method->setAccessible(true);
    
    // Вызываем метод и проверяем результат
    echo "🔍 Проверка главного меню проектов:\n";
    $method->invoke($projectHandler, "test_chat_$userId", $userId);
    
    echo "\n";
}

echo "✅ Тест завершён!\n\n";

echo "📋 ОЖИДАЕМЫЕ РЕЗУЛЬТАТЫ:\n";
echo "- Уровень 0 (Гость): НЕТ кнопки отчётов\n";
echo "- Уровень 1 (Пользователь): НЕТ кнопки отчётов\n";
echo "- Уровень 2+ (Супервайзер и выше): ЕСТЬ кнопка отчётов\n";
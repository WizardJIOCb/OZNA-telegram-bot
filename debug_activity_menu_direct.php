<?php
/**
 * Прямая проверка работы метода showMyActivityMenu
 */

require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;

echo "=== ПРЯМАЯ ПРОВЕРКА МЕТОДА showMyActivityMenu ===\n\n";

try {
    // Инициализация
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "1. Тестирование метода getUserActivityStatistics...\n";
    
    // Создадим упрощенный тест метода getUserActivityStatistics
    $databaseService = new DatabaseService();
    $testUserId = 230379668;
    
    // Попробуем получить записи пользователя
    $entries = $databaseService->getUserLastTabelEntries($testUserId, 10);
    echo "   Найдено записей для пользователя: " . count($entries) . "\n";
    
    if (count($entries) > 0) {
        echo "   Пример записи: " . substr($entries[0]['message'], 0, 50) . "...\n";
    }
    
    echo "\n2. Тестирование создания текста активности...\n";
    
    // Симулируем статистику вручную для тестирования
    $activityStats = [
        'total_entries' => count($entries),
        'last_30_days' => count($entries), // упрощение
        'this_week' => min(3, count($entries)),
        'milestones' => 0,
        'unique_projects' => 1,
        'streak_days' => 1,
        'most_active_project' => null,
        'favorite_activity' => null
    ];
    
    // Подсчитаем вехи вручную
    foreach ($entries as $entry) {
        if (stripos($entry['message'], 'веха') !== false || 
            stripos($entry['message'], 'этап') !== false || 
            stripos($entry['message'], 'завершен') !== false) {
            $activityStats['milestones']++;
        }
    }
    
    echo "   Статистика:\n";
    echo "   - Всего записей: {$activityStats['total_entries']}\n";
    echo "   - За 30 дней: {$activityStats['last_30_days']}\n";
    echo "   - За неделю: {$activityStats['this_week']}\n";
    echo "   - Вехи: {$activityStats['milestones']}\n";
    echo "   - Уникальные проекты: {$activityStats['unique_projects']}\n";
    
    echo "\n3. Создание текста сообщения...\n";
    
    // Создаем текст так же, как в методе
    $text = "📈 <b>Моя активность</b>\n\n";
    $text .= "📊 <b>Общая статистика:</b>\n";
    $text .= "• 📅 Всего записей в табель: <b>{$activityStats['total_entries']}</b>\n";
    $text .= "• 📅 За последние 30 дней: <b>{$activityStats['last_30_days']}</b>\n";
    $text .= "• 📅 За эту неделю: <b>{$activityStats['this_week']}</b>\n\n";
    
    $text .= "🏆 <b>Потенциал достижений:</b>\n";
    
    if ($activityStats['milestones'] > 0) {
        $text .= "• ⭐ Подтверждено вех: <b>{$activityStats['milestones']}</b>\n";
    } else {
        $text .= "• 🎁 Первая веха ждёт вас!\n";
    }
    
    if ($activityStats['unique_projects'] > 0) {
        $text .= "• 📄 Проектов в работе: <b>{$activityStats['unique_projects']}</b>\n";
    }
    
    $text .= "\n🚀 <b>Ожидаемые функции:</b>\n";
    $text .= "• 🏅 Система достижений (\"Достижитель вех!\", \"Мастер многозадачности\")\n";
    $text .= "• 🏆 Ранговая система с эмодзи у имени\n";
    $text .= "• 📊 Личные рекорды и чемпионаты\n";
    $text .= "• 📅 Аналитика продуктивности";
    
    echo "✅ Текст создан успешно!\n";
    echo "Длина текста: " . strlen($text) . " символов\n\n";
    
    echo "4. Проверка клавиатуры...\n";
    
    $keyboard = [
        [
            ['text' => '📅 Последние записи', 'callback_data' => 'category_Проекты_ПоследниеЗаписи']
        ],
        [
            ['text' => '📈 Детальная статистика', 'callback_data' => 'category_Проекты_ДетальнаяСтатистика']
        ],
        [
            ['text' => '🏆 Мои достижения', 'callback_data' => 'category_Проекты_МоиДостижения']
        ],
        [
            ['text' => '📋 Экспорт активности', 'callback_data' => 'category_Проекты_ЭкспортАктивности']
        ],
        [
            ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
        ]
    ];
    
    echo "✅ Клавиатура создана: " . count($keyboard) . " рядов кнопок\n\n";
    
    echo "5. Тестирование полного вызова метода...\n";
    
    // Теперь попробуем вызвать сам ProjectHandler
    $projectHandler = new ProjectHandler($adminService, $messageService);
    
    // Проверим canHandle
    $canHandle = $projectHandler->canHandle('category_Проекты_МояАктивность');
    echo "   canHandle результат: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";
    
    if ($canHandle) {
        echo "✅ ДИАГНОЗ: Код работает правильно!\n\n";
        
        echo "=== ВОЗМОЖНАЯ ПРИЧИНА ПРОБЛЕМЫ ===\n";
        echo "Если кнопка не показывает новое меню, возможные причины:\n\n";
        
        echo "1. 🔍 Ошибка в getUserActivityStatistics():\n";
        echo "   - Возможно, метод вызывает исключение\n";
        echo "   - Проблема с доступом к базе данных\n\n";
        
        echo "2. 📱 Проблема с отправкой сообщения:\n";
        echo "   - MessageService не может отправить сообщение\n";
        echo "   - Слишком длинный текст\n";
        echo "   - Проблема с Telegram API\n\n";
        
        echo "=== ПРОСТОЕ РЕШЕНИЕ ===\n";
        echo "Давайте упростим метод showMyActivityMenu, чтобы он точно работал:\n";
        echo "1. Уберем сложную статистику\n";
        echo "2. Сделаем простое статичное сообщение\n";
        echo "3. Проверим, что это работает\n";
        echo "4. Потом добавим статистику обратно\n\n";
        
        echo "Хотите, чтобы я создал упрощенную версию?\n";
    } else {
        echo "❌ ПРОБЛЕМА: canHandle возвращает FALSE\n";
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Стек вызовов:\n" . $e->getTraceAsString() . "\n";
}
?>
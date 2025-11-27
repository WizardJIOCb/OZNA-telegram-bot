<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;

// Bot credentials
$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

// Initialize bot
$telegram = new Telegram($bot_api_key, $bot_username);

// MySQL credentials (same as in hook.php)
$mysql_credentials = [
    'host'     => '82.146.42.213',
    'port'     => 3306,
    'user'     => 'WizardJIOCb',
    'password' => '7qQ#28051961',
    'database' => 'tg_bot',
];
$telegram->enableMySql($mysql_credentials);

echo "🚀 ОТПРАВКА РУССКОГО ОБЪЯВЛЕНИЯ!\n\n";

// Russian announcement message
$message = "🎉 <b>НОВЫЙ РЕЛИЗ OZNA BOT!</b>\n\n";
$message .= "📊 <b>Добавлена система \"Моя Активность\"</b>\n\n";
$message .= "✨ <b>Что нового:</b>\n";
$message .= "📈 Персональная статистика работы\n";
$message .= "🏆 Система достижений (как в играх!)\n";
$message .= "📋 Детальная аналитика ваших записей\n";
$message .= "⭐ Прогресс по проектам и вехам\n";
$message .= "📊 Экспорт данных в различных форматах\n\n";
$message .= "🎮 <b>Геймификация:</b>\n";
$message .= "🏅 Зарабатывайте достижения за активность!\n";
$message .= "📊 Следите за своим прогрессом\n";
$message .= "🎯 Достигайте новых целей каждый день\n\n";
$message .= "🚀 <b>Как попробовать:</b>\n";
$message .= "1️⃣ Перейдите в раздел \"Проекты\"\n";
$message .= "2️⃣ Нажмите \"Моя Активность\"\n";
$message .= "3️⃣ Изучайте свою статистику и достижения!\n\n";
$message .= "💪 Успешной работы с новыми возможностями!";

// Get all users from database using the Telegram Bot's built-in database
try {
    // Use Telegram's database connection to get users
    $result = \Longman\TelegramBot\DB::selectUsers();
    
    if (!$result->isOk()) {
        throw new Exception('Failed to get users from Telegram database');
    }
    
    $users = [];
    while ($user = $result->fetchArray(MYSQLI_ASSOC)) {
        $users[] = $user;
    }
    
    echo "✅ Найдено пользователей в базе: " . count($users) . "\n";
    echo "🚀 Начинаю отправку...\n\n";
    
    $sent = 0;
    $errors = 0;
    
    foreach ($users as $user) {
        $chatId = $user['chat_id'];
        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        if (empty($name)) $name = 'Пользователь';
        
        try {
            $result = Request::sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
            
            if ($result->isOk()) {
                $sent++;
                echo "✅ Отправлено: {$name} (ID: {$chatId})\n";
            } else {
                $errors++;
                echo "❌ Ошибка: {$name} (ID: {$chatId}) - " . $result->getDescription() . "\n";
            }
        } catch (Exception $e) {
            $errors++;
            echo "❌ Исключение для {$name}: " . $e->getMessage() . "\n";
        }
        
        // Small delay to avoid rate limiting
        usleep(100000); // 0.1 second
    }
    
    echo "\n🎉 ОТПРАВКА ЗАВЕРШЕНА!\n";
    echo "✅ Успешно: {$sent}\n";
    echo "❌ Ошибок: {$errors}\n";
    echo "📊 Всего: " . count($users) . "\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка базы данных: " . $e->getMessage() . "\n";
}
?>
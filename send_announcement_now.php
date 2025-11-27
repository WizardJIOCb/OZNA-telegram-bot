<?php
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== ОТПРАВКА РУССКОГО ОБЪЯВЛЕНИЯ ===\n\n";

// Initialize services
$messageService = new MessageService();
$databaseService = new DatabaseService();

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

// Get users with access level 1+
$allUsers = $databaseService->getAllUsers();
$eligibleUsers = [];

foreach ($allUsers as $user) {
    $userId = $user['user_id'];
    $userData = $user['data'];
    $accessLevel = $userData['access_level'] ?? 0;
    
    if ($accessLevel >= 1) {
        $eligibleUsers[] = [
            'user_id' => $userId,
            'fio' => $userData['fio'] ?? 'Неизвестный пользователь',
            'access_level' => $accessLevel
        ];
    }
}

echo "✅ Найдено пользователей для отправки: " . count($eligibleUsers) . "\n";
echo "🚀 Начинаю отправку русского объявления...\n\n";

$sent = 0;
$errors = 0;

foreach ($eligibleUsers as $user) {
    $userId = $user['user_id'];
    $fio = $user['fio'];
    
    try {
        $result = $messageService->sendMessage($userId, $message);
        
        if ($result && $result->isOk()) {
            $sent++;
            echo "✅ Отправлено: {$fio} (ID: {$userId})\n";
        } else {
            $errors++;
            echo "❌ Ошибка отправки: {$fio} (ID: {$userId})\n";
        }
    } catch (Exception $e) {
        $errors++;
        echo "❌ Исключение для {$fio}: " . $e->getMessage() . "\n";
    }
    
    // Small delay to avoid rate limiting
    usleep(100000); // 0.1 second
}

echo "\n=== РЕЗУЛЬТАТ РАССЫЛКИ ===\n";
echo "📤 Успешно отправлено: {$sent}\n";
echo "❌ Ошибок: {$errors}\n";
echo "📊 Общий охват: " . count($eligibleUsers) . " пользователей\n";
echo "\n🎉 Русское объявление о новом релизе отправлено!\n";
?>
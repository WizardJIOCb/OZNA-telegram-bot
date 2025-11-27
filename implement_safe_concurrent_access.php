<?php
/**
 * Тест и внедрение безопасного одновременного доступа
 * Решение проблемы с потерей данных при одновременном редактировании
 */

echo "=== РЕШЕНИЕ ПРОБЛЕМЫ ОДНОВРЕМЕННОГО ДОСТУПА ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ThreadSafeDatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ThreadSafeDatabaseService;

echo "1. ТЕСТИРОВАНИЕ БЕЗОПАСНОГО СЕРВИСА:\n";

$safeService = new ThreadSafeDatabaseService();
$safeService->setDebugMode(true);

// Тестовые данные
$testUserId = 999999999; // Тестовый пользователь
$testProject = "ТЕСТ_ПРОЕКТ_БЕЗОПАСНОСТЬ";

echo "   Тестирование добавления проекта безопасным методом...\n";

$projectSettings = [
    'group_id' => '-1001234567890',
    'chat_id' => 'personal',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'notification_types' => ['daily_reports'],
    'notification_mode' => 'standard'
];

$result = $safeService->addProjectToControlListSafe($testUserId, $testProject, $projectSettings);
echo "   Результат добавления: " . ($result ? "УСПЕХ" : "ОШИБКА") . "\n";

// Попытка добавить тот же проект (должна завершиться неудачей)
$result2 = $safeService->addProjectToControlListSafe($testUserId, $testProject, $projectSettings);
echo "   Повторное добавление: " . ($result2 ? "УСПЕХ (не должно быть!)" : "ОШИБКА (ожидается)") . "\n";

echo "\n2. ТЕСТИРОВАНИЕ ОБНОВЛЕНИЯ НАСТРОЕК:\n";

$newSettings = [
    'group_id' => '-1001234567891',
    'notification_mode' => 'immediate'
];

$updateResult = $safeService->updateProjectSettingsSafe($testUserId, $testProject, $newSettings);
echo "   Обновление настроек: " . ($updateResult ? "УСПЕХ" : "ОШИБКА") . "\n";

echo "\n3. ТЕСТИРОВАНИЕ УДАЛЕНИЯ:\n";

$removeResult = $safeService->removeProjectFromControlListSafe($testUserId, $testProject);
echo "   Удаление проекта: " . ($removeResult ? "УСПЕХ" : "ОШИБКА") . "\n";

echo "\n4. СОЗДАНИЕ ПАТЧА ДЛЯ PROJECTHANDLER:\n";

$patchContent = '
/*
 * ПАТЧ ДЛЯ БЕЗОПАСНОГО ОДНОВРЕМЕННОГО ДОСТУПА
 * Замените следующие методы в ProjectHandler на безопасные версии
 */

// В конструкторе ProjectHandler добавьте:
private ThreadSafeDatabaseService $safeDatabaseService;

public function __construct(AdminService $adminService, MessageService $messageService)
{
    // ... существующий код ...
    $this->safeDatabaseService = new ThreadSafeDatabaseService();
}

// ЗАМЕНИТЕ МЕТОД addProjectToControlList НА:
private function addProjectToControlList(string $chatId, int $userId, string $projectCode): ServerResponse
{
    $projectSettings = [
        "group_id" => "",
        "chat_id" => "",
        "created_at" => date("Y-m-d H:i:s"),
        "updated_at" => date("Y-m-d H:i:s")
    ];

    $success = $this->safeDatabaseService->addProjectToControlListSafe($userId, $projectCode, $projectSettings);

    if ($success) {
        $text = "✅ <b>Проект добавлен</b>\n\n<b>{$projectCode}</b> добавлен на контроль.\n\n📝 Не забудьте настроить уведомления!";
    } else {
        $text = "ℹ️ <b>Проект уже на контроле</b>\n\n<b>{$projectCode}</b> уже в списке.";
    }

    $keyboard = [[ ["text" => "⬅️ Назад", "callback_data" => "category_Проекты_ПроектыНаКонтроле"] ]];
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}

// ЗАМЕНИТЕ МЕТОД removeProjectFromControlList НА:
private function removeProjectFromControlList(string $chatId, int $userId, string $projectCode): ServerResponse
{
    $success = $this->safeDatabaseService->removeProjectFromControlListSafe($userId, $projectCode);

    if ($success) {
        $text = "✅ <b>Проект удалён</b>\n\n<b>{$projectCode}</b> удалён с контроля.";
    } else {
        $text = "ℹ️ <b>Проект не найден</b>\n\n<b>{$projectCode}</b> не найден в списке.";
    }

    $keyboard = [[ ["text" => "⬅️ Назад", "callback_data" => "category_Проекты_ПроектыНаКонтроле"] ]];
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}

// ЗАМЕНИТЕ МЕТОД setProjectGroup НА:
private function setProjectGroup(string $chatId, int $userId, string $projectCode, string $groupId): ServerResponse
{
    $newSettings = ["group_id" => $groupId];
    $success = $this->safeDatabaseService->updateProjectSettingsSafe($userId, $projectCode, $newSettings);

    $text = $success ? 
        "✅ <b>Группа настроена</b>\n\nГруппа для проекта {$projectCode} успешно настроена." :
        "❌ <b>Ошибка</b>\n\nНе удалось настроить группу.";

    $keyboard = [[ ["text" => "⬅️ Назад", "callback_data" => "proj_ctrl_set_{$projectCode}"] ]];
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}

// ЗАМЕНИТЕ МЕТОД setProjectChat НА:
private function setProjectChat(string $chatId, int $userId, string $projectCode, string $chatType): ServerResponse
{
    $targetChatId = ($chatType === "personal") ? "personal" : $chatType;
    $newSettings = ["chat_id" => $targetChatId];
    $success = $this->safeDatabaseService->updateProjectSettingsSafe($userId, $projectCode, $newSettings);

    $text = $success ?
        "✅ <b>Чат настроен</b>\n\nЧат для проекта {$projectCode} успешно настроен." :
        "❌ <b>Ошибка</b>\n\nНе удалось настроить чат.";

    $keyboard = [[ ["text" => "⬅️ Назад", "callback_data" => "proj_ctrl_set_{$projectCode}"] ]];
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}

// ЗАМЕНИТЕ МЕТОД toggleNotificationType НА:
private function toggleNotificationType(string $chatId, int $userId, string $projectCode, string $notificationType): ServerResponse
{
    // Получаем текущие настройки
    $userData = $this->databaseService->getUserData($userId);
    $controlProjects = $userData["control_projects"] ?? [];
    
    if (!isset($controlProjects[$projectCode])) {
        $text = "❌ Проект не найден";
        $keyboard = [[ ["text" => "⬅️ Назад", "callback_data" => "category_Проекты_ПроектыНаКонтроле"] ]];
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    $currentTypes = $controlProjects[$projectCode]["notification_types"] ?? [];
    
    if (in_array($notificationType, $currentTypes)) {
        $currentTypes = array_diff($currentTypes, [$notificationType]);
    } else {
        $currentTypes[] = $notificationType;
    }

    $newSettings = ["notification_types" => array_values($currentTypes)];
    $success = $this->safeDatabaseService->updateProjectSettingsSafe($userId, $projectCode, $newSettings);

    if ($success) {
        return $this->showNotificationTypesConfiguration($chatId, $userId, $projectCode);
    } else {
        $text = "❌ Ошибка сохранения настроек";
        $keyboard = [[ ["text" => "⬅️ Назад", "callback_data" => "proj_ctrl_set_{$projectCode}"] ]];
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
}

// ЗАМЕНИТЕ МЕТОД setNotificationMode НА:
private function setNotificationMode(string $chatId, int $userId, string $projectCode, string $mode): ServerResponse
{
    $newSettings = ["notification_mode" => $mode];
    $success = $this->safeDatabaseService->updateProjectSettingsSafe($userId, $projectCode, $newSettings);

    $text = $success ?
        "✅ <b>Режим уведомлений сохранён</b>\n\nРежим уведомлений для проекта {$projectCode} успешно изменён." :
        "❌ <b>Ошибка сохранения</b>\n\nНе удалось сохранить режим уведомлений.";

    $keyboard = [[ ["text" => "⬅️ Назад", "callback_data" => "proj_ctrl_set_{$projectCode}"] ]];
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}
';

file_put_contents(__DIR__ . '/ProjectHandler_SafetyPatch.txt', $patchContent);

echo "   ✅ Создан файл ProjectHandler_SafetyPatch.txt с инструкциями\n\n";

echo "5. ИНСТРУКЦИИ ПО ВНЕДРЕНИЮ:\n";
echo "   1. Добавьте require_once для ThreadSafeDatabaseService в начало ProjectHandler.php\n";
echo "   2. Добавьте свойство \$safeDatabaseService в класс ProjectHandler\n";
echo "   3. Замените небезопасные методы на версии из патча\n";
echo "   4. Протестируйте функциональность\n";
echo "   5. Мониторьте логи на предмет ошибок блокировок\n\n";

echo "6. ПРЕИМУЩЕСТВА РЕШЕНИЯ:\n";
echo "   ✅ Предотвращение потери данных при одновременном доступе\n";
echo "   ✅ Использование транзакций для атомарности\n";
echo "   ✅ Блокировки SELECT ... FOR UPDATE\n";
echo "   ✅ Автоматический откат при ошибках\n";
echo "   ✅ Детальное логирование операций\n";
echo "   ✅ Совместимость с существующим кодом\n\n";

echo "7. МОНИТОРИНГ:\n";
echo "   Проверяйте логи error.log на наличие записей:\n";
echo "   - ThreadSafeDatabaseService: Starting transaction\n";
echo "   - ThreadSafeDatabaseService: Transaction committed\n";
echo "   - ThreadSafeDatabaseService: Successfully added/updated\n\n";

echo "=== РЕШЕНИЕ ГОТОВО К ВНЕДРЕНИЮ ===\n";
?>
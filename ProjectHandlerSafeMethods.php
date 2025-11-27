<?php
/**
 * Исправления для безопасного одновременного доступа в ProjectHandler
 * Заменяет небезопасные операции на thread-safe версии
 */

// Подключаем новый безопасный сервис
require_once __DIR__ . '/ThreadSafeDatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\ThreadSafeDatabaseService;

/**
 * Mixin для добавления безопасных методов в ProjectHandler
 */
trait ThreadSafeProjectHandlerMethods
{
    private ?ThreadSafeDatabaseService $threadSafeDatabaseService = null;

    /**
     * Получить экземпляр thread-safe database service
     */
    private function getThreadSafeDatabaseService(): ThreadSafeDatabaseService
    {
        if ($this->threadSafeDatabaseService === null) {
            $this->threadSafeDatabaseService = new ThreadSafeDatabaseService();
            $this->threadSafeDatabaseService->setDebugMode(true);
        }
        return $this->threadSafeDatabaseService;
    }

    /**
     * Thread-safe версия addProjectToControlList
     */
    private function addProjectToControlListSafe(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $projectSettings = [
            'group_id' => '',
            'chat_id' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $success = $this->getThreadSafeDatabaseService()->addProjectToControlListSafe($userId, $projectCode, $projectSettings);

        if ($success) {
            $text = "✅ <b>Проект добавлен</b>

<b>{$projectCode}</b> успешно добавлен на контроль.

📝 Не забудьте настроить уведомления!";
        } else {
            $text = "ℹ️ <b>Проект уже на контроле</b>

<b>{$projectCode}</b> уже в списке контроля или произошла ошибка при добавлении.";
        }

        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']
        ]];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Thread-safe версия removeProjectFromControlList
     */
    private function removeProjectFromControlListSafe(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $success = $this->getThreadSafeDatabaseService()->removeProjectFromControlListSafe($userId, $projectCode);

        if ($success) {
            $text = "✅ <b>Проект удалён</b>

<b>{$projectCode}</b> успешно удалён с контроля.";
        } else {
            $text = "ℹ️ <b>Проект не найден</b>

<b>{$projectCode}</b> не найден в списке контроля.";
        }

        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']
        ]];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Thread-safe версия setProjectGroup
     */
    private function setProjectGroupSafe(string $chatId, int $userId, string $projectCode, string $groupId): ServerResponse
    {
        $newSettings = ['group_id' => $groupId];
        $success = $this->getThreadSafeDatabaseService()->updateProjectSettingsSafe($userId, $projectCode, $newSettings);

        if ($success) {
            $text = "✅ <b>Группа настроена</b>

Группа для проекта <b>{$projectCode}</b> успешно настроена.

ID группы: <code>{$groupId}</code>";
        } else {
            $text = "❌ <b>Ошибка настройки</b>

Не удалось настроить группу для проекта <b>{$projectCode}</b>.";
        }

        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Thread-safe версия setProjectChat
     */
    private function setProjectChatSafe(string $chatId, int $userId, string $projectCode, string $chatType): ServerResponse
    {
        $targetChatId = ($chatType === 'personal') ? 'personal' : $chatType;
        $newSettings = ['chat_id' => $targetChatId];
        $success = $this->getThreadSafeDatabaseService()->updateProjectSettingsSafe($userId, $projectCode, $newSettings);

        if ($success) {
            $text = "✅ <b>Чат настроен</b>

Чат для проекта <b>{$projectCode}</b> успешно настроен.";
        } else {
            $text = "❌ <b>Ошибка настройки</b>

Не удалось настроить чат для проекта <b>{$projectCode}</b>.";
        }

        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Thread-safe версия toggleNotificationType
     */
    private function toggleNotificationTypeSafe(string $chatId, int $userId, string $projectCode, string $notificationType): ServerResponse
    {
        // Получаем текущие настройки
        $userData = $this->databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!isset($controlProjects[$projectCode])) {
            $text = "❌ Проект не найден";
            $keyboard = [[ ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле'] ]];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }

        $currentTypes = $controlProjects[$projectCode]['notification_types'] ?? [];
        
        if (in_array($notificationType, $currentTypes)) {
            $currentTypes = array_diff($currentTypes, [$notificationType]);
        } else {
            $currentTypes[] = $notificationType;
        }

        $newSettings = ['notification_types' => array_values($currentTypes)];
        $success = $this->getThreadSafeDatabaseService()->updateProjectSettingsSafe($userId, $projectCode, $newSettings);

        if ($success) {
            // Возвращаемся к экрану настройки типов уведомлений
            return $this->showNotificationTypesConfiguration($chatId, $userId, $projectCode);
        } else {
            $text = "❌ Ошибка сохранения настроек";
            $keyboard = [[ ['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"] ]];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
    }

    /**
     * Thread-safe версия setNotificationMode
     */
    private function setNotificationModeSafe(string $chatId, int $userId, string $projectCode, string $mode): ServerResponse
    {
        $newSettings = ['notification_mode' => $mode];
        $success = $this->getThreadSafeDatabaseService()->updateProjectSettingsSafe($userId, $projectCode, $newSettings);

        if ($success) {
            $text = "✅ <b>Режим уведомлений сохранён</b>

Режим уведомлений для проекта <b>{$projectCode}</b> успешно изменён на: <b>{$mode}</b>";
        } else {
            $text = "❌ <b>Ошибка сохранения</b>

Не удалось сохранить режим уведомлений для проекта <b>{$projectCode}</b>.";
        }

        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
}

echo "=== СОЗДАН БЕЗОПАСНЫЙ TRAIT ДЛЯ PROJECTHANDLER ===\n";
echo "Trait ThreadSafeProjectHandlerMethods содержит:\n";
echo "✅ addProjectToControlListSafe() - безопасное добавление проекта\n";
echo "✅ removeProjectFromControlListSafe() - безопасное удаление проекта\n";
echo "✅ setProjectGroupSafe() - безопасная настройка группы\n";
echo "✅ setProjectChatSafe() - безопасная настройка чата\n";
echo "✅ toggleNotificationTypeSafe() - безопасное переключение типов\n";
echo "✅ setNotificationModeSafe() - безопасная настройка режима\n";
echo "\nДля использования нужно:\n";
echo "1. Добавить 'use ThreadSafeProjectHandlerMethods;' в класс ProjectHandler\n";
echo "2. Заменить небезопасные методы на безопасные версии\n";
?>
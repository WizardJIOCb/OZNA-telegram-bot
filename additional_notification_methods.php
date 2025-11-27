<?php
/**
 * Additional notification management methods for ProjectHandler
 * Add these methods to the ProjectHandler class
 */

/**
 * Save group configuration for project notifications
 */
private function saveGroupConfiguration(string $chatId, int $userId, string $projectCode, string $groupId): ServerResponse
{
    $userData = $this->databaseService->getUserData($userId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (isset($controlProjects[$projectCode])) {
        $controlProjects[$projectCode]['group_id'] = $groupId;
        $userData['control_projects'] = $controlProjects;
        $this->databaseService->updateUserData($userId, $userData);
        
        $text = "✅ <b>Группа настроена</b>

Группа для проекта <b>{$projectCode}</b> успешно настроена.

📝 Теперь можете настроить другие параметры уведомлений.";
    } else {
        $text = "❌ <b>Ошибка</b>

Проект не найден в списке контроля.";
    }
    
    $keyboard = [[
        ['text' => '⬅️ Назад к настройкам', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
    ]];
    
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}

/**
 * Save chat configuration for project notifications
 */
private function saveChatConfiguration(string $chatId, int $userId, string $projectCode, string $chatType): ServerResponse
{
    $userData = $this->databaseService->getUserData($userId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (isset($controlProjects[$projectCode])) {
        $controlProjects[$projectCode]['chat_id'] = $chatType;
        $userData['control_projects'] = $controlProjects;
        $this->databaseService->updateUserData($userId, $userData);
        
        $chatName = ($chatType === 'personal') ? 'личный чат' : 'чат ID: ' . $chatType;
        $text = "✅ <b>Чат настроен</b>

Чат для проекта <b>{$projectCode}</b> настроен: {$chatName}

📝 Теперь можете настроить другие параметры уведомлений.";
    } else {
        $text = "❌ <b>Ошибка</b>

Проект не найден в списке контроля.";
    }
    
    $keyboard = [[
        ['text' => '⬅️ Назад к настройкам', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
    ]];
    
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}

/**
 * Toggle notification type for project
 */
private function toggleNotificationType(string $chatId, int $userId, string $projectCode, string $notificationType): ServerResponse
{
    $userData = $this->databaseService->getUserData($userId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (isset($controlProjects[$projectCode])) {
        $currentTypes = $controlProjects[$projectCode]['notification_types'] ?? [];
        
        if (in_array($notificationType, $currentTypes)) {
            // Remove type
            $currentTypes = array_filter($currentTypes, function($type) use ($notificationType) {
                return $type !== $notificationType;
            });
        } else {
            // Add type
            $currentTypes[] = $notificationType;
        }
        
        $controlProjects[$projectCode]['notification_types'] = array_values($currentTypes);
        $userData['control_projects'] = $controlProjects;
        $this->databaseService->updateUserData($userId, $userData);
    }
    
    // Return to types configuration menu to show updated state
    return $this->showNotificationTypesConfiguration($chatId, $userId, $projectCode);
}

/**
 * Save notification mode for project
 */
private function saveNotificationMode(string $chatId, int $userId, string $projectCode, string $mode): ServerResponse
{
    $userData = $this->databaseService->getUserData($userId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (isset($controlProjects[$projectCode])) {
        $controlProjects[$projectCode]['notification_mode'] = $mode;
        $userData['control_projects'] = $controlProjects;
        $this->databaseService->updateUserData($userId, $userData);
        
        $modeNames = [
            'standard' => 'Стандартный',
            'immediate' => 'Мгновенный',
            'summary' => 'Сводка',
            'critical_only' => 'Только критические',
            'disabled' => 'Отключены'
        ];
        
        $modeName = $modeNames[$mode] ?? $mode;
        $text = "✅ <b>Режим уведомлений изменён</b>

Для проекта <b>{$projectCode}</b> установлен режим: <b>{$modeName}</b>

📝 Настройка сохранена.";
    } else {
        $text = "❌ <b>Ошибка</b>

Проект не найден в списке контроля.";
    }
    
    $keyboard = [[
        ['text' => '⬅️ Назад к настройкам', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
    ]];
    
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}
?>
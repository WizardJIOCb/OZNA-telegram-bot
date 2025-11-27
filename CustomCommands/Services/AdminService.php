<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Admin service for managing administrative functions
 */
class AdminService
{
    private MessageService $messageService;
    private DatabaseService $databaseService;
    private string $accessRequestsFile;
    private int $nextRequestId = 1;

    // Текущий список проектов (пока статический, потом можно в базу)
    private array $projects = [
        'ОИ834', 'ОИ651', 'ОИ696', 'ОИ795',
        'ОИ810', 'ОИ768', 'ОИ837', 'ОИ562',
        'ОИ788', 'ОИ860', 'ОИ862',
        'БКНС115', 'БКНС120'
    ];

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        $this->databaseService = new DatabaseService();
        $this->accessRequestsFile = __DIR__ . '/../../data/access_requests.json';
        
        // Initialize next request ID
        $requests = $this->getAllAccessRequests();
        if (!empty($requests)) {
            $maxId = 0;
            foreach ($requests as $request) {
                if (isset($request['id']) && $request['id'] > $maxId) {
                    $maxId = $request['id'];
                }
            }
            $this->nextRequestId = $maxId + 1;
        }
        
        // Загружаем проекты из файла при создании сервиса
        $this->loadProjectsFromFile();
    }

    /**
     * Проверяет является ли пользователь супер-админом
     */
    public function isSuperAdmin(int $userId): bool
    {
        // For now, we'll use a simple approach to check super admin status
        // In a real implementation, you might want to store this in a file or database
        $superAdmins = [5261465966]; // Example super admin ID
        return in_array($userId, $superAdmins);
    }

    /**
     * Получить список супер-админов
     */
    public function getSuperAdmins(): array
    {
        // For now, we'll use a simple approach
        // In a real implementation, you might want to store this in a file or database
        return [5261465966]; // Example super admin ID
    }

    /**
     * Проверяет уровень доступа пользователя
     */
    public function getUserAccessLevel(int $userId): int
    {
        // Сначала проверяем, является ли пользователь супер-админом
        if ($this->isSuperAdmin($userId)) {
            return 4; // ACCESS_LEVEL_SUPER_ADMIN
        }
        
        // Получаем уровень доступа из базы данных
        return $this->databaseService->getUserAccessLevel($userId);
    }

    /**
     * Проверяет, имеет ли пользователь определенный уровень доступа или выше
     */
    public function hasAccessLevel(int $userId, int $requiredLevel): bool
    {
        return $this->getUserAccessLevel($userId) >= $requiredLevel;
    }

    /**
     * Получить текстовое описание уровня доступа
     */
    public function getAccessLevelName(int $accessLevel): string
    {
        switch ($accessLevel) {
            case 0:
                return "Гость";
            case 1:
                return "Пользователь";
            case 2:
                return "Супервайзер";
            case 3:
                return "Администратор";
            case 4:
                return "Суперадминистратор";
            default:
                return "Неизвестный уровень";
        }
    }

    /**
     * Обновляет уровень доступа пользователя
     */
    public function updateUserAccessLevel(int $userId, int $newLevel): bool
    {
        // Обновляем уровень доступа в базе данных
        return $this->databaseService->updateUserAccessLevel($userId, $newLevel);
    }

    /**
     * Получить ФИО пользователя из профиля
     */
    public function getUserFio(int $userId): string
    {
        $userData = $this->databaseService->getUserData($userId);
        return $userData['fio'] ?? '';
    }

    /**
     * Получить данные пользователя
     */
    public function getUserData(int $userId): array
    {
        return $this->databaseService->getUserData($userId);
    }

    /**
     * Установить ФИО пользователя
     */
    public function setUserFio(int $userId, string $fio): bool
    {
        // Устанавливаем ФИО в базе данных
        return $this->databaseService->setUserFio($userId, $fio);
    }

    /**
     * Установить функциональное направление пользователя
     */
    public function setUserFunctionalDirection(int $userId, string $direction): bool
    {
        // Устанавливаем функциональное направление в базе данных
        return $this->databaseService->setUserFunctionalDirection($userId, $direction);
    }

    /**
     * Добавить пользователя как администратора
     */
    public function addAdministrator(int $userId): bool
    {
        // Устанавливаем уровень доступа администратора (уровень 3)
        return $this->databaseService->updateUserAccessLevel($userId, 3);
    }

    /**
     * Получить всех администраторов (пользователей с уровнем доступа администратора)
     */
    public function getAllAdministrators(): array
    {
        // Получаем всех пользователей из базы данных
        $allUsers = $this->databaseService->getAllUsers();
        $administrators = [];
        
        foreach ($allUsers as $user) {
            $accessLevel = $user['data']['access_level'] ?? 0;
            if ($accessLevel >= 3) { // Уровень администратора или выше
                $administrators[] = $user;
            }
        }
        
        return $administrators;
    }

    /**
     * Получить всех пользователей с любым уровнем доступа выше гостя
     */
    public function getAllUsersWithAccess(): array
    {
        // Получаем всех пользователей из базы данных
        $allUsers = $this->databaseService->getAllUsers();
        $usersWithAccess = [];
        
        foreach ($allUsers as $user) {
            $accessLevel = $user['data']['access_level'] ?? 0;
            if ($accessLevel > 0) { // Уровень выше гостя
                $usersWithAccess[] = $user;
            }
        }
        
        return $usersWithAccess;
    }

    /**
     * Очистить список администраторов (сбросить уровень доступа всех администраторов до уровня пользователя)
     */
    public function clearAdministrators(): bool
    {
        // Получаем всех администраторов
        $administrators = $this->getAllAdministrators();
        
        // Сбрасываем уровень доступа каждого администратора до уровня пользователя (1)
        foreach ($administrators as $admin) {
            $userId = $admin['user_id'];
            $this->databaseService->updateUserAccessLevel($userId, 1); // ACCESS_LEVEL_USER
        }
        
        return true;
    }
    
    /**
     * Get the database service instance
     */
    public function getDatabaseService(): DatabaseService
    {
        return $this->databaseService;
    }

    /**
     * Получить список всех проектов
     */
    public function getAllProjects(): array
    {
        return $this->projects;
    }

    /**
     * Добавить новый проект
     */
    public function addProject(string $projectCode): bool
    {
        $projectCode = trim(strtoupper($projectCode));
        
        if (empty($projectCode)) {
            return false;
        }
        
        if (in_array($projectCode, $this->projects)) {
            return false; // Проект уже существует
        }
        
        $this->projects[] = $projectCode;
        
        // Сохранить в файл
        $this->saveProjectsToFile();
        
        return true;
    }

    /**
     * Удалить проект
     */
    public function deleteProject(string $projectCode): bool
    {
        $key = array_search($projectCode, $this->projects);
        
        if ($key === false) {
            return false; // Проект не найден
        }
        
        unset($this->projects[$key]);
        $this->projects = array_values($this->projects); // Переиндексация
        
        // Сохранить в файл
        $this->saveProjectsToFile();
        
        return true;
    }

    /**
     * Создать клавиатуру для запроса доступа
     */
    public function createAccessRequestKeyboard(): array
    {
        return [
            [
                ['text' => '📝 Запросить доступ', 'callback_data' => 'category_Админка_ЗапросДоступ']
            ]
        ];
    }

    /**
     * Создать главную клавиатуру админки
     */
    public function createAdminMainKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Управление проектами', 'callback_data' => 'category_Админка_Проекты']
            ],
            [
                ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
            ]
        ];
    }

    /**
     * Создать клавиатуру управления проектами
     */
    public function createProjectManagementKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Просмотреть все проекты', 'callback_data' => 'category_Админка_Проекты_Список']
            ],
            [
                ['text' => '➕ Добавить проект', 'callback_data' => 'category_Админка_Проекты_Добавить']
            ],
            [
                ['text' => '🗑️ Удалить проект', 'callback_data' => 'category_Админка_Проекты_Удалить']
            ],
            [
                ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
            ]
        ];
    }

    /**
     * Создать клавиатуру для возврата к управлению проектами
     */
    public function createBackToProjectsKeyboard(): array
    {
        return [
            [
                ['text' => '⬅️ Назад к управлению проектами', 'callback_data' => 'category_Админка_Проекты']
            ]
        ];
    }

    /**
     * Создать клавиатуру для удаления проектов
     */
    public function createDeleteProjectKeyboard(array $projects): array
    {
        $keyboard = [];
        
        // Создаем кнопки по 3 в ряд
        $row = [];
        foreach ($projects as $index => $project) {
            // Используем сокращенный callback_data чтобы не превысить лимит Telegram (64 байта)
            $callbackData = "category_Адм_УдПр_{$project}";
            
            $row[] = ['text' => $project, 'callback_data' => $callbackData];
            
            // Каждые 3 кнопки создаем новый ряд
            if (count($row) == 3 || $index == count($projects) - 1) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        // Кнопка назад
        $keyboard[] = [
            ['text' => '⬅️ Назад к управлению проектами', 'callback_data' => 'category_Админка_Проекты']
        ];
        
        return $keyboard;
    }

    /**
     * Отправить заявку на доступ супер-админам
     */
    public function sendAccessRequest(int $userId, string $userName, string $chatId): bool
    {
        // Get user data from database
        $userData = $this->databaseService->getUserData($userId);
        $userFio = $userData['fio'] ?? '';
        $userFunctionalDirection = $userData['functional_direction'] ?? '';
        
        // Check if required profile information is provided
        if (empty($userFio) || empty($userFunctionalDirection) || $userFunctionalDirection === 'Не указано') {
            // Log the issue for debugging
            error_log("AdminService: User {$userId} cannot submit access request - missing required profile information. FIO: '{$userFio}', Functional Direction: '{$userFunctionalDirection}'");
            return false;
        }
        
        // Use FIO as the primary display name if available
        $displayName = !empty($userFio) ? $userFio : $userName;
        
        // Create the request data
        $requestData = [
            'user_id' => $userId,
            'user_name' => $displayName,
            'user_telegram_name' => $userName,
            'user_fio' => $userFio,
            'chat_id' => $chatId,
            'timestamp' => time()
        ];
        
        // Use database service to add access request
        $result = $this->databaseService->addAccessRequest($requestData);
        
        if ($result) {
            $message = "🔔 <b>Новая заявка на доступ к боту</b>\n\n";
            $message .= "👤 Пользователь: {$displayName}\n";
            $message .= "🆔 User ID: {$userId}\n";
            $message .= "💬 Chat ID: {$chatId}\n";
            $message .= "🏢 Функциональное направление: {$userFunctionalDirection}\n\n";
            $message .= "Для предоставления доступа добавьте ID {$userId} в список пользователей.";

            // Получаем актуальное количество заявок для отображения в уведомлении
            $pendingCount = count($this->databaseService->getPendingAccessRequests());
            $countMessage = "\n\n📢 Всего заявок в ожидании: {$pendingCount}";
            $message .= $countMessage;

            // Создаем клавиатуру с кнопкой быстрого доступа к заявкам
            $keyboard = [
                [
                    ['text' => '👥 Посмотреть заявки', 'callback_data' => 'category_Админка_Заявки']
                ]
            ];

            // Отправляем всем супер-админам
            foreach ($this->getSuperAdmins() as $adminId) {
                try {
                    $this->messageService->sendMessage($adminId, $message, $keyboard);
                } catch (\Exception $e) {
                    error_log("AdminService: Error sending message to super admin {$adminId}: " . $e->getMessage());
                    // Continue with other admins even if one fails
                }
            }
            
            // Отправляем всем администраторам (уровень доступа 3 и выше)
            $administrators = $this->getAllAdministrators();
            foreach ($administrators as $admin) {
                $adminId = $admin['user_id'];
                // Пропускаем супер-админов, чтобы не дублировать уведомления
                if (!in_array($adminId, $this->getSuperAdmins())) {
                    try {
                        $this->messageService->sendMessage($adminId, $message, $keyboard);
                    } catch (\Exception $e) {
                        error_log("AdminService: Error sending message to admin {$adminId}: " . $e->getMessage());
                        // Continue with other admins even if one fails
                    }
                }
            }
        } else {
            error_log("AdminService: Failed to add access request to database for user {$userId}");
        }

        return $result;
    }

    /**
     * Получить список всех заявок на доступ
     */
    public function getAllAccessRequests(): array
    {
        try {
            // First try to get from database
            return $this->databaseService->getPendingAccessRequests();
        } catch (\Exception $e) {
            error_log("AdminService: Error getting access requests from database: " . $e->getMessage());
            
            // Fallback to file-based access requests
            try {
                if (file_exists($this->accessRequestsFile)) {
                    $jsonData = file_get_contents($this->accessRequestsFile);
                    if ($jsonData !== false) {
                        $requests = json_decode($jsonData, true);
                        if (is_array($requests)) {
                            // Filter only pending requests (all in the file are considered pending)
                            return array_filter($requests, function($request) {
                                // All requests in the file are considered pending
                                return true;
                            });
                        }
                    }
                }
            } catch (\Exception $fileException) {
                error_log("AdminService: Error getting access requests from file: " . $fileException->getMessage());
            }
            
            return [];
        }
    }

    /**
     * Одобрить заявку на доступ
     */
    public function approveAccessRequest(int $userId, string $userName, string $userChatId, int $adminId): bool
    {
        try {
            // First, find the request by user ID to get its ID
            $request = $this->databaseService->getAccessRequestByUserId($userId);
            
            if ($request === null) {
                return false;
            }
            
            $requestId = $request['id'];
            
            // Use database service to approve access request
            $result = $this->databaseService->approveAccessRequest($requestId, $adminId);
            
            if ($result) {
                // Обновляем уровень доступа пользователя до уровня Пользователь
                $this->databaseService->updateUserAccessLevel($userId, 1); // ACCESS_LEVEL_USER
                
                // Отправляем уведомление пользователю
                $message = "✅ <b>Заявка одобрена</b>\n\n";
                $message .= "Ваша заявка на доступ к боту одобрена.\n";
                $message .= "Теперь у вас есть доступ к основным функциям бота.";
                
                try {
                    $this->messageService->sendMessage($userChatId, $message);
                } catch (\Exception $e) {
                    error_log("AdminService: Error sending approval message to user: " . $e->getMessage());
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("AdminService: Error approving access request in database: " . $e->getMessage());
            
            
            // Fallback to file-based access requests
            try {
                if (file_exists($this->accessRequestsFile)) {
                    $jsonData = file_get_contents($this->accessRequestsFile);
                    if ($jsonData !== false) {
                        $requests = json_decode($jsonData, true);
                        if (is_array($requests)) {
                            // Find and remove the request by user ID
                            $updatedRequests = [];
                            $found = false;
                            foreach ($requests as $request) {
                                if (isset($request['user_id']) && (int)$request['user_id'] === $userId) {
                                    $found = true;
                                    // This request is approved, so we don't add it back to the list
                                    // Instead, we update the user's access level
                                    $this->databaseService->updateUserAccessLevel($userId, 1); // ACCESS_LEVEL_USER
                                    
                                    // Отправляем уведомление пользователю
                                    $message = "✅ <b>Заявка одобрена</b>\n\n";
                                    $message .= "Ваша заявка на доступ к боту одобрена.\n";
                                    $message .= "Теперь у вас есть доступ к основным функциям бота.";
                                    
                                    try {
                                        $this->messageService->sendMessage($userChatId, $message);
                                    } catch (\Exception $msgException) {
                                        error_log("AdminService: Error sending approval message to user: " . $msgException->getMessage());
                                    }
                                } else {
                                    // Keep other requests
                                    $updatedRequests[] = $request;
                                }
                            }
                            
                            if ($found) {
                                // Save updated requests back to file
                                $jsonData = json_encode(array_values($updatedRequests), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                // Write data to file
                                file_put_contents($this->accessRequestsFile, $jsonData);
                                return true;
                            }
                        }
                    }
                }
            } catch (\Exception $fileException) {
                error_log("AdminService: Error approving access request in file: " . $fileException->getMessage());
            }
            
            return false;
        }
    }

    /**
     * Отклонить заявку на доступ
     */
    public function rejectAccessRequest(int $userId, string $userName, string $userChatId, int $adminId): bool
    {
        try {
            // First, find the request by user ID to get its ID
            $request = $this->databaseService->getAccessRequestByUserId($userId);
            
            if ($request === null) {
                return false;
            }
            
            $requestId = $request['id'];
            
            // Use database service to reject access request
            $result = $this->databaseService->rejectAccessRequest($requestId, $adminId);
            
            if ($result) {
                // Отправляем уведомление пользователю
                $message = "❌ <b>Заявка отклонена</b>\n\n";
                $message .= "Ваша заявка на доступ к боту отклонена.\n";
                $message .= "Если вы считаете, что это ошибка, обратитесь к администратору.";
                
                try {
                    $this->messageService->sendMessage($userChatId, $message);
                } catch (\Exception $e) {
                    error_log("AdminService: Error sending rejection message to user: " . $e->getMessage());
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("AdminService: Error rejecting access request in database: " . $e->getMessage());
            
            // Fallback to file-based access requests
            try {
                if (file_exists($this->accessRequestsFile)) {
                    $jsonData = file_get_contents($this->accessRequestsFile);
                    if ($jsonData !== false) {
                        $requests = json_decode($jsonData, true);
                        if (is_array($requests)) {
                            // Find and remove the request by user ID
                            $updatedRequests = [];
                            $found = false;
                            foreach ($requests as $request) {
                                if (isset($request['user_id']) && (int)$request['user_id'] === $userId) {
                                    $found = true;
                                    // This request is rejected, so we don't add it back to the list
                                    
                                    // Отправляем уведомление пользователю
                                    $message = "❌ <b>Заявка отклонена</b>\n\n";
                                    $message .= "Ваша заявка на доступ к боту отклонена.\n";
                                    $message .= "Если вы считаете, что это ошибка, обратитесь к администратору.";
                                    
                                    try {
                                        $this->messageService->sendMessage($userChatId, $message);
                                    } catch (\Exception $msgException) {
                                        error_log("AdminService: Error sending rejection message to user: " . $msgException->getMessage());
                                    }
                                } else {
                                    // Keep other requests
                                    $updatedRequests[] = $request;
                                }
                            }
                            
                            if ($found) {
                                // Save updated requests back to file
                                $jsonData = json_encode(array_values($updatedRequests), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                // Write data to file
                                file_put_contents($this->accessRequestsFile, $jsonData);
                                return true;
                            }
                        }
                    }
                }
            } catch (\Exception $fileException) {
                error_log("AdminService: Error rejecting access request in file: " . $fileException->getMessage());
            }
            
            return false;
        }
    }

    /**
     * Очистить все заявки на доступ
     */
    public function clearAllAccessRequests(): void
    {
        try {
            // Use database service to clear pending access requests
            $this->databaseService->clearPendingAccessRequests();
        } catch (\Exception $e) {
            error_log("AdminService: Error clearing access requests in database: " . $e->getMessage());
            
            // Fallback to file-based access requests
            try {
                if (file_exists($this->accessRequestsFile)) {
                    // Clear the file by writing an empty array
                    $jsonData = json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    // Write data to file
                    file_put_contents($this->accessRequestsFile, $jsonData);
                }
            } catch (\Exception $fileException) {
                error_log("AdminService: Error clearing access requests in file: " . $fileException->getMessage());
            }
        }
    }

    /**
     * Проверить, есть ли у пользователя pending заявка на доступ
     */
    public function hasPendingAccessRequest(int $userId): bool
    {
        try {
            $request = $this->databaseService->getAccessRequestByUserId($userId);
            return $request !== null;
        } catch (\Exception $e) {
            error_log("AdminService: Error checking pending access request in database: " . $e->getMessage());
            
            // Fallback to file-based access requests
            try {
                if (file_exists($this->accessRequestsFile)) {
                    $jsonData = file_get_contents($this->accessRequestsFile);
                    if ($jsonData !== false) {
                        $requests = json_decode($jsonData, true);
                        if (is_array($requests)) {
                            // Find request by user ID
                            foreach ($requests as $request) {
                                if (isset($request['user_id']) && (int)$request['user_id'] === $userId) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $fileException) {
                error_log("AdminService: Error checking pending access request in file: " . $fileException->getMessage());
            }
            
            return false;
        }
    }

    /**
     * Получить заявку на доступ по ID пользователя
     */
    public function getAccessRequestByUserId(int $userId): ?array
    {
        try {
            // First try to get from database
            return $this->databaseService->getAccessRequestByUserId($userId);
        } catch (\Exception $e) {
            error_log("AdminService: Error getting access request from database: " . $e->getMessage());
            
            // Fallback to file-based access requests
            try {
                if (file_exists($this->accessRequestsFile)) {
                    $jsonData = file_get_contents($this->accessRequestsFile);
                    if ($jsonData !== false) {
                        $requests = json_decode($jsonData, true);
                        if (is_array($requests)) {
                            // Find request by user ID
                            foreach ($requests as $request) {
                                if (isset($request['user_id']) && (int)$request['user_id'] === $userId) {
                                    return $request;
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $fileException) {
                error_log("AdminService: Error getting access request from file: " . $fileException->getMessage());
            }
            
            return null;
        }
    }

    /**
     * Перезагрузить заявки на доступ из файла
     */
    public function reloadAccessRequests(): void
    {
        // Not needed as we're reading from file each time
    }

    /**
     * Проверить существует ли проект
     */
    public function projectExists(string $projectCode): bool
    {
        return in_array(strtoupper(trim($projectCode)), $this->projects);
    }

    /**
     * Временное сохранение проектов в файл
     */
    private function saveProjectsToFile(): void
    {
        $filePath = __DIR__ . '/../../data/projects.json';
        
        // Создаем директорию если не существует
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $jsonData = json_encode($this->projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Write data to file
        file_put_contents($filePath, $jsonData);
    }

    /**
     * Временная загрузка проектов из файла
     */
    private function loadProjectsFromFile(): void
    {
        $filePath = __DIR__ . '/../../data/projects.json';
        
        if (file_exists($filePath)) {
            $jsonData = file_get_contents($filePath);
            if ($jsonData !== false) {
                $projects = json_decode($jsonData, true);
                if (is_array($projects)) {
                    $this->projects = $projects;
                }
            }
        }
    }

    /**
     * Сохранение заявок на доступ в файл
     */
    private function saveAccessRequests(array $requests): void
    {
        // Создаем директорию если не существует
        $dir = dirname($this->accessRequestsFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $jsonData = json_encode(array_values($requests), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Write data to file
        file_put_contents($this->accessRequestsFile, $jsonData);
    }

    /**
     * Назначить пользователя супер-админом
     */
    public function setSuperAdmin(int $userId, bool $isSuperAdmin = true): bool
    {
        // For now, we'll just return true
        // In a real implementation, you would update this in a database
        return true;
    }
}
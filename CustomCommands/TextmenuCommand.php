<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Text menu command
 *
 * This command handles text menu selections from the reply keyboard.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

class TextmenuCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';

    /**
     * @var string
     */
    protected $description = 'Handle text menu selections';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * @var MessageService
     */
    private MessageService $messageService;

    /**
     * @var AdminService
     */
    private AdminService $adminService;

    /**
     * Initialize services
     */
    private function initializeServices(): void
    {
        if (isset($this->messageService) && isset($this->adminService)) {
            return; // Already initialized
        }

        $this->messageService = new MessageService();
        $this->adminService = new AdminService($this->messageService);
    }

    /**
     * Main command execution
     */
    public function execute(): ServerResponse
    {
        $this->initializeServices();

        $message = $this->getMessage();
        $text = trim($message->getText());
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();

        // Debug: Log which command is handling the message
        error_log("TextmenuCommand handling text: '{$text}' from user: {$userId} in chat: {$chatId}");

        // Получаем уровень доступа пользователя
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);

        // Для гостей (уровень 0) ограничиваем доступ к меню
        if ($userAccessLevel == 0) { // Guest level
            switch ($text) {
                case '👤 Профиль':
                case 'Профиль':
                    return $this->handleProfileMenu($chatId);
                    
                case '❓ Помощь':
                case 'Помощь':
                    return $this->handleHelpMenu($chatId);
                    
                case '🔐 Запросить доступ':
                case 'Запросить доступ':
                    // Обработка запроса доступа для гостей
                    return $this->handleAccessRequest($chatId, $userId);
                    
                case '📊 Табель':
                case 'Табель':
                case '⚙️ Админка':
                case 'Админка':
                    // Ограничиваем доступ для гостей
                    return $this->messageService->sendMessage(
                        $chatId,
                        "⚠️ <b>Доступ ограничен</b>\n\nУ вас уровень доступа \"Гость\". Обратитесь к администратору для получения расширенного доступа.",
                        null
                    );
                    
                default:
                    // Если текст не соответствует меню - игнорируем
                    return Request::emptyResponse();
            }
        }

        // Обрабатываем команды из Reply меню для пользователей с уровнем доступа выше гостя
        switch ($text) {
            case '📊 Табель':
            case 'Табель':
                return $this->handleTabelMenu($chatId);
                
            case '❓ Помощь':
            case 'Помощь':
                return $this->handleHelpMenu($chatId);
                
            case '👤 Профиль':
            case 'Профиль':
                return $this->handleProfileMenu($chatId);
                
            case '📁 Проекты':
            case 'Проекты':
                // Debug: Log that we're handling the projects button
                error_log("TextmenuCommand handling projects button for user: {$userId}");
                error_log("TextmenuCommand projects button text matched: '{$text}'");
                return $this->handleProjectsMenu($chatId, $userId);
                
            case '⚙️ Админка':
            case 'Админка':
                // Debug: Log that we're handling the admin button
                error_log("TextmenuCommand handling admin button for user: {$userId}");
                return $this->handleAdminMenu($chatId, $userId);
                
            case '🛠️ Управление проектом':
            case 'Управление проектом':
                // Debug: Log that we're handling the project management button
                error_log("TextmenuCommand handling project management button for user: {$userId}");
                return $this->handleProjectManagementMenu($chatId, $userId);
                
            case '📋 Супервайзер':
            case 'Супервайзер':
                // Debug: Log that we're handling the supervisor button
                error_log("TextmenuCommand handling supervisor button for user: {$userId}");
                return $this->handleSupervisorMenu($chatId, $userId);
                
            default:
                // Debug: Log unrecognized text
                error_log("TextmenuCommand received unrecognized text: '{$text}' from user: {$userId}");
                
                // Если текст не соответствует меню - игнорируем
                return Request::emptyResponse();
        }
    }

    /**
     * Обработка выбора "Табель"
     */
    private function handleTabelMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createWorkTypeKeyboard();
        return $this->messageService->sendMessage(
            $chatId,
            '📊 <b>Табель учета рабочего времени</b>

Выберите подразделение или вид деятельности:',
            $keyboard
        );
    }

    /**
     * Обработка выбора "Помощь"
     */
    private function handleHelpMenu(string $chatId): ServerResponse
    {
        $text = "📘 <b>Помощь</b>\n\n";
        $text .= "Добро пожаловать в справочный раздел!\n";
        $text .= "Выберите раздел:\n";
        
        $keyboard = [
            [
                ['text' => '🚀 Как начать работу', 'callback_data' => 'category_Помощь_Начало']
            ],
            [
                ['text' => '✨ Основные функции', 'callback_data' => 'category_Помощь_Функции']
            ],
            [
                ['text' => '❓ Частые вопросы', 'callback_data' => 'category_Помощь_FAQ']
            ],
            [
                ['text' => '🔐 Уровни доступа', 'callback_data' => 'category_Помощь_Уровни']
            ],
            [
                ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Обработка выбора "Профиль"
     */
    private function handleProfileMenu(string $chatId): ServerResponse
    {
        error_log("TextmenuCommand handleProfileMenu called");
        $keyboard = [
            [
                ['text' => '👤 Просмотр профиля', 'callback_data' => 'category_Профиль']
            ]
        ];
        
        error_log("TextmenuCommand sending profile menu with keyboard: " . print_r($keyboard, true));
        return $this->messageService->sendMessage(
            $chatId,
            '👤 <b>Профиль пользователя</b>

Выберите действие:',
            $keyboard
        );
    }

    /**
     * Обработка выбора "Проекты"
     */
    private function handleProjectsMenu(string $chatId, int $userId): ServerResponse
    {
        error_log("TextmenuCommand handleProjectsMenu called for user: {$userId}");
        error_log("TextmenuCommand handleProjectsMenu chatId: {$chatId}");
        
        // Проверяем уровень доступа пользователя
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        error_log("TextmenuCommand handleProjectsMenu userAccessLevel: {$userAccessLevel}");
        
        // Проверяем, что пользователь не гость (уровень 1+)
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_USER) {
            error_log("TextmenuCommand: user {$userId} doesn't have access to projects (guest level)");
            return $this->messageService->sendMessage(
                $chatId,
                "⚠️ <b>Доступ ограничен</b>\n\nУ вас уровень доступа \"Гость\". Обратитесь к администратору для получения доступа к проектам.",
                null
            );
        }
        
        $keyboard = [
            [
                ['text' => '📂 Мои проекты', 'callback_data' => 'category_Проекты_МоиПроекты']
            ],
            [
                ['text' => '📊 Моя активность', 'callback_data' => 'category_Проекты_МояАктивность']
            ]
        ];
        
        error_log("TextmenuCommand sending projects menu with keyboard: " . print_r($keyboard, true));
        return $this->messageService->sendMessage(
            $chatId,
            "📁 <b>Проекты</b>\nВыберите действие:",
            $keyboard
        );
    }

    /**
     * Обработка выбора "Админка" - УПРОЩЕННАЯ ВЕРСИЯ
     */
    private function handleAdminMenu(string $chatId, int $userId): ServerResponse
    {
        // Debug: Log that we're handling the admin menu
        error_log("TextmenuCommand handleAdminMenu called for user: {$userId}");
        
        // Проверяем, является ли пользователь супер-админом
        $isSuperAdmin = $this->adminService->isSuperAdmin($userId);
        error_log("User {$userId} is super admin (service check): " . ($isSuperAdmin ? 'true' : 'false'));
        
        // Получаем уровень доступа пользователя
        $accessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($accessLevel);
        
        // UPDATED: Супервайзеры, администраторы и супер-админы имеют доступ к админке
        // Супервайзеры видят только модерацию проектов
        if ($accessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            // Debug: Log that user has access to admin panel
            error_log("User {$userId} has access to admin panel (level {$accessLevel}), showing menu");
            
            $keyboard = [];
            
            // Администраторы и Суперадмины видят полное меню
            if ($accessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
                $keyboard[] = [
                    ['text' => '📋 Управление проектами', 'callback_data' => 'category_Админка_Проекты']
                ];
                
                // Добавляем кнопку заявок на доступ с счетчиком
                try {
                    $databaseService = new DatabaseService();
                    $accessRequestsCount = count($databaseService->getPendingAccessRequests());
                    
                    $accessButtonText = '👥 Заявки на доступ';
                    if ($accessRequestsCount > 0) {
                        $accessButtonText .= " ({$accessRequestsCount})";
                    }
                    
                    $keyboard[] = [
                        ['text' => $accessButtonText, 'callback_data' => 'category_Админка_Заявки']
                    ];
                    error_log("TextmenuCommand: Access requests button added: {$accessButtonText}");
                } catch (\Exception $e) {
                    error_log("TextmenuCommand: Error adding access requests button: " . $e->getMessage());
                    // Fallback без счетчика если ошибка
                    $keyboard[] = [
                        ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
                    ];
                }
            }
            
            // Управление уровнями доступа - для администраторов и выше
            if ($accessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
                $keyboard[] = [
                    ['text' => '🔐 Управление уровнями доступа', 'callback_data' => 'category_УровеньДоступа_Управление']
                ];
            }
            
            // Разные заголовки для разных уровней
            if ($accessLevel === DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                $title = "⚙️ <b>Панель супервайзера</b>";
                $description = "Вы можете модерировать проекты, добавленные пользователями.";
            } else {
                $title = "⚙️ <b>Панель администратора</b>";
                $description = "Выберите действие:";
            }

            return $this->messageService->sendMessage(
                $chatId,
                "{$title}\n\nВаш уровень доступа: {$levelName}\n\n{$description}",
                $keyboard
            );
        } else {
            // Debug: Log that user doesn't have admin access
            error_log("User {$userId} doesn't have admin access, showing request access prompt");
            
            // Check if user already has a pending access request
            if ($this->adminService->hasPendingAccessRequest($userId)) {
                return $this->messageService->sendMessage(
                    $chatId,
                    "📝 <b>Запрос доступа</b>

У вас уже есть активный запрос на получение доступа.
Пожалуйста, дождитесь рассмотрения вашего запроса администратором."
                );
            }
            
            // Показываем меню с кнопкой "Запросить доступ"
            $keyboard = $this->adminService->createAccessRequestKeyboard();
            return $this->messageService->sendMessage(
                $chatId,
                "🔐 <b>Админка</b>

Для получения доступа к административным функциям подайте заявку.",
                $keyboard
            );
        }
    }

    /**
     * Обработка запроса доступа от гостя
     */
    private function handleAccessRequest(string $chatId, int $userId): ServerResponse
    {
        error_log("TextmenuCommand: handleAccessRequest called for user: {$userId}");
        
        // Get user access level
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($userAccessLevel);
        
        // Check if user already has a pending access request
        if ($this->adminService->hasPendingAccessRequest($userId)) {
            error_log("TextmenuCommand: user {$userId} already has a pending access request");
            return $this->messageService->sendMessage(
                $chatId,
                "📝 <b>Запрос доступа</b>\n\nУ вас уже есть активный запрос на получение доступа.\nПожалуйста, дождитесь рассмотрения вашего запроса администратором."
            );
        }
        
        // For guests (level 0), check if they have required profile information
        if ($userAccessLevel == 0) {
            error_log("TextmenuCommand: user {$userId} is a guest, checking profile information");
            // Get user profile data
            $userData = $this->adminService->getUserData($userId);
            $userFio = $userData['fio'] ?? '';
            $userFunctionalDirection = $userData['functional_direction'] ?? '';
            
            error_log("TextmenuCommand: user profile data for {$userId} - FIO: '{$userFio}', Functional Direction: '{$userFunctionalDirection}'");
            
            // Check if required profile information is provided
            if (empty($userFio) || $userFio === 'Без ФИО' || empty($userFunctionalDirection) || $userFunctionalDirection === 'Не указано') {
                error_log("TextmenuCommand: user {$userId} missing required profile information");
                $text = "📝 <b>Запрос доступа</b>\n\n";
                $text .= "⚠️ Для подачи заявки на доступ необходимо заполнить профиль:\n";
                
                if (empty($userFio) || $userFio === 'Без ФИО') {
                    $text .= "• Указать ФИО\n";
                }
                
                if (empty($userFunctionalDirection) || $userFunctionalDirection === 'Не указано') {
                    $text .= "• Указать функциональное направление\n";
                }
                
                $text .= "\nПожалуйста, перейдите в профиль и заполните недостающую информацию.";
                
                // Add a button to go to the profile
                $keyboard = [
                    [
                        ['text' => '👤 Перейти в профиль', 'callback_data' => 'category_Профиль']
                    ]
                ];
                
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
            
            error_log("TextmenuCommand: user {$userId} has required profile information, sending access request");
            // Send access request
            $userName = $userData['first_name'] ?? '' . ' ' . ($userData['last_name'] ?? '');
            $success = $this->adminService->sendAccessRequest($userId, $userName, $chatId);
            
            if ($success) {
                error_log("TextmenuCommand: access request sent successfully for user: {$userId}");
                $text = "📝 <b>Запрос доступа отправлен</b>\n\n";
                $text .= "Ваш текущий уровень: {$levelName}\n";
                $text .= "Запрашиваемый уровень: Пользователь\n";
                $text .= "ФИО: {$userFio}\n";
                $text .= "Функциональное направление: {$userFunctionalDirection}\n\n";
                $text .= "Администраторы получили уведомление о вашем запросе.\n";
                $text .= "Ожидайте подтверждения.";
            } else {
                error_log("TextmenuCommand: failed to send access request for user: {$userId}");
                $text = "❌ <b>Ошибка при отправке запроса</b>\n\n";
                $text .= "Не удалось отправить запрос. Проверьте, что профиль заполнен корректно.";
            }
        } else {
            error_log("TextmenuCommand: user {$userId} already has access level: {$userAccessLevel}");
            $text = "🔐 <b>Запрос доступа</b> У вас уже есть уровень доступа выше гостя: {$levelName}";
        }
        
        return $this->messageService->sendMessage($chatId, $text);
    }

    /**
     * Обработка выбора "Управление проектом"
     */
    private function handleProjectManagementMenu(string $chatId, int $userId): ServerResponse
    {
        error_log("TextmenuCommand handleProjectManagementMenu called for user: {$userId}");
        
        // Проверяем, является ли пользователь супервайзером или выше
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        error_log("TextmenuCommand handleProjectManagementMenu userAccessLevel: {$userAccessLevel}");
        
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            error_log("TextmenuCommand: user {$userId} doesn't have access to project management (not supervisor+)");
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Доступ запрещен</b>\n\nФункция \"Управление проектом\" доступна только для Супервайзеров, Администраторов и Суперадминистраторов.",
                null
            );
        }
        
        $text = "🛠️ <b>Управление проектом</b>\n\n";
        $text .= "Добро пожаловать в раздел управления проектами!\n";
        $text .= "Выберите нужный пункт меню:\n";
        
        $keyboard = [
            [
                ['text' => '👥 Персонал', 'callback_data' => 'category_УправлениеПроектом_Персонал']
            ],
            [
                ['text' => '🏗️ Объекты', 'callback_data' => 'pm_objects']
            ],
            [
                ['text' => '🗂️ Карточка проекта', 'callback_data' => 'category_Проекты_КарточкаПроекта']
            ],
            [
                ['text' => '📋 Отчёт по проекту', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ]
        ];
        
        // Добавляем кнопку модерации проектов со счетчиком
        try {
            $databaseService = new DatabaseService();
            $pendingCount = $databaseService->getPendingProjectsCount();
            
            $buttonText = '🔔 Проекты на согласование';
            if ($pendingCount > 0) {
                $buttonText .= " ({$pendingCount})";
            }
            
            $keyboard[] = [
                ['text' => $buttonText, 'callback_data' => 'category_Админка_МодерацияПроектов']
            ];
        } catch (\Exception $e) {
            error_log("TextmenuCommand: Error adding moderation button: " . $e->getMessage());
        }
        
        $keyboard[] = [
            ['text' => '🔍 Проекты на контроле', 'callback_data' => 'pm_control']
        ];
        $keyboard[] = [
            ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
        ];
        
        error_log("TextmenuCommand sending project management menu with Objects button - NEW VERSION");
        error_log("TextmenuCommand keyboard: " . print_r($keyboard, true));
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Обработка выбора "Супервайзер"
     */
    private function handleSupervisorMenu(string $chatId, int $userId): ServerResponse
    {
        error_log("TextmenuCommand handleSupervisorMenu called for user: {$userId}");
        
        // Проверяем уровень доступа пользователя
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        error_log("TextmenuCommand handleSupervisorMenu userAccessLevel: {$userAccessLevel}");
        
        // Проверяем, что пользователь - супервайзер или выше
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            error_log("TextmenuCommand: user {$userId} doesn't have access to supervisor menu");
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Доступ запрещен</b>\n\nЭтот раздел доступен только для супервайзеров и выше.",
                null
            );
        }
        
        $text = "📋 <b>Панель супервайзера</b>\n\n";
        $text .= "Выберите действие:\n";
        
        $keyboard = [
            [
                ['text' => '📄 Отчеты', 'callback_data' => 'category_Супервайзер_Отчеты']
            ],
            [
                ['text' => '📈 Активность', 'callback_data' => 'category_Супервайзер_Активность']
            ],
            [
                ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
            ]
        ];
        
        error_log("TextmenuCommand sending supervisor menu with keyboard: " . print_r($keyboard, true));
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
}
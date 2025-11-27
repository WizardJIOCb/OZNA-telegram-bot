<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for personnel management callback queries
 */
class PersonnelHandler implements CallbackHandlerInterface
{
    private AdminService $adminService;
    private MessageService $messageService;
    private PersonnelService $personnelService;

    public function __construct(AdminService $adminService, MessageService $messageService)
    {
        error_log("PersonnelHandler::__construct called - STARTING INITIALIZATION");
        
        $this->adminService = $adminService;
        $this->messageService = $messageService;
        
        error_log("PersonnelHandler::__construct - Creating PersonnelService");
        $this->personnelService = new PersonnelService(new DatabaseService());
        
        error_log("PersonnelHandler::__construct - INITIALIZATION COMPLETE");
    }

    public function canHandle(string $callbackData): bool
    {
        $canHandle = strpos($callbackData, 'personnel_') === 0;
        
        error_log("PersonnelHandler canHandle check for '{$callbackData}': " . ($canHandle ? 'true' : 'false'));
        
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("PersonnelHandler handling callback for user: {$userId}, category: '{$category}'");

        // Check access level - требуется администратор (3) или выше
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            error_log("PersonnelHandler user {$userId} doesn't have admin access");
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Недостаточно прав</b>\n\n" .
                "Для управления персоналом требуется уровень доступа Администратор или выше."
            );
        }

        // Main personnel menu
        if ($category === 'personnel_main') {
            return $this->showPersonnelMainMenu($chatId);
        }

        // Functional direction menus
        if ($category === 'personnel_fn_mobile') {
            return $this->showStatusMenu($chatId, 'mobile', 'Мобильная бригада', 'personnel_main');
        }
        if ($category === 'personnel_fn_shmr') {
            return $this->showStatusMenu($chatId, 'shmr', 'Отдел ШМР', 'personnel_main');
        }
        if ($category === 'personnel_fn_pnr') {
            return $this->showStatusMenu($chatId, 'pnr', 'Отдел ПНР', 'personnel_main');
        }

        // Status lists - На объекте
        if (preg_match('/^personnel_status_(mobile|shmr|pnr)_onsite$/', $category, $matches)) {
            $fn = $matches[1];
            return $this->showOnsiteEmployees($chatId, $fn);
        }

        // Status lists - Свободная вахта
        if (preg_match('/^personnel_status_(mobile|shmr|pnr)_freetime$/', $category, $matches)) {
            $fn = $matches[1];
            return $this->showFreetimeEmployees($chatId, $fn);
        }

        // Status lists - Отпуск (stub)
        if (preg_match('/^personnel_status_(mobile|shmr|pnr)_vacation$/', $category, $matches)) {
            $fn = $matches[1];
            return $this->showVacationStub($chatId, $fn);
        }

        // Status lists - Больничный (stub)
        if (preg_match('/^personnel_status_(mobile|shmr|pnr)_sick$/', $category, $matches)) {
            $fn = $matches[1];
            return $this->showSickLeaveStub($chatId, $fn);
        }

        error_log("PersonnelHandler unknown category: '{$category}'");
        return $this->messageService->sendMessage($chatId, '❌ Неизвестная команда управления персоналом');
    }

    public function getPriority(): int
    {
        return 24; // Higher priority than AccessLevelHandler (25)
    }

    /**
     * Show main personnel menu with functional directions
     */
    private function showPersonnelMainMenu(string $chatId): ServerResponse
    {
        $keyboard = [
            [
                ['text' => '👷‍♂️ Мобильная бригада', 'callback_data' => 'personnel_fn_mobile']
            ],
            [
                ['text' => '🏗️ Отдел ШМР', 'callback_data' => 'personnel_fn_shmr']
            ],
            [
                ['text' => '⚙️ Отдел ПНР', 'callback_data' => 'personnel_fn_pnr']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'acc_lvl_users']
            ]
        ];

        $message = "👨‍💼 <b>Управление персоналом</b>\n\nВыберите функциональное направление:";

        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }
    
    /**
     * Show personnel menu when accessed from Project Management
     */
    private function showPersonnelMenuFromPM(string $chatId): ServerResponse
    {
        $keyboard = [
            [
                ['text' => '👷‍♂️ Мобильная бригада', 'callback_data' => 'pm_pers_mobile']
            ],
            [
                ['text' => '🏭 Отдел ШМР', 'callback_data' => 'pm_pers_shmr']
            ],
            [
                ['text' => '⚙️ Отдел ПНР', 'callback_data' => 'pm_pers_pnr']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pm_menu']
            ]
        ];

        $message = "👨‍💼 <b>Управление персоналом</b>\n\nВыберите функциональное направление:";

        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Show status menu for selected functional direction
     */
    private function showStatusMenu(string $chatId, string $fn, string $fnName, string $backCallback = 'personnel_main'): ServerResponse
    {
        error_log("PersonnelHandler::showStatusMenu called - chatId: {$chatId}, fn: {$fn}, fnName: {$fnName}");
        
        try {
            // Map the fn code to database value (must match values in ozna_data.functional_direction)
            $fnMapping = [
                'mobile' => 'Мобильная бригада',
                'shmr' => 'Шеф-монтаж',
                'pnr' => 'Пусконаладка'
            ];
            
            $dbFn = $fnMapping[$fn] ?? '';
            error_log("PersonnelHandler::showStatusMenu - Mapped fn '{$fn}' to database value: '{$dbFn}'");
            
            if (empty($dbFn)) {
                error_log("PersonnelHandler::showStatusMenu - ERROR: Invalid fn code '{$fn}'");
                return $this->messageService->sendMessage(
                    $chatId,
                    '❌ Неверный код функционального направления',
                    $this->getBackKeyboard()
                );
            }
            
            // Get counts for each status
            $onsiteCount = 0;
            $freetimeCount = 0;
            
            try {
                error_log("PersonnelHandler::showStatusMenu - Calling getOnsiteEmployees for fn: {$dbFn}");
                $onsiteEmployees = $this->personnelService->getOnsiteEmployees($dbFn);
                $onsiteCount = count($onsiteEmployees);
                error_log("PersonnelHandler::showStatusMenu - Onsite count: {$onsiteCount}");
            } catch (\Exception $e) {
                error_log("PersonnelHandler::showStatusMenu - ERROR getting onsite employees: " . $e->getMessage());
                error_log("PersonnelHandler::showStatusMenu - Stack trace: " . $e->getTraceAsString());
            }
            
            try {
                error_log("PersonnelHandler::showStatusMenu - Calling getFreetimeEmployees for fn: {$dbFn}");
                $freetimeEmployees = $this->personnelService->getFreetimeEmployees($dbFn);
                $freetimeCount = count($freetimeEmployees);
                error_log("PersonnelHandler::showStatusMenu - Freetime count: {$freetimeCount}");
            } catch (\Exception $e) {
                error_log("PersonnelHandler::showStatusMenu - ERROR getting freetime employees: " . $e->getMessage());
                error_log("PersonnelHandler::showStatusMenu - Stack trace: " . $e->getTraceAsString());
            }
            
            $vacationCount = 0;  // TODO: implement
            $sickCount = 0;      // TODO: implement
            
            error_log("PersonnelHandler::showStatusMenu - Building keyboard with counts - onsite: {$onsiteCount}, freetime: {$freetimeCount}");
            
            $keyboard = [
                [['text' => "🏗️ На объекте ({$onsiteCount})", 'callback_data' => "personnel_status_{$fn}_onsite"]],
                [['text' => "🏖️ Свободная вахта ({$freetimeCount})", 'callback_data' => "personnel_status_{$fn}_freetime"]],
                [['text' => "🌴 Отпуск ({$vacationCount})", 'callback_data' => "personnel_status_{$fn}_vacation"]],
                [['text' => "🏥 Больничный ({$sickCount})", 'callback_data' => "personnel_status_{$fn}_sick"]],
                [['text' => '⬅️ Назад', 'callback_data' => $backCallback]]
            ];
            
            error_log("PersonnelHandler::showStatusMenu - Sending message with keyboard");
            return $this->messageService->sendMessage(
                $chatId,
                "📊 Статистика по направлению: {$fnName}",
                $keyboard
            );
        } catch (\Exception $e) {
            error_log("PersonnelHandler::showStatusMenu - CRITICAL ERROR: " . $e->getMessage());
            error_log("PersonnelHandler::showStatusMenu - Stack trace: " . $e->getTraceAsString());
            
            return $this->messageService->sendMessage(
                $chatId,
                '❌ Ошибка при получении данных о персонале. Проверьте логи.',
                $this->getBackKeyboard()
            );
        }
    }

    /**
     * Show list of employees currently on site
     */
    private function showOnsiteEmployees(string $chatId, string $fn): ServerResponse
    {
        $dbFn = $this->mapFnToDatabase($fn);
        $fnName = $this->getFnDisplayName($fn);
        $employees = $this->personnelService->getOnsiteEmployees($dbFn);

        if (empty($employees)) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => "personnel_fn_{$fn}"]
                ]
            ];

            $message = "🏗️ <b>Сотрудники на объекте</b>\n{$fnName}\n\n⚠️ <b>Нет данных</b>\n\nНа данный момент нет сотрудников на объекте.";

            return $this->messageService->sendMessage($chatId, $message, $keyboard);
        }

        $message = "🏗️ <b>Сотрудники на объекте</b>\n{$fnName}\n\nСписок сотрудников и длительность пребывания:\n\n";

        foreach ($employees as $emp) {
            $daysWord = $this->personnelService->getDaysWord($emp['days']);
            $message .= "{$emp['icon']} {$emp['fio']} - {$emp['days']} {$daysWord}\n";
            $message .= "   📅 На объекте с: {$emp['start_date']}";
            
            // Add project code if available
            if (!empty($emp['project_code'])) {
                $message .= "\n   💼 Проект: {$emp['project_code']}";
            }
            
            $message .= "\n\n";
        }

        $message .= "Всего: " . count($employees);

        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => "personnel_fn_{$fn}"]
            ]
        ];

        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Show list of employees on free shift
     */
    private function showFreetimeEmployees(string $chatId, string $fn): ServerResponse
    {
        $dbFn = $this->mapFnToDatabase($fn);
        $fnName = $this->getFnDisplayName($fn);
        $employees = $this->personnelService->getFreetimeEmployees($dbFn);

        if (empty($employees)) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => "personnel_fn_{$fn}"]
                ]
            ];

            $message = "🏖️ <b>Сотрудники на свободной вахте</b>\n{$fnName}\n\n⚠️ <b>Нет данных</b>\n\nНа данный момент нет сотрудников на свободной вахте.";

            return $this->messageService->sendMessage($chatId, $message, $keyboard);
        }

        $message = "🏖️ <b>Сотрудники на свободной вахте</b>\n{$fnName}\n\nСписок сотрудников и длительность отдыха:\n\n";

        foreach ($employees as $emp) {
            $daysWord = $this->personnelService->getDaysWord($emp['days']);
            $message .= "{$emp['icon']} {$emp['fio']} - {$emp['days']} {$daysWord}\n";
            $message .= "   📅 Свободная вахта: {$emp['last_work_date']}\n\n";
        }

        $message .= "Всего: " . count($employees);

        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => "personnel_fn_{$fn}"]
            ]
        ];

        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Show vacation stub
     */
    private function showVacationStub(string $chatId, string $fn): ServerResponse
    {
        $fnName = $this->getFnDisplayName($fn);

        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => "personnel_fn_{$fn}"]
            ]
        ];

        $message = "🌴 <b>Отпуск</b>\n{$fnName}\n\n⚠️ <b>Функционал в разработке</b>\n\n" .
                   "В следующих версиях здесь будет отображаться список сотрудников, находящихся в отпуске.";

        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Show sick leave stub
     */
    private function showSickLeaveStub(string $chatId, string $fn): ServerResponse
    {
        $fnName = $this->getFnDisplayName($fn);

        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => "personnel_fn_{$fn}"]
            ]
        ];

        $message = "🏥 <b>Больничный</b>\n{$fnName}\n\n⚠️ <b>Функционал в разработке</b>\n\n" .
                   "В следующих версиях здесь будет отображаться список сотрудников, находящихся на больничном.";

        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Map FN code to database value
     */
    private function mapFnToDatabase(string $fn): string
    {
        $mapping = [
            'mobile' => 'Мобильная бригада',
            'shmr' => 'Шеф-монтаж',
            'pnr' => 'Пусконаладка'
        ];

        return $mapping[$fn] ?? 'Мобильная бригада';
    }

    /**
     * Get FN display name
     */
    private function getFnDisplayName(string $fn): string
    {
        $mapping = [
            'mobile' => '👷‍♂️ Мобильная бригада',
            'shmr' => '🏗️ Отдел ШМР',
            'pnr' => '⚙️ Отдел ПНР'
        ];

        return $mapping[$fn] ?? '👷‍♂️ Мобильная бригада';
    }

    /**
     * Get department keyboard
     */
    private function getDepartmentKeyboard(): array
    {
        return [
            [['text' => '🚗 Мобильная бригада', 'callback_data' => 'personnel_fn_mobile']],
            [['text' => '⚙️ Отдел ШМР', 'callback_data' => 'personnel_fn_shmr']],
            [['text' => '🔧 Отдел ПНР', 'callback_data' => 'personnel_fn_pnr']],
            [['text' => '⬅️ Назад', 'callback_data' => 'acc_lvl_main']]
        ];
    }
    
    /**
     * Get back button keyboard
     */
    private function getBackKeyboard(): array
    {
        return [
            [['text' => '⬅️ Назад', 'callback_data' => 'personnel_main']]
        ];
    }
}

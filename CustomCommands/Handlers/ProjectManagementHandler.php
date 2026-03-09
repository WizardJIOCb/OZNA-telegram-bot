<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectsOverviewService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for project management callback queries (super admin only)
 */
class ProjectManagementHandler implements CallbackHandlerInterface
{
    private MessageService $messageService;
    private AdminService $adminService;
    private ProjectsOverviewService $projectsOverviewService;
    private PersonnelService $personnelService;

    public function __construct(MessageService $messageService)
    {
        error_log("ProjectManagementHandler::__construct - START");
        
        try {
            $this->messageService = $messageService;
            error_log("ProjectManagementHandler::__construct - MessageService assigned");
            
            $this->adminService = new AdminService($messageService);
            error_log("ProjectManagementHandler::__construct - AdminService created");
            
            $this->projectsOverviewService = new ProjectsOverviewService(new DatabaseService());
            error_log("ProjectManagementHandler::__construct - ProjectsOverviewService created");
            
            $this->personnelService = new PersonnelService(new DatabaseService());
            error_log("ProjectManagementHandler::__construct - PersonnelService created");
            
            error_log("ProjectManagementHandler::__construct - COMPLETE");
        } catch (\Exception $e) {
            error_log("ProjectManagementHandler::__construct - ERROR: " . $e->getMessage());
            error_log("ProjectManagementHandler::__construct - TRACE: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function canHandle(string $callbackData): bool
    {
        error_log("ProjectManagementHandler::canHandle called with: '{$callbackData}'");
        
        // Handle project management callback (short codes)
        $canHandle = str_starts_with($callbackData, 'category_УправлениеПроектом') || 
                     str_starts_with($callbackData, 'pm_');
        
        error_log("ProjectManagementHandler::canHandle result: " . ($canHandle ? 'TRUE' : 'FALSE'));
        
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // CRITICAL FIX: Use callbackData directly if category is empty
        $callbackData = $callbackQuery->getData();
        if (empty($category)) {
            $category = $callbackData;
        }
        
        error_log("ProjectManagementHandler::handle called - category: '{$category}', userId: {$userId}");
        
        // Check if user is supervisor or above (level 2+)
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        error_log("ProjectManagementHandler::handle - user access level: {$userAccessLevel}");
        
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            error_log("ProjectManagementHandler::handle - ACCESS DENIED for user {$userId}");
            $text = "❌ <b>Доступ запрещен</b>\n\nФункция \"Управление проектом\" доступна только для Супервайзеров, Администраторов и Суперадминистраторов.";
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        error_log("ProjectManagementHandler::handle - processing category: '{$category}'");
        
        // Handle short codes (pm_*)
        if ($category === 'pm_objects') {
            error_log("ProjectManagementHandler::handle - showing projects overview menu");
            return $this->showProjectsOverviewMenu($chatId);
        } elseif ($category === 'pm_menu') {
            error_log("ProjectManagementHandler::handle - showing project management menu");
            return $this->showProjectManagementMenu($chatId, $userId);
        } elseif ($category === 'pm_personnel') {
            error_log("ProjectManagementHandler::handle - showing personnel menu");
            return $this->showPersonnelMenu($chatId);
        } elseif ($category === 'pm_control') {
            error_log("ProjectManagementHandler::handle - redirecting to control projects");
            // Check access level first
            if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                $text = "❌ <b>Доступ запрещен</b>\n\nФункция \"Проекты на контроле\" доступна только для Супервайзеров, Администраторов и Суперадминистраторов.";
                return $this->messageService->sendMessage($chatId, $text);
            }
            // Use the existing callback to trigger ProjectHandler
            // Create ProjectHandler instance
            $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler(
                $this->adminService,
                $this->messageService
            );
            // Call the handle method with the correct category
            return $projectHandler->handle($callbackQuery, 'Проекты_ПроектыНаКонтроле', $conversation);
        } elseif (preg_match('/^pm_pers_(mobile|shmr|pnr)$/', $category, $matches)) {
            error_log("ProjectManagementHandler::handle - showing personnel status for: {$matches[1]}");
            return $this->showPersonnelStatus($chatId, $matches[1]);
        } elseif (preg_match('/^pm_pers_status_(mobile|shmr|pnr)_(onsite|freetime|vacation|sick)$/', $category, $matches)) {
            error_log("ProjectManagementHandler::handle - showing personnel detail: {$matches[1]} / {$matches[2]}");
            return $this->showPersonnelStatusDetail($chatId, $matches[1], $matches[2]);
        } elseif (preg_match('/^pm_obj_(mobile|shmr|pnr|all|zpi)$/', $category, $matches)) {
            error_log("ProjectManagementHandler::handle - showing FN projects: {$matches[1]}");
            return $this->showFnProjects($chatId, $matches[1]);
        } elseif (preg_match('/^pm_prj_(.+)$/', $category, $matches)) {
            error_log("ProjectManagementHandler::handle - showing project detail: {$matches[1]}");
            return $this->showProjectDetail($chatId, $matches[1]);
        }
        
        // Handle long codes (backward compatibility)
        if ($category === 'УправлениеПроектом') {
            error_log("ProjectManagementHandler::handle - showing project management menu");
            return $this->showProjectManagementMenu($chatId, $userId);
        } elseif ($category === 'УправлениеПроектом_Персонал') {
            error_log("ProjectManagementHandler::handle - showing personnel menu");
            return $this->showPersonnelMenu($chatId);
        } elseif ($category === 'УправлениеПроектом_Объекты') {
            error_log("ProjectManagementHandler::handle - showing projects overview menu");
            return $this->showProjectsOverviewMenu($chatId);
        } elseif (preg_match('/^УправлениеПроектом_Объекты_(mobile|shmr|pnr|all|zpi)$/', $category, $matches)) {
            error_log("ProjectManagementHandler::handle - showing FN projects: {$matches[1]}");
            return $this->showFnProjects($chatId, $matches[1]);
        } elseif (preg_match('/^УправлениеПроектом_ПроектДеталь_(.+)$/', $category, $matches)) {
            error_log("ProjectManagementHandler::handle - showing project detail: {$matches[1]}");
            return $this->showProjectDetail($chatId, $matches[1]);
        }
        
        error_log("ProjectManagementHandler::handle - NO MATCH, default case, showing project management menu");
        return $this->showProjectManagementMenu($chatId, $userId);
    }
    
    /**
     * Show project management menu
     */
    private function showProjectManagementMenu(string $chatId, int $userId): ServerResponse
    {
        error_log("ProjectManagementHandler::showProjectManagementMenu called - NEW VERSION WITH OBJECTS BUTTON");
        
        $text = "🛠️ <b>Управление проектом</b>\n\n";
        $text .= "Добро пожаловать в раздел управления проектами!\n";
        $text .= "Выберите нужный пункт меню:\n";
        
        $keyboard = [
            [
                ['text' => '👥 Персонал', 'callback_data' => 'pm_personnel']
            ],
            [
                ['text' => '🏗️ Объекты', 'callback_data' => 'pm_objects']
            ],
            [
                ['text' => '🗂️ Карточки проектов', 'callback_data' => 'proj_card_menu']
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
            error_log("ProjectManagementHandler: Error adding moderation button: " . $e->getMessage());
        }
        
        $keyboard[] = [
            ['text' => '🔍 Проекты на контроле', 'callback_data' => 'pm_control']
        ];
        
        // Кнопка рекламаций со счётчиком ожидающих модерации
        try {
            $claimServiceFile = __DIR__ . '/../Services/ClaimService.php';
            if (file_exists($claimServiceFile)) {
                require_once $claimServiceFile;
                $claimPdo = new \PDO(
                    'mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4',
                    'WizardJIOCb',
                    '7qQ#28051961',
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
                $claimService = new \Longman\TelegramBot\Commands\SystemCommands\Services\ClaimService($claimPdo);
                $pendingClaimsCount = $claimService->getPendingClaimsCount();
                $claimButtonText = '📋 Рекламации';
                if ($pendingClaimsCount > 0) {
                    $claimButtonText .= " ({$pendingClaimsCount})";
                }
                $keyboard[] = [
                    ['text' => $claimButtonText, 'callback_data' => 'claim_menu']
                ];
            } else {
                error_log("ProjectManagementHandler: ClaimService.php not found at: " . $claimServiceFile);
                $keyboard[] = [
                    ['text' => '📋 Рекламации', 'callback_data' => 'claim_menu']
                ];
            }
        } catch (\Throwable $e) {
            error_log("ProjectManagementHandler: Error adding claims button: " . $e->getMessage());
            $keyboard[] = [
                ['text' => '📋 Рекламации', 'callback_data' => 'claim_menu']
            ];
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
        ];
        
        error_log("ProjectManagementHandler::showProjectManagementMenu - keyboard has " . count($keyboard) . " rows");
        error_log("ProjectManagementHandler::showProjectManagementMenu - sending message with Objects button");
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show personnel management menu (redirect to existing personnel system)
     */
    private function showPersonnelMenu(string $chatId): ServerResponse
    {
        $text = "👥 <b>Управление персоналом</b>\n\n";
        $text .= "Выберите функциональное направление:\n";
        
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
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    public function getPriority(): int
    {
        return 55; // High priority for project management
    }
    
    /**
     * Ensure UTF-8 encoding for text
     */
    private function ensureUtf8Encoding($text)
    {
        // If mbstring is available, use it for encoding conversion
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // If mbstring is not available, return as is
        return $text;
    }
    
    /**
     * Show projects overview menu - select by FN or all
     */
    private function showProjectsOverviewMenu(string $chatId): ServerResponse
    {
        error_log("ProjectManagementHandler::showProjectsOverviewMenu - START");
        
        try {
            // Get project counts for each category
            error_log("ProjectManagementHandler::showProjectsOverviewMenu - calling getProjectsByFunctionalDirection");
            $projectsByFn = $this->projectsOverviewService->getProjectsByFunctionalDirection();
            $mobileCount = count($projectsByFn['mobile'] ?? []);
            $shmrCount = count($projectsByFn['shmr'] ?? []);
            $pnrCount = count($projectsByFn['pnr'] ?? []);
            $allCount = count($projectsByFn['all'] ?? []);
            
            // Get ZPI projects count
            error_log("ProjectManagementHandler::showProjectsOverviewMenu - calling getZpiProjects");
            $zpiProjects = $this->projectsOverviewService->getZpiProjects();
            $zpiCount = count($zpiProjects);
            
            error_log("ProjectManagementHandler::showProjectsOverviewMenu - counts: mobile={$mobileCount}, shmr={$shmrCount}, pnr={$pnrCount}, all={$allCount}, zpi={$zpiCount}");
        } catch (\Exception $e) {
            error_log("ProjectManagementHandler::showProjectsOverviewMenu - ERROR: " . $e->getMessage());
            error_log("ProjectManagementHandler::showProjectsOverviewMenu - TRACE: " . $e->getTraceAsString());
            // Set all counts to 0 on error
            $mobileCount = 0;
            $shmrCount = 0;
            $pnrCount = 0;
            $allCount = 0;
            $zpiCount = 0;
        }
        
        $text = "🏗️ <b>Объекты</b>\n\n";
        $text .= "Оперативная оценка проектов в работе.\n";
        $text .= "Выберите функциональное направление:\n";
        
        $keyboard = [
            [
                ['text' => "👷‍♂️ Мобильная бригада ({$mobileCount})", 'callback_data' => 'pm_obj_mobile']
            ],
            [
                ['text' => "🏭 Отдел ШМР ({$shmrCount})", 'callback_data' => 'pm_obj_shmr']
            ],
            [
                ['text' => "⚙️ Отдел ПНР ({$pnrCount})", 'callback_data' => 'pm_obj_pnr']
            ],
            [
                ['text' => "📊 Итого в работе ({$allCount})", 'callback_data' => 'pm_obj_all']
            ],
            [
                ['text' => "🏢 Проекты ЗПИ ({$zpiCount})", 'callback_data' => 'pm_obj_zpi']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pm_menu']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show projects for specific FN or all
     */
    private function showFnProjects(string $chatId, string $fn): ServerResponse
    {
        error_log("ProjectManagementHandler::showFnProjects called - fn: {$fn}");
        
        if ($fn === 'zpi') {
            error_log("ProjectManagementHandler::showFnProjects - redirecting to ZPI projects");
            return $this->showZpiProjects($chatId);
        }
        
        $fnNames = [
            'mobile' => '👷‍♂️ Мобильная бригада',
            'shmr' => '🏭 Отдел ШМР',
            'pnr' => '⚙️ Отдел ПНР',
            'all' => '📊 Итого в работе'
        ];
        
        $fnName = $fnNames[$fn] ?? 'Проекты';
        
        error_log("ProjectManagementHandler::showFnProjects - fnName: {$fnName}");
        
        if ($fn === 'all') {
            error_log("ProjectManagementHandler::showFnProjects - getting all projects");
            $projectsByFn = $this->projectsOverviewService->getProjectsByFunctionalDirection();
            $projects = $projectsByFn['all'] ?? [];
            
            error_log("ProjectManagementHandler::showFnProjects - found " . count($projects) . " projects");
            
            $text = "{$fnName}\n\n";
            $text .= "Уникальные проекты (без ЗПИ):\n\n";
            
            if (empty($projects)) {
                $text .= "⚠️ Нет данных\n";
            } else {
                foreach ($projects as $projectCode) {
                    $details = $this->projectsOverviewService->getProjectDetails($projectCode);
                    if ($details) {
                        $daysWord = $this->projectsOverviewService->getDaysWord($details['duration_days']);
                        $text .= "💼 <b>{$projectCode}</b>\n";
                        $text .= "   👥 Специалистов: {$details['specialists_count']}\n";
                        $text .= "   📅 Старт: {$details['first_activity']}\n";
                        $text .= "   🕜 Последняя: {$details['last_activity']}\n";
                        $text .= "   ⏱️ Длительность: {$details['duration_days']} {$daysWord}\n\n";
                    }
                }
                $text .= "📊 Всего проектов: " . count($projects) . "\n";
            }
        } else {
            $summary = $this->projectsOverviewService->getFnProjectsSummary($fn);
            
            $text = "{$fnName}\n\n";
            $text .= "Проекты в работе:\n\n";
            
            if (empty($summary)) {
                $text .= "⚠️ Нет данных\n";
            } else {
                foreach ($summary as $project) {
                    $daysWord = $this->projectsOverviewService->getDaysWord($project['duration_days']);
                    $text .= "💼 <b>{$project['project_code']}</b>\n";
                    $text .= "   👥 Специалистов: {$project['specialists_count']}\n";
                    $text .= "   📅 Старт: {$project['first_activity']}\n";
                    $text .= "   🕜 Последняя: {$project['last_activity']}\n";
                    $text .= "   ⏱️ Длительность: {$project['duration_days']} {$daysWord}\n\n";
                }
                $text .= "📊 Всего проектов: " . count($summary) . "\n";
            }
        }
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pm_objects']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show ЗПИ projects separately
     */
    private function showZpiProjects(string $chatId): ServerResponse
    {
        $zpiProjects = $this->projectsOverviewService->getZpiProjects();
        
        $text = "🏢 <b>Проекты ЗПИ</b>\n\n";
        
        if (empty($zpiProjects)) {
            $text .= "⚠️ Нет данных\n";
        } else {
            foreach ($zpiProjects as $project) {
                $daysWord = $this->projectsOverviewService->getDaysWord($project['duration_days']);
                $text .= "💼 <b>{$project['project_code']}</b>\n";
                $text .= "   👥 Специалистов: {$project['specialists_count']}\n";
                $text .= "   📅 Старт: {$project['first_activity']}\n";
                $text .= "   🕜 Последняя: {$project['last_activity']}\n";
                $text .= "   ⏱️ Длительность: {$project['duration_days']} {$daysWord}\n\n";
            }
            $text .= "📊 Всего проектов ЗПИ: " . count($zpiProjects) . "\n";
        }
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pm_objects']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show project detail
     */
    private function showProjectDetail(string $chatId, string $projectCode): ServerResponse
    {
        $details = $this->projectsOverviewService->getProjectDetails($projectCode);
        
        if (!$details) {
            $text = "⚠️ <b>Проект не найден</b>\n\n";
            $text .= "Проект: {$projectCode}\n";
        } else {
            $daysWord = $this->projectsOverviewService->getDaysWord($details['duration_days']);
            
            $text = "💼 <b>Проект: {$projectCode}</b>\n\n";
            $text .= "📅 Дата старта: {$details['first_activity']}\n";
            $text .= "🕜 Последняя активность: {$details['last_activity']}\n";
            $text .= "⏱️ Длительность: {$details['duration_days']} {$daysWord}\n";
            $text .= "👥 Всего специалистов: {$details['specialists_count']}\n\n";
            
            // Group specialists by FN
            $text .= "👥 <b>Специалисты:</b>\n\n";
            
            foreach ($details['fn_map'] as $fn => $userIds) {
                $text .= "<b>{$fn}</b>:\n";
                $fnSpecialists = array_filter($details['specialists'], function($spec) use ($fn) {
                    return $spec['fn'] === $fn;
                });
                
                foreach ($fnSpecialists as $specialist) {
                    $text .= "  • {$specialist['fio']} (С {$specialist['first_activity']})\n";
                }
                $text .= "\n";
            }
        }
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pm_objects']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show personnel status menu for specific FN (from PM context)
     */
    private function showPersonnelStatus(string $chatId, string $fn): ServerResponse
    {
        error_log("ProjectManagementHandler::showPersonnelStatus called - fn: {$fn}");
        
        // Map fn codes to display names and database values
        $fnMapping = [
            'mobile' => ['name' => 'Мобильная бригада', 'db' => 'Мобильная бригада'],
            'shmr' => ['name' => 'Отдел ШМР', 'db' => 'Шеф-монтаж'],
            'pnr' => ['name' => 'Отдел ПНР', 'db' => 'Пусконаладка']
        ];
        
        $fnData = $fnMapping[$fn] ?? ['name' => 'Unknown', 'db' => ''];
        $fnName = $fnData['name'];
        $dbFn = $fnData['db'];
        
        // Get counts for each status
        $onsiteCount = 0;
        $freetimeCount = 0;
        
        try {
            error_log("ProjectManagementHandler::showPersonnelStatus - Getting onsite employees for: {$dbFn}");
            $onsiteEmployees = $this->personnelService->getOnsiteEmployees($dbFn);
            $onsiteCount = count($onsiteEmployees);
            error_log("ProjectManagementHandler::showPersonnelStatus - Onsite count: {$onsiteCount}");
        } catch (\Exception $e) {
            error_log("ProjectManagementHandler::showPersonnelStatus - ERROR getting onsite: " . $e->getMessage());
        }
        
        try {
            error_log("ProjectManagementHandler::showPersonnelStatus - Getting freetime employees for: {$dbFn}");
            $freetimeEmployees = $this->personnelService->getFreetimeEmployees($dbFn);
            $freetimeCount = count($freetimeEmployees);
            error_log("ProjectManagementHandler::showPersonnelStatus - Freetime count: {$freetimeCount}");
        } catch (\Exception $e) {
            error_log("ProjectManagementHandler::showPersonnelStatus - ERROR getting freetime: " . $e->getMessage());
        }
        
        $vacationCount = 0;
        $sickCount = 0;
        
        try {
            $vacationEmployees = $this->personnelService->getAbsenceEmployees($dbFn, 'Отпуск');
            $vacationCount = count($vacationEmployees);
        } catch (\Exception $e) {
            error_log("ProjectManagementHandler::showPersonnelStatus - ERROR getting vacation: " . $e->getMessage());
        }
        
        try {
            $sickEmployees = $this->personnelService->getAbsenceEmployees($dbFn, 'Больничный');
            $sickCount = count($sickEmployees);
        } catch (\Exception $e) {
            error_log("ProjectManagementHandler::showPersonnelStatus - ERROR getting sick: " . $e->getMessage());
        }
        
        $text = "📊 Статистика по направлению: {$fnName}";
        
        $keyboard = [
            [['text' => "🏗️ На объекте ({$onsiteCount})", 'callback_data' => "pm_pers_status_{$fn}_onsite"]],
            [['text' => "🏖️ Свободная вахта ({$freetimeCount})", 'callback_data' => "pm_pers_status_{$fn}_freetime"]],
            [['text' => "🌴 Отпуск ({$vacationCount})", 'callback_data' => "pm_pers_status_{$fn}_vacation"]],
            [['text' => "🏥 Больничный ({$sickCount})", 'callback_data' => "pm_pers_status_{$fn}_sick"]],
            [['text' => '⬅️ Назад', 'callback_data' => 'pm_personnel']]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show personnel status detail (onsite, freetime, etc.)
     */
    private function showPersonnelStatusDetail(string $chatId, string $fn, string $status): ServerResponse
    {
        error_log("ProjectManagementHandler::showPersonnelStatusDetail called - fn: {$fn}, status: {$status}");
        
        // Map fn codes
        $fnMapping = [
            'mobile' => ['name' => 'Мобильная бригада', 'db' => 'Мобильная бригада', 'icon' => '👷‍♂️'],
            'shmr' => ['name' => 'Отдел ШМР', 'db' => 'Шеф-монтаж', 'icon' => '🏭'],
            'pnr' => ['name' => 'Отдел ПНР', 'db' => 'Пусконаладка', 'icon' => '⚙️']
        ];
        
        $fnData = $fnMapping[$fn] ?? ['name' => 'Unknown', 'db' => '', 'icon' => '👷'];
        $fnName = $fnData['name'];
        $dbFn = $fnData['db'];
        
        if ($status === 'onsite') {
            $employees = $this->personnelService->getOnsiteEmployees($dbFn);
            $title = "🏗️ <b>Сотрудники на объекте</b>\n{$fnName}\n\n";
            
            if (empty($employees)) {
                $text = $title . "⚠️ <b>Нет данных</b>\n\nНа данный момент нет сотрудников на объекте.";
            } else {
                $text = $title . "Список сотрудников и длительность пребывания:\n\n";
                
                foreach ($employees as $emp) {
                    $daysWord = $this->personnelService->getDaysWord($emp['days']);
                    $text .= "{$emp['icon']} {$emp['fio']} - {$emp['days']} {$daysWord}\n";
                    $text .= "   📅 На объекте с: {$emp['start_date']}";
                    
                    if (!empty($emp['project_code'])) {
                        $text .= "\n   💼 Проект: {$emp['project_code']}";
                    }
                    
                    $text .= "\n\n";
                }
                
                $text .= "Всего: " . count($employees);
            }
        } elseif ($status === 'freetime') {
            $employees = $this->personnelService->getFreetimeEmployees($dbFn);
            $title = "🏖️ <b>Сотрудники на свободной вахте</b>\n{$fnName}\n\n";
            
            if (empty($employees)) {
                $text = $title . "⚠️ <b>Нет данных</b>\n\nНа данный момент нет сотрудников на свободной вахте.";
            } else {
                $text = $title . "Список сотрудников и длительность отдыха:\n\n";
                
                foreach ($employees as $emp) {
                    $daysWord = $this->personnelService->getDaysWord($emp['days']);
                    $text .= "{$emp['icon']} {$emp['fio']} - {$emp['days']} {$daysWord}\n";
                    $text .= "   📅 Свободная вахта: {$emp['last_work_date']}\n\n";
                }
                
                $text .= "Всего: " . count($employees);
            }
        } elseif ($status === 'vacation') {
            $employees = $this->personnelService->getAbsenceEmployees($dbFn, 'Отпуск');
            $title = "🌴 <b>Сотрудники в отпуске</b>\n{$fnName}\n\n";
            
            if (empty($employees)) {
                $text = $title . "На данный момент нет сотрудников в отпуске.";
            } else {
                $text = $title;
                
                foreach ($employees as $emp) {
                    $daysWord = $this->personnelService->getDaysWord($emp['days']);
                    $text .= "🏖 {$emp['fio']}\n";
                    $text .= "   📅 с {$emp['start_date']} по {$emp['end_date']} ({$emp['days']} {$daysWord})\n\n";
                }
                
                $text .= "Всего: " . count($employees);
            }
        } else { // sick
            $employees = $this->personnelService->getAbsenceEmployees($dbFn, 'Больничный');
            $title = "🏥 <b>Сотрудники на больничном</b>\n{$fnName}\n\n";
            
            if (empty($employees)) {
                $text = $title . "На данный момент нет сотрудников на больничном.";
            } else {
                $text = $title;
                
                foreach ($employees as $emp) {
                    $daysWord = $this->personnelService->getDaysWord($emp['days']);
                    $text .= "🏥 {$emp['fio']}\n";
                    $text .= "   📅 с {$emp['start_date']} по {$emp['end_date']} ({$emp['days']} {$daysWord})\n\n";
                }
                
                $text .= "Всего: " . count($employees);
            }
        }
        
        $keyboard = [
            [['text' => '⬅️ Назад', 'callback_data' => "pm_pers_{$fn}"]]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
}
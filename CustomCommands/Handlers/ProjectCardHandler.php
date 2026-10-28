<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

/**
 * Handler for project card management
 * Uses direct PDO connection to avoid initialization issues
 */
class ProjectCardHandler
{
    private MessageService $messageService;
    private ?\PDO $pdo = null;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }
    
    /**
     * Get PDO connection (lazy loading)
     */
    private function getPdo(): \PDO
    {
        if ($this->pdo === null) {
            $this->pdo = new \PDO(
                "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
                "WizardJIOCb",
                "7qQ#28051961",
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        }
        return $this->pdo;
    }

    public function canHandle(string $callbackData): bool
    {
        return str_starts_with($callbackData, 'proj_card_');
    }

    public function handle($callbackQuery, string $callbackData, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Routing
        if ($callbackData === 'proj_card_menu') {
            return $this->showMainMenu($chatId);
        }
        
        if ($callbackData === 'proj_card_list') {
            return $this->showProjectCardsList($chatId);
        }
        
        if (str_starts_with($callbackData, 'proj_card_view_')) {
            $projectCode = str_replace('proj_card_view_', '', $callbackData);
            return $this->viewProjectCard($chatId, $projectCode);
        }

        return $this->messageService->sendMessage($chatId, 'Неизвестная команда');
    }

    public function getPriority(): int
    {
        return 20;
    }

    /**
     * Show main menu
     */
    private function showMainMenu(string $chatId): ServerResponse
    {
        $text = '🗂️ <b>Карточки проектов</b>

Просмотр информации о проектах:';

        $keyboard = [
            [
                ['text' => '📋 Список проектов', 'callback_data' => 'proj_card_list']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_УправлениеПроектом']
            ]
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Send CSV template
     */
    private function sendTemplate(string $chatId): ServerResponse
    {
        try {
            $filePath = $this->getProjectCardService()->generateTemplate();
            
            $text = '📄 <b>Шаблон карточки проекта</b>

Откройте файл в Excel или Google Sheets.
Заполните данные и сохраните в формате CSV.
Затем отправьте файл обратно боту.

<b>Формат:</b>
• Разделитель: точка с запятой (;)
• Кодировка: UTF-8
• Первая строка - пример заполнения

<b>Новое:</b> В основной информации можно указать плановые даты завершения ШМР и ПНР (формат: дд.мм.гггг).';

            Request::sendDocument([
                'chat_id' => $chatId,
                'document' => Request::encodeFile($filePath),
                'caption' => $text,
                'parse_mode' => 'HTML'
            ]);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return Request::emptyResponse();
            
        } catch (\Exception $e) {
            error_log("ProjectCardHandler::sendTemplate error: " . $e->getMessage());
            return $this->messageService->sendMessage(
                $chatId,
                '❌ Ошибка при генерации шаблона: ' . $e->getMessage()
            );
        }
    }

    /**
     * Prompt file upload
     */
    private function promptFileUpload(string $chatId, Conversation $conversation): ServerResponse
    {
        $conversation->notes['awaiting_project_card_file'] = true;
        $conversation->update();

        $text = '📥 <b>Загрузка карточки проекта</b>

Отправьте заполненный CSV-файл с карточкой проекта.

Если у вас нет шаблона, сначала скачайте его кнопкой "📄 Скачать шаблон".';

        $keyboard = [
            [
                ['text' => '⬅️ Отмена', 'callback_data' => 'proj_card_menu']
            ]
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Process uploaded file
     */
    public function processUploadedFile(string $chatId, int $userId, string $filePath, Conversation $conversation): ServerResponse
    {
        try {
            $result = $this->getProjectCardService()->importFromCsv($filePath, $userId);
            
            unset($conversation->notes['awaiting_project_card_file']);
            $conversation->update();
            
            if ($result['success']) {
                $text = $result['message'];
                $keyboard = [
                    [
                        ['text' => '📋 Список карточек', 'callback_data' => 'proj_card_list']
                    ],
                    [
                        ['text' => '⬅️ В меню', 'callback_data' => 'proj_card_menu']
                    ]
                ];
            } else {
                $text = '❌ <b>Ошибка загрузки</b>

' . $result['message'];
                $keyboard = [
                    [
                        ['text' => '🔄 Попробовать снова', 'callback_data' => 'proj_card_upload']
                    ],
                    [
                        ['text' => '⬅️ В меню', 'callback_data' => 'proj_card_menu']
                    ]
                ];
            }
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectCardHandler::processUploadedFile error: " . $e->getMessage());
            
            unset($conversation->notes['awaiting_project_card_file']);
            $conversation->update();
            
            return $this->messageService->sendMessage(
                $chatId,
                '❌ Ошибка обработки файла: ' . $e->getMessage()
            );
        }
    }

    /**
     * Show list of project cards
     */
    private function showProjectCardsList(string $chatId): ServerResponse
    {
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->query("
                SELECT DISTINCT project_code, project_name 
                FROM projects 
                WHERE project_code IS NOT NULL AND project_code != ''
                ORDER BY project_code
            ");
            $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($projects)) {
                $text = '📋 <b>Список проектов</b>

Проекты пока не созданы.';
                
                $keyboard = [
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'proj_card_menu']
                    ]
                ];
                
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
            
            $text = '📋 <b>Список проектов</b>

Всего проектов: ' . count($projects) . '

Выберите проект для просмотра карточки:';
            
            $keyboard = [];
            foreach ($projects as $project) {
                $keyboard[] = [
                    ['text' => '🗂️ ' . $project['project_code'] . ' - ' . $project['project_name'], 
                     'callback_data' => 'proj_card_view_' . $project['project_code']]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_card_menu']
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectCardHandler::showProjectCardsList error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, 'Ошибка загрузки списка проектов');
        }
    }

    /**
     * View project card details
     */
    private function viewProjectCard(string $chatId, string $projectCode): ServerResponse
    {
        try {
            $pdo = $this->getPdo();
            
            // Get project from projects table
            $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_code = ? LIMIT 1");
            $stmt->execute([$projectCode]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$project) {
                return $this->messageService->sendMessage($chatId, 'Проект не найден');
            }
            
            // Get additional data from project_cards if exists
            $stmt = $pdo->prepare("
                SELECT pc.*, c.name as customer_name, bu.name as business_unit_name
                FROM project_cards pc
                LEFT JOIN customers c ON pc.customer_id = c.id
                LEFT JOIN business_units bu ON pc.business_unit_id = bu.id
                WHERE pc.project_code = ?
            ");
            $stmt->execute([$projectCode]);
            $cardData = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Get actual hours from tabel
            $stmt = $pdo->prepare("
                SELECT SUM(hours_worked) as total_hours 
                FROM ozna_tabel 
                WHERE project_code = ?
            ");
            $stmt->execute([$projectCode]);
            $actualData = $stmt->fetch(\PDO::FETCH_ASSOC);
            $actualHours = $actualData['total_hours'] ?? 0;
            
            $text = '🗂️ <b>Карточка проекта</b>

';
            $text .= '<b>Шифр:</b> ' . $project['project_code'] . '
';
            $text .= '<b>Название:</b> ' . $project['project_name'] . '

';
            
            if ($cardData) {
                if ($cardData['customer_name']) {
                    $text .= '<b>Заказчик:</b> ' . $cardData['customer_name'] . '
';
                }
                if ($cardData['business_unit_name']) {
                    $text .= '<b>БН:</b> ' . $cardData['business_unit_name'] . '
';
                }
                
                // Плановые даты завершения — отдельный блок
                if (!empty($cardData['smr_planned_completion']) || !empty($cardData['pnr_planned_completion'])) {
                    $text .= '
📅 <b>План завершения:</b>
';
                    if (!empty($cardData['smr_planned_completion'])) {
                        $text .= '  • ШМР: ' . date('d.m.Y', strtotime($cardData['smr_planned_completion'])) . '
';
                    }
                    if (!empty($cardData['pnr_planned_completion'])) {
                        $text .= '  • ПНР: ' . date('d.m.Y', strtotime($cardData['pnr_planned_completion'])) . '
';
                    }
                }
                
                $text .= '
📊 <b>Трудоёмкость:</b>
';
                if ($cardData['smr_planned_hours']) {
                    $text .= '  • ШМР План: ' . $cardData['smr_planned_hours'] . ' ч.
';
                }
                if ($cardData['pnr_planned_hours']) {
                    $text .= '  • ПНР План: ' . $cardData['pnr_planned_hours'] . ' ч.
';
                }
            }
            
            $text .= '  • Факт (всего): ' . number_format($actualHours, 2) . ' ч.
';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_card_list']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectCardHandler::viewProjectCard error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, 'Ошибка загрузки карточки проекта');
        }
    }
}

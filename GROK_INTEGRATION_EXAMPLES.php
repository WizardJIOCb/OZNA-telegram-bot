<?php

/**
 * Grok AI Integration Examples
 * 
 * Примеры интеграции Grok AI в существующие handlers
 * Скопируй нужные фрагменты в свой код
 */

// ============================================================================
// ПРИМЕР 1: Добавить AI-анализ в отчеты по проектам (ProjectHandler)
// ============================================================================

/**
 * В файле: CustomCommands/Handlers/ProjectHandler.php
 * 
 * После метода handleProjectReport() добавь новый метод:
 */

use Longman\TelegramBot\Commands\SystemCommands\Services\GrokService;

class ProjectHandler implements CallbackHandlerInterface
{
    
    /**
     * Handle Grok AI analysis for project report
     */
    private function handleProjectGrokAnalysis(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // Get project and dates from conversation
        $projectCode = $conversation->notes['project_report_code'] ?? '';
        $startDate = $conversation->notes['project_report_start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $conversation->notes['project_report_end_date'] ?? date('Y-m-d');
        
        if (empty($projectCode)) {
            $text = "❌ Проект не выбран";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Show loading message
        $text = "🤖 <b>Grok AI анализирует проект {$projectCode}</b>\n\n";
        $text .= "⏳ Это займет 10-15 секунд...\n";
        $text .= "Анализирую период: {$startDate} - {$endDate}";
        
        $this->messageService->sendMessage($chatId, $text);
        
        // Initialize Grok
        $grokService = new GrokService($this->databaseService);
        
        // Get AI analysis
        $result = $grokService->analyzeProjectReport($projectCode, $startDate, $endDate);
        
        if ($result && $result['success']) {
            $text = "🤖 <b>AI-Анализ проекта {$projectCode}</b>\n\n";
            $text .= $result['analysis'];
            
            // Add export button and back button
            $keyboard = [
                [
                    ['text' => '📊 Экспорт CSV', 'callback_data' => "proj_report_{$projectCode}"]
                ],
                [
                    ['text' => '🔄 Другой проект', 'callback_data' => 'category_Проекты_ОтчетПоПроекту']
                ],
                [
                    ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
                ]
            ];
        } else {
            $text = "❌ <b>Ошибка AI-анализа</b>\n\n";
            $text .= $result['message'] ?? 'Не удалось выполнить анализ. Попробуйте позже.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчетПоПроекту']
                ]
            ];
        }
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Modify existing handleProjectReport() method
     * Add AI analysis button to the menu
     */
    private function showProjectReportMenu(string $chatId, string $projectCode): ServerResponse
    {
        $keyboard = [
            [
                ['text' => '📅 Сегодня', 'callback_data' => "proj_report_today_{$projectCode}"],
                ['text' => '📅 Вчера', 'callback_data' => "proj_report_yesterday_{$projectCode}"]
            ],
            [
                ['text' => '📅 Последние 7 дней', 'callback_data' => "proj_report_week_{$projectCode}"],
                ['text' => '📅 Последние 30 дней', 'callback_data' => "proj_report_month_{$projectCode}"]
            ],
            [
                ['text' => '📅 Весь период', 'callback_data' => "proj_report_all_{$projectCode}"]
            ],
            [
                ['text' => '📆 Выбранный период', 'callback_data' => "proj_report_custom_{$projectCode}"]
            ],
            // NEW: Add AI analysis button
            [
                ['text' => '🤖 AI-Анализ проекта', 'callback_data' => "proj_grok_analyze_{$projectCode}"]
            ],
            [
                ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
            ]
        ];
        
        $text = "📋 <b>Отчёт по проекту {$projectCode}</b>\n\n";
        $text .= "Выберите период для отчёта или получите AI-анализ проекта:";
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
}

// Update handle() method to route to new handler
public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
{
    
    // NEW: Handle Grok AI analysis
    if (str_starts_with($category, 'Проекты_GrokAnalyze_')) {
        return $this->handleProjectGrokAnalysis($callbackQuery, $category, $conversation);
    }
    
    // Also handle short format
    if (str_starts_with($category, 'proj_grok_analyze_')) {
        // Extract project code
        $projectCode = str_replace('proj_grok_analyze_', '', $category);
        $conversation->notes['project_report_code'] = $projectCode;
        $conversation->update();
        
        return $this->handleProjectGrokAnalysis($callbackQuery, $category, $conversation);
    }
    
    // ... rest of existing code ...
}


// ============================================================================
// ПРИМЕР 2: Добавить анализ паттернов в заметки (NotesHandler)
// ============================================================================

/**
 * В файле: CustomCommands/Handlers/NotesHandler.php
 */

class NotesHandler implements CallbackHandlerInterface
{
    
    /**
     * Show notes menu with AI analysis option
     */
    private function showNotesMenu(string $chatId, string $projectCode): ServerResponse
    {
        $keyboard = [
            [
                ['text' => '✍️ Оставить заметку', 'callback_data' => 'notes_create_' . $this->contextMenu]
            ],
            [
                ['text' => '📋 Посмотреть заметки', 'callback_data' => 'notes_view_list']
            ],
            // NEW: Add AI pattern analysis
            [
                ['text' => '💡 AI-анализ паттернов', 'callback_data' => 'notes_grok_patterns']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => $this->getBackCallback()]
            ]
        ];
        
        $text = "📌 <b>Заметки по проекту</b>\n\n";
        $text .= "Что вы хотите сделать?\n\n";
        $text .= "💡 <i>AI-анализ поможет найти повторяющиеся проблемы</i>";
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle AI pattern analysis for notes
     */
    private function handleGrokPatternAnalysis(CallbackQuery $callbackQuery, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // Get current project
        $projectCode = $conversation->notes['current_project'] ?? '';
        
        if (empty($projectCode)) {
            $text = "❌ Проект не определен";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Show loading
        $text = "💡 <b>AI анализирует заметки проекта {$projectCode}</b>\n\n";
        $text .= "⏳ Анализирую последние 30 дней...\n";
        
        $this->messageService->sendMessage($chatId, $text);
        
        // Get Grok analysis
        $databaseService = new DatabaseService();
        $grokService = new GrokService($databaseService);
        
        $result = $grokService->analyzeNotesPatterns($projectCode, 30);
        
        if ($result && $result['success']) {
            $text = "💡 <b>AI-Анализ заметок проекта {$projectCode}</b>\n\n";
            $text .= "📊 Проанализировано заметок: {$result['notes_analyzed']}\n\n";
            $text .= $result['patterns'];
            
            $keyboard = [
                [
                    ['text' => '📋 Все заметки', 'callback_data' => 'notes_view_list']
                ],
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'notes_main']
                ]
            ];
        } else {
            $text = "❌ " . ($result['message'] ?? 'Ошибка анализа заметок');
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'notes_main']
                ]
            ];
        }
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
}

// Update handle() to route pattern analysis
public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
{
    
    // NEW: Handle Grok pattern analysis
    if ($category === 'notes_grok_patterns' || str_ends_with($category, '_grok_patterns')) {
        return $this->handleGrokPatternAnalysis($callbackQuery, $conversation);
    }
    
    // ... rest of code ...
}


// ============================================================================
// ПРИМЕР 3: Добавить прогноз загрузки в главное меню для супервайзеров
// ============================================================================

/**
 * В файле: CustomCommands/Handlers/MainMenuHandler.php
 */

class MainMenuHandler implements CallbackHandlerInterface
{
    /**
     * Show main menu based on user access level
     */
    private function showMainMenu(string $chatId, int $userId): ServerResponse
    {
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        if ($userAccessLevel >= 2) { // Supervisor and above
            $keyboard = [
                [
                    ['text' => '📊 Табель', 'callback_data' => 'category_Табель']
                ],
                [
                    ['text' => '👤 Профиль', 'callback_data' => 'category_Профиль']
                ],
                // NEW: Add Grok AI for supervisors
                [
                    ['text' => '🤖 Grok AI', 'callback_data' => 'category_Grok']
                ],
                [
                    ['text' => '⚙️ Админка', 'callback_data' => 'category_Админка']
                ],
                [
                    ['text' => '❓ Помощь', 'callback_data' => 'category_Помощь']
                ]
            ];
            
            $text = "<b>🏠 Главное меню</b>\n\n";
            $text .= "(для вызова этого меню в любой момент отправьте /start)\n\n";
            $text .= "🤖 <i>Новое: AI-анализ проектов и прогнозы!</i>";
        }
        // ... rest of code ...
    }
}


// ============================================================================
// ПРИМЕР 4: Executive Summary при экспорте CSV
// ============================================================================

/**
 * После генерации CSV отчета, предложи AI-summary
 * В ProjectHandler после отправки CSV файла:
 */

private function sendProjectReport(/* ... */): ServerResponse
{
    // ... existing CSV generation ...
    
    // Send CSV file
    $response = Request::sendDocument([
        'chat_id' => $chatId,
        'document' => Request::encodeFile($csvFilePath),
        'caption' => $caption
    ]);
    
    // NEW: Offer AI summary
    $text = "📊 <b>Отчёт отправлен!</b>\n\n";
    $text .= "Хотите получить краткий executive summary от AI?";
    
    $keyboard = [
        [
            ['text' => '🤖 Получить AI-summary', 'callback_data' => "proj_grok_summary_{$projectCode}"]
        ],
        [
            ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
        ]
    ];
    
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}

// Handler for summary
private function handleGrokSummary(CallbackQuery $callbackQuery, string $projectCode): ServerResponse
{
    $chatId = $callbackQuery->getMessage()->getChat()->getId();
    
    $text = "🤖 <b>Генерирую executive summary...</b>\n";
    $this->messageService->sendMessage($chatId, $text);
    
    // Get report data
    $reportData = $this->getProjectReportData($projectCode, $startDate, $endDate);
    
    // Get AI summary
    $grokService = new GrokService($this->databaseService);
    $result = $grokService->generateExecutiveSummary($reportData);
    
    if ($result && $result['success']) {
        $text = "📊 <b>Executive Summary - {$projectCode}</b>\n\n";
        $text .= $result['summary'];
        
        return $this->messageService->sendMessage($chatId, $text);
    }
    
    return $this->messageService->sendMessage($chatId, "❌ Ошибка генерации summary");
}


// ============================================================================
// ПРИМЕР 5: Напоминание об AI-анализе после создания заметки
// ============================================================================

/**
 * В NotesHandler после успешного создания заметки
 */

private function handleNoteTextInput(/* ... */): ServerResponse
{
    // ... existing code to save note ...
    
    // After successful save
    $text = "✅ <b>Заметка создана!</b>\n\n";
    $text .= "💡 <i>Совет: Используй \"AI-анализ паттернов\" чтобы найти повторяющиеся проблемы</i>";
    
    $keyboard = [
        [
            ['text' => '💡 Запустить AI-анализ', 'callback_data' => 'notes_grok_patterns']
        ],
        [
            ['text' => '✍️ Создать еще заметку', 'callback_data' => 'notes_create_' . $this->contextMenu']
        ],
        [
            ['text' => '⬅️ Назад', 'callback_data' => 'notes_main']
        ]
    ];
    
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}


// ============================================================================
// ЗАКЛЮЧЕНИЕ
// ============================================================================

/**
 * Это примеры интеграции Grok AI в существующий бот.
 * 
 * Главные принципы:
 * 1. Добавляй кнопки AI там где есть данные для анализа
 * 2. Показывай loading перед запросом к Grok (10-15 сек)
 * 3. Обрабатывай ошибки gracefully
 * 4. Предлагай AI-фичи ненавязчиво
 * 
 * Приоритетные интеграции:
 * ✅ Отчеты по проектам - САМОЕ ПОЛЕЗНОЕ
 * ✅ Анализ заметок - ОЧЕНЬ ПОЛЕЗНОЕ
 * ✅ Executive Summary - ДЛЯ РУКОВОДСТВА
 * 
 * Хочешь, чтобы я добавил эти интеграции прямо в твои handlers?
 * Скажи слово и я начну! 🚀
 */

?>

<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\GrokService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for Grok AI features
 */
class GrokHandler implements CallbackHandlerInterface
{
    private GrokService $grokService;
    private DatabaseService $databaseService;
    private MessageService $messageService;
    private AdminService $adminService;
    private array $config;
    
    public function __construct(
        DatabaseService $databaseService,
        MessageService $messageService,
        AdminService $adminService
    ) {
        $this->databaseService = $databaseService;
        $this->messageService = $messageService;
        $this->adminService = $adminService;
        $this->grokService = new GrokService($databaseService);
        $this->config = $this->loadConfig();
    }
    
    private function loadConfig(): array
    {
        $configPath = __DIR__ . '/../../grok_config.php';
        if (file_exists($configPath)) {
            return require $configPath;
        }
        return ['features' => [], 'min_access_level' => 2];
    }
    
    public function canHandle(string $callbackData): bool
    {
        return str_starts_with($callbackData, 'category_Grok') || 
               str_starts_with($callbackData, 'grok_');
    }
    
    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Check access level
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        $minLevel = $this->config['min_access_level'] ?? 2;
        
        if ($userAccessLevel < $minLevel) {
            $text = "🤖 <b>Grok AI</b>\n\n";
            $text .= "❌ Доступ к AI-функциям доступен только для супервайзеров и выше.\n";
            $text .= "Ваш уровень: " . $this->adminService->getAccessLevelName($userAccessLevel);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Route to appropriate handler
        if ($category === 'Grok' || $category === 'Grok_Main') {
            return $this->showMainMenu($chatId);
        }
        
        // Анализ проекта - пока заглушка
        if ($category === 'Grok_AnalyzeProject') {
            return $this->showProjectAnalysisDemo($chatId);
        }
        
        // Анализ заметок - пока заглушка
        if ($category === 'Grok_AnalyzeNotes') {
            return $this->showNotesAnalysisDemo($chatId);
        }
        
        // AI-ассистент
        if ($category === 'Grok_Assistant') {
            return $this->handleAIAssistant($callbackQuery, $conversation);
        }
        
        $text = "❌ Неизвестная команда Grok AI";
        return $this->messageService->sendMessage($chatId, $text);
    }
    
    public function getPriority(): int
    {
        return 26; // After Admin, Personnel handlers
    }
    
    /**
     * Show main Grok AI menu
     */
    private function showMainMenu(string $chatId): ServerResponse
    {
        $keyboard = [];
        
        // Для начала показываем только основные функции
        $keyboard[] = [
            ['text' => '📊 Анализ проекта', 'callback_data' => 'category_Grok_AnalyzeProject']
        ];
        
        $keyboard[] = [
            ['text' => '💡 Паттерны в заметках', 'callback_data' => 'category_Grok_AnalyzeNotes']
        ];
        
        $keyboard[] = [
            ['text' => '❓ AI-Ассистент', 'callback_data' => 'category_Grok_Assistant']
        ];
        
        $keyboard[] = [
            ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
        ];
        
        $text = "🤖 <b>AI-помощник (Grok)</b>\n\n";
        $text .= "Используй искусственный интеллект для:\n\n";
        $text .= "📊 <b>Анализ проекта</b> - Получи инсайты и рекомендации\n";
        $text .= "💡 <b>Паттерны в заметках</b> - Найди повторяющиеся проблемы\n";
        $text .= "❓ <b>AI-Ассистент</b> - Задай вопрос о проектах\n\n";
        $text .= "⚡ <i>Powered by xAI Grok</i>";
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle project analysis request
     */
    private function handleProjectAnalysis(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // For demo purposes, analyze last 30 days
        // In production, you'd let user select project and dates
        
        $text = "🤖 <b>Анализ проекта с помощью Grok AI</b>\n\n";
        $text .= "⏳ Анализирую данные...\n";
        $text .= "Это может занять 10-15 секунд.";
        
        // Send initial message
        $this->messageService->sendMessage($chatId, $text);
        
        // Example: analyze a project
        $projectCode = 'XXX'; // You can get from conversation state
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));
        
        $result = $this->grokService->analyzeProjectReport($projectCode, $startDate, $endDate);
        
        if ($result && $result['success']) {
            $text = "🤖 <b>Анализ проекта {$projectCode}</b>\n\n";
            $text .= $result['analysis'];
            
            $keyboard = [
                [
                    ['text' => '🔄 Другой проект', 'callback_data' => 'category_Grok_SelectProject']
                ],
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                ]
            ];
        } else {
            $text = "❌ <b>Ошибка анализа</b>\n\n";
            $text .= $result['message'] ?? 'Не удалось выполнить анализ';
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                ]
            ];
        }
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle notes pattern analysis
     */
    private function handleNotesAnalysis(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        $text = "🤖 <b>Анализ паттернов в заметках</b>\n\n";
        $text .= "⏳ Анализирую заметки за последние 30 дней...\n";
        
        $this->messageService->sendMessage($chatId, $text);
        
        $projectCode = 'XXX'; // Get from state
        
        $result = $this->grokService->analyzeNotesPatterns($projectCode, 30);
        
        if ($result && $result['success']) {
            $text = "💡 <b>Паттерны в заметках проекта {$projectCode}</b>\n\n";
            $text .= "Проанализировано заметок: {$result['notes_analyzed']}\n\n";
            $text .= $result['patterns'];
            
            $keyboard = [
                [
                    ['text' => '🔄 Другой проект', 'callback_data' => 'category_Grok_SelectNotesProject']
                ],
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                ]
            ];
        } else {
            $text = "❌ " . ($result['message'] ?? 'Ошибка анализа');
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                ]
            ];
        }
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle resource load prediction
     */
    private function handleResourcePrediction(CallbackQuery $callbackQuery, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Get user's functional direction
        $userData = $this->databaseService->getUserData($userId);
        $functionalDirection = $userData['functional_direction'] ?? 'ПНР';
        
        $text = "🤖 <b>Прогноз загрузки</b>\n\n";
        $text .= "⏳ Анализирую загрузку {$functionalDirection} за последние 60 дней...\n";
        
        $this->messageService->sendMessage($chatId, $text);
        
        $result = $this->grokService->predictResourceLoad($functionalDirection, 14);
        
        if ($result && $result['success']) {
            $text = "🎯 <b>Прогноз загрузки {$functionalDirection}</b>\n\n";
            $text .= "На {$result['days_ahead']} дней вперед:\n\n";
            $text .= $result['prediction'];
            
            $keyboard = [
                [
                    ['text' => '🔄 Обновить прогноз', 'callback_data' => 'category_Grok_PredictLoad']
                ],
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                ]
            ];
        } else {
            $text = "❌ " . ($result['message'] ?? 'Ошибка прогнозирования');
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                ]
            ];
        }
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle AI Assistant
     */
    private function handleAIAssistant(CallbackQuery $callbackQuery, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        $text = "❓ <b>AI-Ассистент</b>\n\n";
        $text .= "Задай мне ЛЮБОЙ вопрос о твоих проектах, табеле или процессах!\n\n";
        $text .= "<b>Примеры вопросов:</b>\n";
        $text .= "• Сколько часов я отработал на проекте XXX?\n";
        $text .= "• Какие проблемы были на объекте YYY?\n";
        $text .= "• Кто работал на электрооборудовании в октябре?\n";
        $text .= "• Какие заметки есть по проекту ZZZ?\n";
        $text .= "• Сколько времени потрачено на наладку?\n";
        $text .= "• Какие частые проблемы на монтаже?\n\n";
        $text .= "💡 <i>Grok AI умеет анализировать данные и отвечать на сложные вопросы!</i>\n\n";
        $text .= "Напиши свой вопрос текстом:";
        
        // Set conversation state to wait for question
        $conversation->notes['awaiting_grok_question'] = true;
        $conversation->update();
        
        $keyboard = [
            [
                ['text' => '⬅️ Отмена', 'callback_data' => 'category_Grok_Main']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Process user's question (called from GenericmessageCommand)
     */
    public function processQuestion(string $question, string $chatId, string $projectCode = ''): ServerResponse
    {
        error_log("GrokHandler: Processing question from chat {$chatId}: '{$question}'");
        
        $text = "🤖 <b>AI-Ассистент</b>\n\n";
        $text .= "⏳ Думаю над ответом...\n";
        
        $this->messageService->sendMessage($chatId, $text);
        error_log("GrokHandler: Sent 'thinking' message to chat {$chatId}");
        
        try {
            error_log("GrokHandler: Calling GrokService->answerProjectQuestion()");
            $result = $this->grokService->answerProjectQuestion($question, $projectCode);
            error_log("GrokHandler: GrokService returned: " . json_encode($result));
            
            if ($result && isset($result['success']) && $result['success']) {
                error_log("GrokHandler: Success! Answer length: " . strlen($result['answer'] ?? ''));
                
                $text = "💬 <b>Ответ AI-Ассистента:</b>\n\n";
                $text .= $result['answer'];
                
                $keyboard = [
                    [
                        ['text' => '❓ Задать еще вопрос', 'callback_data' => 'category_Grok_Assistant']
                    ],
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                    ]
                ];
            } else {
                error_log("GrokHandler: Failed to get answer. Result: " . json_encode($result));
                
                $errorMsg = $result['message'] ?? 'Неизвестная ошибка';
                $text = "❌ <b>Не удалось получить ответ</b>\n\n";
                $text .= "Причина: {$errorMsg}\n\n";
                $text .= "Попробуй переформулировать вопрос или обратись к администратору.";
                
                $keyboard = [
                    [
                        ['text' => '🔄 Попробовать снова', 'callback_data' => 'category_Grok_Assistant']
                    ],
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                    ]
                ];
            }
        } catch (\Exception $e) {
            error_log("GrokHandler: EXCEPTION in processQuestion: " . $e->getMessage());
            error_log("GrokHandler: Exception trace: " . $e->getTraceAsString());
            
            $text = "❌ <b>Ошибка обработки запроса</b>\n\n";
            $text .= "Произошла техническая ошибка. Попробуй позже или обратись к администратору.\n\n";
            $text .= "<i>Код ошибки: " . substr($e->getMessage(), 0, 100) . "</i>";
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                ]
            ];
        }
        
        error_log("GrokHandler: Sending final response to chat {$chatId}");
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Demo: Project analysis
     */
    private function showProjectAnalysisDemo(string $chatId): ServerResponse
    {
        $text = "📊 <b>Анализ проекта - DEMO</b>\n\n";
        $text .= "🚀 Это демонстрация того, как будет работать AI-анализ!\n\n";
        
        $text .= "<b>Пример анализа проекта XXX:</b>\n\n";
        
        $text .= "📊 <b>ОСНОВНЫЕ НАБЛЮДЕНИЯ:</b>\n";
        $text .= "✅ За 30 дней выполнено 245 часов работ\n";
        $text .= "✅ Наибольшая активность: монтаж электрооборудования (85ч)\n";
        $text .= "✅ Участвовало 8 сотрудников\n\n";
        
        $text .= "⚠️ <b>ВЫЯВЛЕННЫЕ ПРОБЛЕМЫ:</b>\n";
        $text .= "- Высокий процент простоев (12%)\n";
        $text .= "- Неравномерное распределение загрузки\n";
        $text .= "- Задержки на этапе наладки\n\n";
        
        $text .= "🎯 <b>РЕКОМЕНДАЦИИ:</b>\n";
        $text .= "1️⃣ Пересмотреть график монтажа\n";
        $text .= "2️⃣ Добавить 2 монтажников на пиковый период\n";
        $text .= "3️⃣ Провести доп. инструктаж по наладке\n\n";
        
        $text .= "📅 <b>ПРОГНОЗ:</b>\n";
        $text .= "При текущих темпах завершение через 14-18 дней\n\n";
        
        $text .= "🚧 <i>Это DEMO. После настройки API ключа Grok будет анализировать реальные данные!</i>";
        
        $keyboard = [
            [
                ['text' => '🔄 Другой проект', 'callback_data' => 'category_Grok_AnalyzeProject']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Demo: Notes pattern analysis
     */
    private function showNotesAnalysisDemo(string $chatId): ServerResponse
    {
        $text = "💡 <b>Паттерны в заметках - DEMO</b>\n\n";
        $text .= "🚀 Это демонстрация анализа заметок!\n\n";
        
        $text .= "<b>Пример анализа (30 дней, 47 заметок):</b>\n\n";
        
        $text .= "🔄 <b>ПОВТОРЯЮЩИЕСЯ ПРОБЛЕМЫ:</b>\n";
        $text .= "1️⃣ Несоответствие монтажных отверстий (5 случаев)\n";
        $text .= "   → Рекомендация: Проверять чертежи ДО начала работ\n\n";
        $text .= "2️⃣ Отсутствие кабельной продукции (3 случая)\n";
        $text .= "   → Рекомендация: Улучшить планирование поставок\n\n";
        
        $text .= "🚨 <b>КРИТИЧЕСКИЕ ЗАМЕЧАНИЯ:</b>\n";
        $text .= "- Повреждение кабеля при монтаже (19.10)\n";
        $text .= "- Проблемы с заземлением шкафа (22.10)\n\n";
        
        $text .= "✅ <b>ПОЗИТИВНЫЕ МОМЕНТЫ:</b>\n";
        $text .= "- Успешное испытание оборудования\n";
        $text .= "- Хорошая координация между бригадами\n\n";
        
        $text .= "🎯 <b>ПРЕДОТВРАЩЕНИЕ:</b>\n";
        $text .= "Рекомендую создать чек-лист проверки перед монтажом\n\n";
        
        $text .= "🚧 <i>Это DEMO. После настройки API ключа Grok будет анализировать реальные заметки!</i>";
        
        $keyboard = [
            [
                ['text' => '🔄 Другой проект', 'callback_data' => 'category_Grok_AnalyzeNotes']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
}

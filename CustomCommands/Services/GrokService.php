<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * GrokService - Integration with xAI Grok API
 * 
 * Provides AI-powered features:
 * - Report analysis and insights
 * - Notes pattern recognition
 * - Resource load prediction
 * - Automated report generation
 * - Natural language queries
 */
class GrokService
{
    private string $apiKey;
    private string $apiUrl = 'https://api.x.ai/v1/chat/completions';
    private string $model = 'grok-beta';
    private DatabaseService $databaseService;
    
    public function __construct(DatabaseService $databaseService, string $apiKey = '')
    {
        $this->databaseService = $databaseService;
        $this->apiKey = $apiKey ?: $this->getApiKeyFromConfig();
    }
    
    /**
     * Get Grok API key from config or environment
     */
    private function getApiKeyFromConfig(): string
    {
        // Try to get from environment first
        $envKey = getenv('GROK_API_KEY');
        if ($envKey) {
            return $envKey;
        }
        
        // Try to get from config file
        $configPath = __DIR__ . '/../../grok_config.php';
        if (file_exists($configPath)) {
            $config = require $configPath;
            return $config['grok_api_key'] ?? '';
        }
        
        return '';
    }
    
    /**
     * Make API request to Grok
     */
    private function makeRequest(array $messages, float $temperature = 0.7, int $maxTokens = 2000): ?array
    {
        if (empty($this->apiKey)) {
            error_log("GrokService: API key not configured");
            return null;
        }
        
        error_log("GrokService: Making API request to Grok (temp={$temperature}, max_tokens={$maxTokens})");
        error_log("GrokService: Messages count: " . count($messages));
        
        $data = [
            'messages' => $messages,
            'model' => $this->model,
            'stream' => false,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];
        
        error_log("GrokService: Request payload: " . json_encode($data, JSON_UNESCAPED_UNICODE));
        
        $ch = \curl_init($this->apiUrl);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_POST, true);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        \curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        // Set timeouts: 30 seconds connection, 60 seconds total
        \curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        \curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        error_log("GrokService: Executing curl request...");
        $startTime = microtime(true);
        $response = \curl_exec($ch);
        $duration = round(microtime(true) - $startTime, 2);
        
        $httpCode = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = \curl_error($ch);
        \curl_close($ch);
        
        error_log("GrokService: Request completed in {$duration}s with HTTP code {$httpCode}");
        
        if ($curlError) {
            error_log("GrokService: CURL error: {$curlError}");
            return null;
        }
        
        if ($httpCode !== 200) {
            error_log("GrokService: API request failed with code $httpCode");
            error_log("GrokService: Response body: " . substr($response, 0, 500));
            return null;
        }
        
        error_log("GrokService: Response received, length: " . strlen($response) . " bytes");
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("GrokService: JSON decode error: " . json_last_error_msg());
            error_log("GrokService: Raw response: " . substr($response, 0, 500));
            return null;
        }
        
        error_log("GrokService: Successfully decoded JSON response");
        return $decoded;
    }
    
    /**
     * 1. ANALYZE PROJECT REPORT
     * Analyzes project activities and provides insights
     * 
     * @param string $projectCode Project code
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return array|null Analysis result with insights
     */
    public function analyzeProjectReport(string $projectCode, string $startDate, string $endDate): ?array
    {
        // Get project data from database
        $reportData = $this->getProjectReportData($projectCode, $startDate, $endDate);
        
        if (empty($reportData)) {
            return [
                'success' => false,
                'message' => 'Нет данных для анализа'
            ];
        }
        
        // Prepare data summary for Grok
        $dataSummary = $this->formatReportDataForAnalysis($reportData);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'Ты - опытный инженер-аналитик в области пусконаладки и шеф-монтажа промышленного оборудования. Анализируй данные объективно и давай практичные рекомендации на русском языке.'
            ],
            [
                'role' => 'user',
                'content' => "Проанализируй отчет по проекту {$projectCode} за период {$startDate} - {$endDate}:\n\n{$dataSummary}\n\nДай краткий анализ (до 500 слов):\n1. Основные наблюдения\n2. Выявленные проблемы\n3. Рекомендации для улучшения\n4. Прогноз завершения работ"
            ]
        ];
        
        $response = $this->makeRequest($messages, 0.5, 1500);
        
        if (!$response || !isset($response['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'message' => 'Ошибка при получении анализа от Grok'
            ];
        }
        
        return [
            'success' => true,
            'analysis' => $response['choices'][0]['message']['content'],
            'usage' => $response['usage'] ?? null
        ];
    }
    
    /**
     * 2. ANALYZE NOTES PATTERNS
     * Finds patterns and recurring issues in project notes
     * 
     * @param string $projectCode Project code
     * @param int $daysBack How many days to look back
     * @return array|null Pattern analysis
     */
    public function analyzeNotesPatterns(string $projectCode, int $daysBack = 30): ?array
    {
        // Get notes from database
        $notes = $this->getProjectNotes($projectCode, $daysBack);
        
        if (empty($notes)) {
            return [
                'success' => false,
                'message' => 'Нет заметок для анализа'
            ];
        }
        
        // Format notes for analysis
        $notesText = $this->formatNotesForAnalysis($notes);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'Ты - эксперт по анализу производственных проблем. Выявляй повторяющиеся паттерны, критические замечания и системные проблемы в заметках инженеров.'
            ],
            [
                'role' => 'user',
                'content' => "Проанализируй заметки по проекту {$projectCode} за последние {$daysBack} дней:\n\n{$notesText}\n\nВыдели:\n1. Повторяющиеся проблемы (если есть)\n2. Критические замечания\n3. Позитивные моменты\n4. Рекомендации по предотвращению проблем"
            ]
        ];
        
        $response = $this->makeRequest($messages, 0.6, 1500);
        
        if (!$response || !isset($response['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'message' => 'Ошибка при анализе заметок'
            ];
        }
        
        return [
            'success' => true,
            'patterns' => $response['choices'][0]['message']['content'],
            'notes_analyzed' => count($notes)
        ];
    }
    
    /**
     * 3. PREDICT RESOURCE LOAD
     * Predicts future resource requirements based on historical data
     * 
     * @param string $functionalDirection Functional direction (ФН)
     * @param int $daysAhead Days to predict ahead
     * @return array|null Prediction result
     */
    public function predictResourceLoad(string $functionalDirection, int $daysAhead = 14): ?array
    {
        // Get historical workload data
        $historicalData = $this->getHistoricalWorkload($functionalDirection, 60);
        
        if (empty($historicalData)) {
            return [
                'success' => false,
                'message' => 'Недостаточно исторических данных'
            ];
        }
        
        $dataSummary = $this->formatWorkloadDataForPrediction($historicalData);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'Ты - специалист по прогнозированию загрузки производственных ресурсов. Анализируй исторические данные и предсказывай будущие потребности.'
            ],
            [
                'role' => 'user',
                'content' => "На основе исторических данных о загрузке функционального направления '{$functionalDirection}':\n\n{$dataSummary}\n\nСпрогнозируй на {$daysAhead} дней вперед:\n1. Ожидаемую загрузку (низкая/средняя/высокая)\n2. Пиковые периоды\n3. Возможные риски нехватки ресурсов\n4. Рекомендации по распределению задач"
            ]
        ];
        
        $response = $this->makeRequest($messages, 0.4, 1200);
        
        if (!$response || !isset($response['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'message' => 'Ошибка при прогнозировании'
            ];
        }
        
        return [
            'success' => true,
            'prediction' => $response['choices'][0]['message']['content'],
            'days_ahead' => $daysAhead
        ];
    }
    
    /**
     * 4. GENERATE EXECUTIVE SUMMARY
     * Creates executive summary report from raw data
     * 
     * @param array $reportData Raw report data (from CSV export)
     * @return array|null Generated summary
     */
    public function generateExecutiveSummary(array $reportData): ?array
    {
        if (empty($reportData)) {
            return [
                'success' => false,
                'message' => 'Нет данных для отчета'
            ];
        }
        
        $dataSummary = json_encode($reportData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'Ты - менеджер проектов, который создает краткие executive summary для руководства. Превращай технические данные в понятные бизнес-метрики.'
            ],
            [
                'role' => 'user',
                'content' => "Создай executive summary на основе этих данных:\n\n{$dataSummary}\n\nФормат ответа:\n📊 ОСНОВНЫЕ ПОКАЗАТЕЛИ\n✅ ДОСТИЖЕНИЯ\n⚠️ ПРОБЛЕМЫ\n🎯 РЕКОМЕНДАЦИИ\n\nМаксимум 300 слов, только ключевые моменты для руководства."
            ]
        ];
        
        $response = $this->makeRequest($messages, 0.3, 1000);
        
        if (!$response || !isset($response['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'message' => 'Ошибка при генерации отчета'
            ];
        }
        
        return [
            'success' => true,
            'summary' => $response['choices'][0]['message']['content']
        ];
    }
    
    /**
     * 5. ANSWER PROJECT QUESTION
     * Natural language query about project data
     * 
     * @param string $question User's question
     * @param string $projectCode Project code (optional)
     * @return array|null Answer
     */
    public function answerProjectQuestion(string $question, string $projectCode = ''): ?array
    {
        error_log("GrokService: answerProjectQuestion() called with question: '{$question}', project: '{$projectCode}'");
        
        // Get relevant context
        $context = '';
        if (!empty($projectCode)) {
            error_log("GrokService: Getting project context for {$projectCode}");
            $projectData = $this->getProjectContext($projectCode);
            $context = "Контекст по проекту {$projectCode}:\n{$projectData}\n\n";
            error_log("GrokService: Context length: " . strlen($context) . " chars");
        }
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'Ты - AI-ассистент системы управления проектами. Отвечай на вопросы пользователей о проектах, табелях, заметках и процессах. Используй данные из контекста. Отвечай КРАТКО и ПО ДЕЛУ на русском языке.'
            ],
            [
                'role' => 'user',
                'content' => $context . "Вопрос: {$question}"
            ]
        ];
        
        error_log("GrokService: Prepared messages for API, calling makeRequest()");
        
        try {
            $response = $this->makeRequest($messages, 0.7, 800);
            
            if (!$response) {
                error_log("GrokService: makeRequest() returned null");
                return [
                    'success' => false,
                    'message' => 'Не удалось получить ответ от API'
                ];
            }
            
            error_log("GrokService: API response structure: " . json_encode(array_keys($response)));
            
            if (!isset($response['choices'][0]['message']['content'])) {
                error_log("GrokService: Response missing expected structure");
                error_log("GrokService: Full response: " . json_encode($response, JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => 'Неожиданный формат ответа от API'
                ];
            }
            
            $answer = $response['choices'][0]['message']['content'];
            error_log("GrokService: Got answer, length: " . strlen($answer) . " chars");
            error_log("GrokService: Answer preview: " . substr($answer, 0, 100) . "...");
            
            return [
                'success' => true,
                'answer' => $answer
            ];
        } catch (\Exception $e) {
            error_log("GrokService: EXCEPTION in answerProjectQuestion: " . $e->getMessage());
            error_log("GrokService: Exception trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Ошибка при обработке запроса: ' . $e->getMessage()
            ];
        }
    }
    
    // ==================== HELPER METHODS ====================
    
    private function getProjectReportData(string $projectCode, string $startDate, string $endDate): array
    {
        try {
            $pdo = $this->databaseService->getConnection();
            
            $sql = "SELECT 
                        user_fio,
                        activity_type,
                        hours,
                        activity_date,
                        custom_description
                    FROM tabel 
                    WHERE project_code = :project_code 
                    AND activity_date BETWEEN :start_date AND :end_date
                    ORDER BY activity_date DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':project_code' => $projectCode,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("GrokService: Error fetching project data: " . $e->getMessage());
            return [];
        }
    }
    
    private function formatReportDataForAnalysis(array $data): string
    {
        $summary = "Всего записей: " . count($data) . "\n\n";
        
        // Group by activity type
        $byActivity = [];
        $totalHours = 0;
        foreach ($data as $row) {
            $activity = $row['activity_type'];
            if (!isset($byActivity[$activity])) {
                $byActivity[$activity] = ['count' => 0, 'hours' => 0];
            }
            $byActivity[$activity]['count']++;
            $byActivity[$activity]['hours'] += floatval($row['hours']);
            $totalHours += floatval($row['hours']);
        }
        
        $summary .= "Общее время: {$totalHours} часов\n\nПо видам деятельности:\n";
        foreach ($byActivity as $activity => $stats) {
            $summary .= "- {$activity}: {$stats['hours']} ч ({$stats['count']} записей)\n";
        }
        
        return $summary;
    }
    
    private function getProjectNotes(string $projectCode, int $daysBack): array
    {
        try {
            $pdo = $this->databaseService->getConnection();
            
            $sql = "SELECT 
                        note_text,
                        user_fio,
                        stage,
                        created_at,
                        status
                    FROM project_notes 
                    WHERE project_id = (SELECT id FROM project WHERE code = :project_code)
                    AND created_at >= DATE_SUB(NOW(), INTERVAL :days_back DAY)
                    ORDER BY created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':project_code' => $projectCode,
                ':days_back' => $daysBack
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("GrokService: Error fetching notes: " . $e->getMessage());
            return [];
        }
    }
    
    private function formatNotesForAnalysis(array $notes): string
    {
        $formatted = "";
        foreach ($notes as $note) {
            $status = $note['status'] === 'active' ? '🟢' : '🔴';
            $formatted .= "{$status} [{$note['created_at']}] {$note['user_fio']} ({$note['stage']}):\n";
            $formatted .= "{$note['note_text']}\n\n";
        }
        return $formatted;
    }
    
    private function getHistoricalWorkload(string $functionalDirection, int $daysBack): array
    {
        try {
            $pdo = $this->databaseService->getConnection();
            
            $sql = "SELECT 
                        DATE(activity_date) as date,
                        COUNT(DISTINCT user_id) as workers_count,
                        SUM(hours) as total_hours,
                        COUNT(*) as activities_count
                    FROM tabel t
                    JOIN user u ON t.user_id = u.user_id
                    WHERE u.functional_direction = :fn
                    AND t.activity_date >= DATE_SUB(CURDATE(), INTERVAL :days_back DAY)
                    GROUP BY DATE(activity_date)
                    ORDER BY date ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':fn' => $functionalDirection,
                ':days_back' => $daysBack
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("GrokService: Error fetching workload: " . $e->getMessage());
            return [];
        }
    }
    
    private function formatWorkloadDataForPrediction(array $data): string
    {
        $formatted = "Исторические данные загрузки:\n\n";
        foreach ($data as $row) {
            $formatted .= "{$row['date']}: {$row['workers_count']} чел, {$row['total_hours']} ч, {$row['activities_count']} активностей\n";
        }
        return $formatted;
    }
    
    private function getProjectContext(string $projectCode): string
    {
        // Get recent activities and notes
        $recentData = $this->getProjectReportData($projectCode, date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
        $recentNotes = $this->getProjectNotes($projectCode, 7);
        
        $context = "Последние 7 дней:\n";
        $context .= "Активностей: " . count($recentData) . "\n";
        $context .= "Заметок: " . count($recentNotes) . "\n";
        
        return $context;
    }
}

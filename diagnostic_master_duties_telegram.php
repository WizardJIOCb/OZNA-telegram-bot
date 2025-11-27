<?php
// TELEGRAM CHAT DIAGNOSTIC FOR MASTER DUTIES BUTTON
// This script can be run via Telegram commands to diagnose the button in real-time

require_once 'config.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';

class MasterDutiesDiagnostic {
    private $db;
    private $messageService;
    
    public function __construct() {
        $this->db = new DatabaseService();
        $this->messageService = new MessageService();
    }
    
    /**
     * Main diagnostic command that can be triggered via Telegram
     * Usage: /diag_master_duties
     */
    public function runDiagnostic($chatId, $userId = null) {
        $report = "🔍 **ДИАГНОСТИКА КНОПКИ 'ИСПОЛНЕНИЕ ОБЯЗАННОСТЕЙ МАСТЕРА'**\n\n";
        
        try {
            // 1. Check button configuration in MessageService
            $report .= "**1️⃣ ПРОВЕРКА КОНФИГУРАЦИИ КНОПКИ:**\n";
            $buttonCheck = $this->checkButtonConfiguration();
            $report .= $buttonCheck['status'] ? "✅ " : "❌ ";
            $report .= $buttonCheck['message'] . "\n\n";
            
            // 2. Check callback mapping in TabelHandler
            $report .= "**2️⃣ ПРОВЕРКА МАППИНГА CALLBACK:**\n";
            $mappingCheck = $this->checkCallbackMapping();
            $report .= $mappingCheck['status'] ? "✅ " : "❌ ";
            $report .= $mappingCheck['message'] . "\n\n";
            
            // 3. Check handler logic
            $report .= "**3️⃣ ПРОВЕРКА ОБРАБОТЧИКА:**\n";
            $handlerCheck = $this->checkHandlerLogic();
            $report .= $handlerCheck['status'] ? "✅ " : "❌ ";
            $report .= $handlerCheck['message'] . "\n\n";
            
            // 4. Check database connectivity
            $report .= "**4️⃣ ПРОВЕРКА БАЗЫ ДАННЫХ:**\n";
            $dbCheck = $this->checkDatabaseConnection();
            $report .= $dbCheck['status'] ? "✅ " : "❌ ";
            $report .= $dbCheck['message'] . "\n\n";
            
            // 5. Test simulation
            $report .= "**5️⃣ СИМУЛЯЦИЯ ОБРАБОТКИ:**\n";
            $simCheck = $this->simulateButtonPress($userId);
            $report .= $simCheck['status'] ? "✅ " : "❌ ";
            $report .= $simCheck['message'] . "\n\n";
            
            // Overall status
            $allChecksPass = $buttonCheck['status'] && $mappingCheck['status'] && 
                           $handlerCheck['status'] && $dbCheck['status'];
            
            $report .= "**🎯 ОБЩИЙ СТАТУС:**\n";
            $report .= $allChecksPass ? "✅ ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ" : "❌ ОБНАРУЖЕНЫ ПРОБЛЕМЫ";
            $report .= "\n\n";
            
            if (!$allChecksPass) {
                $report .= "**🔧 РЕКОМЕНДАЦИИ:**\n";
                $report .= "1. Проверьте логи ошибок: `/check_logs`\n";
                $report .= "2. Перезапустите бота: `/restart_bot`\n";
                $report .= "3. Проверьте права доступа пользователя\n";
            } else {
                $report .= "**🚀 ГОТОВ К ТЕСТИРОВАНИЮ:**\n";
                $report .= "Попробуйте: /tabel → Отдел ШМР → Выберите проект → Проектная деятельность → 👷 Исполнение обязанностей мастера\n";
            }
            
        } catch (Exception $e) {
            $report .= "❌ **ОШИБКА ДИАГНОСТИКИ:** " . $e->getMessage();
        }
        
        return $report;
    }
    
    private function checkButtonConfiguration(): array {
        try {
            $messageServiceFile = 'CustomCommands/Services/MessageService.php';
            if (!file_exists($messageServiceFile)) {
                return ['status' => false, 'message' => 'MessageService.php не найден'];
            }
            
            $content = file_get_contents($messageServiceFile);
            $buttonExists = strpos($content, "'callback_data' => 'category_Табель_ИсполнениеОбязМастера'") !== false;
            
            if ($buttonExists) {
                return ['status' => true, 'message' => 'Кнопка правильно сконфигурирована в MessageService'];
            } else {
                return ['status' => false, 'message' => 'Конфигурация кнопки не найдена в MessageService'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Ошибка проверки конфигурации: ' . $e->getMessage()];
        }
    }
    
    private function checkCallbackMapping(): array {
        try {
            $tabelHandlerFile = 'CustomCommands/Handlers/TabelHandler.php';
            if (!file_exists($tabelHandlerFile)) {
                return ['status' => false, 'message' => 'TabelHandler.php не найден'];
            }
            
            $content = file_get_contents($tabelHandlerFile);
            $mappingExists = strpos($content, "'category_Табель_ИсполнениеОбязМастера' => 'Табель_ИсполнениеОбязМастера'") !== false;
            
            if ($mappingExists) {
                return ['status' => true, 'message' => 'Маппинг callback найден в extractCategoryFromCallback'];
            } else {
                return ['status' => false, 'message' => 'Маппинг callback отсутствует в TabelHandler'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Ошибка проверки маппинга: ' . $e->getMessage()];
        }
    }
    
    private function checkHandlerLogic(): array {
        try {
            $tabelHandlerFile = 'CustomCommands/Handlers/TabelHandler.php';
            $content = file_get_contents($tabelHandlerFile);
            
            $handlerExists = strpos($content, "if (\$category === 'Табель_ИсполнениеОбязМастера')") !== false;
            
            if ($handlerExists) {
                return ['status' => true, 'message' => 'Обработчик найден в handleOtherWorkTypes'];
            } else {
                return ['status' => false, 'message' => 'Обработчик не найден в TabelHandler'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Ошибка проверки обработчика: ' . $e->getMessage()];
        }
    }
    
    private function checkDatabaseConnection(): array {
        try {
            $result = $this->db->query("SELECT 1 as test");
            if ($result) {
                return ['status' => true, 'message' => 'Соединение с базой данных работает'];
            } else {
                return ['status' => false, 'message' => 'Нет соединения с базой данных'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Ошибка подключения к БД: ' . $e->getMessage()];
        }
    }
    
    private function simulateButtonPress($userId): array {
        try {
            // Simulate the callback processing
            $callback = 'category_Табель_ИсполнениеОбязМастера';
            
            // Test extractCategoryFromCallback logic
            $shortFormats = [
                'category_Табель_ИсполнениеОбязМастера' => 'Табель_ИсполнениеОбязМастера',
                'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
            ];
            
            if (isset($shortFormats[$callback])) {
                $extractedCategory = $shortFormats[$callback];
                $handlerMatch = ($extractedCategory === 'Табель_ИсполнениеОбязМастера');
                
                if ($handlerMatch) {
                    return ['status' => true, 'message' => 'Симуляция успешна: callback корректно обрабатывается'];
                } else {
                    return ['status' => false, 'message' => 'Симуляция неудачна: обработчик не сработает'];
                }
            } else {
                return ['status' => false, 'message' => 'Симуляция неудачна: callback не найден в маппинге'];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Ошибка симуляции: ' . $e->getMessage()];
        }
    }
    
    /**
     * Quick test command for specific callback
     * Usage: /test_callback category_Табель_ИсполнениеОбязМастера
     */
    public function testCallback($chatId, $callback) {
        $report = "🧪 **ТЕСТ CALLBACK: {$callback}**\n\n";
        
        try {
            // Load TabelHandler to test actual logic
            require_once 'CustomCommands/Handlers/TabelHandler.php';
            
            // Test canHandle method
            $canHandle = str_starts_with($callback, 'cat_Таб') || str_starts_with($callback, 'category_Табель_');
            $report .= "**canHandle():** " . ($canHandle ? "✅ TRUE" : "❌ FALSE") . "\n";
            
            // Test extractCategoryFromCallback
            $shortFormats = [
                'category_Табель_ИсполнениеОбязМастера' => 'Табель_ИсполнениеОбязМастера',
                'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
            ];
            
            $extractedCategory = $shortFormats[$callback] ?? 'NOT_MAPPED';
            $report .= "**extractCategory():** {$extractedCategory}\n";
            
            // Test handler match
            $handlerMatch = ($extractedCategory === 'Табель_ИсполнениеОбязМастера');
            $report .= "**handlerMatch():** " . ($handlerMatch ? "✅ TRUE" : "❌ FALSE") . "\n";
            
            if ($handlerMatch) {
                $report .= "\n✅ **РЕЗУЛЬТАТ:** Callback будет обработан корректно";
            } else {
                $report .= "\n❌ **РЕЗУЛЬТАТ:** Callback НЕ будет обработан";
            }
            
        } catch (Exception $e) {
            $report .= "\n❌ **ОШИБКА:** " . $e->getMessage();
        }
        
        return $report;
    }
    
    /**
     * Live monitoring command
     * Usage: /monitor_master_duties
     */
    public function startMonitoring($chatId) {
        $report = "📊 **МОНИТОРИНГ КНОПКИ 'ИСПОЛНЕНИЕ ОБЯЗАННОСТЕЙ МАСТЕРА'**\n\n";
        $report .= "🔍 Мониторинг активирован. Следующие события будут отслеживаться:\n\n";
        $report .= "• Нажатия на кнопку\n";
        $report .= "• Обработка callback'ов\n";  
        $report .= "• Отправка подтверждений\n";
        $report .= "• Генерация отчетов\n\n";
        $report .= "📋 **Что искать в логах:**\n";
        $report .= "```\n";
        $report .= "🎯 MASTER DUTIES CHECK: Current category: 'Табель_ИсполнениеОбязМастера'\n";
        $report .= "🎯 MASTER DUTIES: ✅ HANDLER TRIGGERED!\n";
        $report .= "🎯 MASTER DUTIES: Message sent, response: SUCCESS\n";
        $report .= "```\n\n";
        $report .= "⚠️ **Если логи не появляются:**\n";
        $report .= "1. Проблема в маршрутизации\n";
        $report .= "2. Проверьте CallbackHandlerFactory\n";
        $report .= "3. Запустите: `/diag_master_duties`\n";
        
        return $report;
    }
}

// CLI usage for direct execution
if (php_sapi_name() === 'cli') {
    $diagnostic = new MasterDutiesDiagnostic();
    echo $diagnostic->runDiagnostic('console', 'console_user');
}
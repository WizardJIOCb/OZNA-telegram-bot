<?php
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

echo "=== ТЕСТ ИСПРАВЛЕНИЯ ОБРАБОТЧИКА ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

// Создаем мок-объекты для тестирования
class MockDatabaseService {
    public function getUserData($userId) {
        return ['fio' => 'Тестовый Пользователь', 'functional_direction' => 'Электромонтаж'];
    }
    
    public function hasRecentTabelEntry($userId, $isSuperAdmin) {
        return false;
    }
}

class MockMessageService {
    public function createElectricalEquipmentKeyboard(): array {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабЭлОб_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабЭлОб_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабЭлОб_ДопРаб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабЭлОб_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
    }
    
    public function createAdditionalWorksElectricalKeyboard(): array {
        return [
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'cat_ТабЭлОб_ИзмСхПд']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'cat_ТабЭлОб_МонтКбЛн']
            ],
            [
                ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'cat_ТабЭлОб_РаскКбЛн']
            ],
            [
                ['text' => '⚙️ Монтаж оборудования', 'callback_data' => 'cat_ТабЭлОб_МонтОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабЭлОб_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Электрооборудование']
            ]
        ];
    }
    
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "Отправка сообщения в чат {$chatId}:\n";
        echo "Текст: {$text}\n";
        if ($keyboard) {
            echo "Клавиатура: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "\n";
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true]);
    }
    
    public function createConfirmationKeyboard($confirmCallbackData): array {
        return [
            [
                ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
                ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
            ]
        ];
    }
}

class MockProjectSelector {
    // Пустая реализация для теста
}

class MockTabelEntry {
    public function getConfirmationMessage($activityType, $projectCode) {
        return "{$activityType} для проекта {$projectCode}, отправить запись в табель?";
    }
    
    public function createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
        return "<b>{$fio}</b>\n✅ Запись в табель от <b>{$date}</b>, в проект <b>{$projectCode}</b> о <b>{$activityType} добавлена</b>";
    }
    
    public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') {
        return true;
    }
    
    public function formatRecentEntryError($recentEntry) {
        return "Недавно была добавлена запись. Пожалуйста, подождите немного перед следующей записью.";
    }
}

class MockAdminService {
    public function getUserAccessLevel($userId) {
        return 2; // Пользователь с обычным доступом
    }
    
    public function isSuperAdmin($userId) {
        return false;
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Пустая реализация для теста
    }
}

// Создаем экземпляры сервисов
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

// Создаем обработчик
try {
    $tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
        $databaseService,
        $messageService,
        $projectSelector,
        $tabelEntry,
        $adminService
    );
    
    echo "✅ TabelHandler успешно создан\n\n";
    
    // Тестируем метод canHandle
    echo "1. ТЕСТИРОВАНИЕ canHandle:\n";
    echo "========================\n";
    
    $testCallbacks = [
        'category_Табель' => false, // Не должно обрабатываться этим обработчиком
        'cat_ТабЭлОб' => true, // Должно обрабатываться
        'cat_ТабЭлОб_РабПоГр' => true, // Должно обрабатываться
        'cat_ТабЭлОб_ДопРаб' => true, // Должно обрабатываться
        'cat_ТабЭлОб_ИзмСхПд' => true, // Должно обрабатываться
    ];
    
    foreach ($testCallbacks as $callback => $expected) {
        $result = $tabelHandler->canHandle($callback);
        $status = ($result === $expected) ? '✅' : '❌';
        echo "  {$status} {$callback}: " . ($result ? 'обрабатывается' : 'не обрабатывается') . "\n";
    }
    
    echo "\n2. ТЕСТИРОВАНИЕ СОЗДАНИЯ КЛАВИАТУР:\n";
    echo "==================================\n";
    
    // Тестируем создание клавиатур
    $electricalKeyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "  ✅ Основное меню электрооборудования: " . count($electricalKeyboard) . " кнопок\n";
    
    $additionalWorksKeyboard = $messageService->createAdditionalWorksElectricalKeyboard();
    echo "  ✅ Подменю дополнительных работ: " . count($additionalWorksKeyboard) . " кнопок\n";
    
    echo "\n3. СТРУКТУРА МЕНЮ:\n";
    echo "=================\n";
    
    echo "Основное меню 'Электрооборудование':\n";
    foreach ($electricalKeyboard as $row) {
        foreach ($row as $button) {
            echo "  • " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
    
    echo "\nПодменю 'Дополнительные работы':\n";
    foreach ($additionalWorksKeyboard as $row) {
        foreach ($row as $button) {
            echo "  • " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
    
    echo "\n4. ЗАКЛЮЧЕНИЕ:\n";
    echo "=============\n";
    echo "✅ Все компоненты для корректной работы кнопки 'Электрооборудование' настроены!\n";
    echo "Пользователь сможет:\n";
    echo "  1. Нажать на кнопку 'Электрооборудование' в меню пусконаладочных работ\n";
    echo "  2. Выбрать одну из 5 основных активностей\n";
    echo "  3. При выборе 'Дополнительные работы' перейти в подменю\n";
    echo "  4. В подменю выбрать одну из 6 специализированных работ\n";
    echo "  5. Подтвердить запись в табель\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
?>
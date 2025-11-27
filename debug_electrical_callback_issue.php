<?php
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С КНОПКОЙ 'ЭЛЕКТРООБОРУДОВАНИЕ' ===\n\n";

// Создаем мок-объекты
class MockDatabaseService {
    public function getUserData($userId) {
        return ['fio' => 'Тестовый Пользователь', 'functional_direction' => 'Электромонтаж'];
    }
    
    public function hasRecentTabelEntry($userId, $isSuperAdmin) {
        return false;
    }
}

class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "ОТПРАВКА СООБЩЕНИЯ:\n";
        echo "Chat ID: {$chatId}\n";
        echo "Текст: {$text}\n";
        if ($keyboard) {
            echo "Клавиатура: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
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

// Создаем экземпляры сервисов
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

// Создаем обработчик
$tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
    $databaseService,
    $messageService,
    $projectSelector,
    $tabelEntry,
    $adminService
);

echo "1. ТЕСТИРОВАНИЕ МЕТОДА canHandle:\n";
echo "================================\n";

$testCallbacks = [
    'cat_ТабЭлОб' => true,
    'category_Табель_Электрооборудование' => true,
    'cat_ТабЭлОб_РабПоГр' => true,
    'cat_ТабЭлОб_ДопРаб' => true
];

foreach ($testCallbacks as $callback => $expected) {
    $result = $tabelHandler->canHandle($callback);
    $status = ($result === $expected) ? '✅' : '❌';
    echo "  {$status} {$callback}: " . ($result ? 'обрабатывается' : 'не обрабатывается') . "\n";
}

echo "\n2. ТЕСТИРОВАНИЕ МЕТОДА extractCategoryFromCallback:\n";
echo "==================================================\n";

// Используем Reflection для доступа к приватному методу
$reflection = new ReflectionClass($tabelHandler);
$extractMethod = $reflection->getMethod('extractCategoryFromCallback');
$extractMethod->setAccessible(true);

$testExtractions = [
    'cat_ТабЭлОб' => 'Табель_Электрооборудование',
    'cat_ТабЭлОб_РабПоГр' => 'Табель_РабПоГр_ЭлектрОб',
    'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб'
];

foreach ($testExtractions as $callback => $expected) {
    $actual = $extractMethod->invokeArgs($tabelHandler, [$callback]);
    $status = ($actual === $expected) ? '✅' : '❌';
    echo "  {$status} {$callback} → {$actual}\n";
    if ($actual !== $expected) {
        echo "     ОЖИДАЛОСЬ: {$expected}\n";
    }
}

echo "\n3. СИМУЛЯЦИЯ ПОЛНОГО ПОТОКА ОБРАБОТКИ:\n";
echo "=====================================\n";

// Симулируем callback от Telegram
$callbackData = 'cat_ТабЭлОб';
echo "Получен callback от Telegram: {$callbackData}\n";

// Проверяем canHandle
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler->canHandle('{$callbackData}'): " . ($canHandle ? 'ДА ✅' : 'НЕТ ❌') . "\n";

if ($canHandle) {
    // Извлекаем категорию
    $category = $extractMethod->invokeArgs($tabelHandler, [$callbackData]);
    echo "Извлеченная категория: {$category}\n";
    
    // Проверяем, какие условия сработают в handleOtherWorkTypes
    echo "\nПРОВЕРКА УСЛОВИЙ В handleOtherWorkTypes:\n";
    
    $conditions = [
        "\$category === 'ТабЭлОб'" => ($category === 'ТабЭлОб'),
        "\$category === 'Табель_Электрооборудование'" => ($category === 'Табель_Электрооборудование')
    ];
    
    foreach ($conditions as $condition => $result) {
        $status = $result ? 'СРАБАТЫВАЕТ ✅' : 'НЕ СРАБАТЫВАЕТ ❌';
        echo "  {$condition}: {$status}\n";
    }
    
    if ($category === 'Табель_Электрооборудование') {
        echo "\n✅ ДОЛЖНО ВЫЗВАТЬСЯ: handleElectricalEquipmentMenu()\n";
        echo "   Это должно показать меню с активностями электрооборудования\n";
    } else {
        echo "\n❌ НЕПРАВИЛЬНАЯ КАТЕГОРИЯ, не сработает нужное условие\n";
    }
} else {
    echo "❌ TabelHandler не может обработать этот callback!\n";
}

echo "\n4. ЗАКЛЮЧЕНИЕ:\n";
echo "=============\n";
echo "Если все тесты прошли успешно, то проблема может быть в другом месте.\n";
echo "Возможные причины:\n";
echo "  1. Проблемы с маршрутизацией в методе handle()\n";
echo "  2. Проблемы с уровнем доступа пользователя\n";
echo "  3. Проблемы с состоянием разговора (conversation)\n";
echo "  4. Проблемы с кэшированием или устаревшим кодом\n";
?>
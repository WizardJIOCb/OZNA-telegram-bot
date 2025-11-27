<?php
// Тестовый скрипт для проверки работы основных ветвей таблицы

require_once 'vendor/autoload.php';
require_once 'config.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

// Создаем мock объекты для тестирования
class MockDatabaseService extends DatabaseService {
    public function __construct() {}
    
    public function getUserData($userId) {
        return [
            'fio' => 'Тестовый Пользователь',
            'functional_direction' => 'Мобильная бригада'
        ];
    }
    
    public function getUserAccessLevel($userId) {
        return 1; // User level
    }
    
    public function isSuperAdmin($userId) {
        return false;
    }
    
    public function hasRecentTabelEntry($userId, $isSuperAdmin) {
        return false;
    }
}

class MockMessageService extends MessageService {
    public function __construct() {}
    
    public function sendMessage($chatId, $text, $keyboard = null, $parseMode = 'HTML') {
        echo "Отправка сообщения в чат {$chatId}:\n";
        echo "Текст: {$text}\n";
        if ($keyboard) {
            echo "Клавиатура: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "------------------------\n";
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true]);
    }
    
    public function createWorkTypeKeyboard() {
        return [
            [
                ['text' => '💼 Мобильная бригада', 'callback_data' => 'category_Табель_Мобильная_Бригада']
            ],
            [
                ['text' => '🔧 Отдел ШМР', 'callback_data' => 'category_Табель_Шеф_монтаж']
            ],
            [
                ['text' => '⚙️ Отдел ПНР', 'callback_data' => 'category_Табель_Пусконаладка']
            ],
            [
                ['text' => '📊 Текущая деятельность', 'callback_data' => 'category_Табель_Текущая']
            ]
        ];
    }
    
    public function createDateSelectionKeyboard($prefix = 'category_Табель') {
        return [
            [
                ['text' => '📅 Сегодня', 'callback_data' => "{$prefix}_Сегодня"]
            ],
            [
                ['text' => '📆 Вчера', 'callback_data' => "{$prefix}_Вчера"]
            ],
            [
                ['text' => '📆 Позавчера', 'callback_data' => "{$prefix}_Позавчера"]
            ],
            [
                ['text' => '📆 Другая дата', 'callback_data' => "{$prefix}_ДругаяДата"]
            ]
        ];
    }
    
    public function createProjectSelectionKeyboard() {
        return [
            [
                ['text' => 'Проект 1', 'callback_data' => 'category_Табель_ШифрПроекта_Проект1']
            ],
            [
                ['text' => 'Проект 2', 'callback_data' => 'category_Табель_ШифрПроекта_Проект2']
            ]
        ];
    }
    
    public function createMobileBrigadeKeyboard() {
        return [
            [
                ['text' => '🚗 Подготовка к служебной поездке', 'callback_data' => 'category_Табель_Подготовка']
            ],
            [
                ['text' => '🚗 Дорога', 'callback_data' => 'category_Табель_Дорога']
            ]
        ];
    }
    
    public function createCommissioningKeyboard() {
        return [
            [
                ['text' => '⚙️ Пусконаладочные работы', 'callback_data' => 'category_Табель_ПусконаладочныеРаботы']
            ]
        ];
    }
    
    public function createCurrentActivityKeyboard() {
        return [
            [
                ['text' => '📋 Участие в ЗПИ', 'callback_data' => 'category_Табель_УчастиеВЗПИ']
            ]
        ];
    }
    
    public function createConfirmationKeyboard($confirmCallbackData) {
        return [
            [
                ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
                ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
            ]
        ];
    }
}

class MockProjectSelector extends ProjectSelector {
    public function __construct() {}
    
    public function isDateSelection($category) {
        return strpos($category, '_Сегодня') !== false || 
               strpos($category, '_Вчера') !== false || 
               strpos($category, '_Позавчера') !== false ||
               strpos($category, '_ДругаяДата') !== false;
    }
    
    public function isProjectSelection($category) {
        return strpos($category, 'ШифрПроекта') !== false;
    }
    
    public function extractDate($category) {
        $dateMap = [
            'category_Табель_Сегодня' => date('d.m.Y'),
            'category_Табель_Мобильная_Бригада_Сегодня' => date('d.m.Y'),
            'category_Табель_ШМ_Сегодня' => date('d.m.Y'),
            'category_Табель_ПНЛ_Сегодня' => date('d.m.Y'),
            'category_Табель_Текущая_Сегодня' => date('d.m.Y')
        ];
        
        return $dateMap[$category] ?? date('d.m.Y');
    }
    
    public function extractProjectCode($category) {
        if (strpos($category, 'ШифрПроекта_Проект1') !== false) {
            return 'Проект1';
        }
        if (strpos($category, 'ШифрПроекта_Проект2') !== false) {
            return 'Проект2';
        }
        return 'Unknown';
    }
}

class MockTabelEntry extends TabelEntry {
    public function __construct() {}
    
    public function getConfirmationMessage($activityType, $projectCode) {
        return "Подтвердите запись: {$activityType} для проекта {$projectCode}";
    }
    
    public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') {
        echo "Добавлена запись в табель:\n";
        echo "Пользователь: {$fio} (ID: {$userId})\n";
        echo "Дата: {$date}\n";
        echo "Проект: {$projectCode}\n";
        echo "Активность: {$activityType}\n";
        if ($idleReason) {
            echo "Причина простоя: {$idleReason}\n";
        }
        echo "------------------------\n";
        return true;
    }
    
    public function createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
        return "Запись в табель от {$date} добавлена";
    }
}

class MockAdminService extends AdminService {
    public function __construct() {}
    
    public function getUserAccessLevel($userId) {
        return 1; // User level
    }
    
    public function isSuperAdmin($userId) {
        return false;
    }
}

class MockCallbackQuery extends CallbackQuery {
    private $data;
    private $message;
    
    public function __construct($data) {
        parent::__construct([]);
        $this->data = $data;
        $this->message = new MockMessage();
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getFrom() {
        return new MockUser();
    }
}

class MockMessage extends Message {
    public function __construct() {
        parent::__construct([]);
    }
    
    public function getChat() {
        return new MockChat();
    }
}

class MockChat extends Chat {
    public function __construct() {
        parent::__construct([]);
    }
    
    public function getId() {
        return 123456789;
    }
}

class MockUser extends User {
    public function __construct() {
        parent::__construct([]);
    }
    
    public function getId() {
        return 987654321;
    }
    
    public function getFirstName() {
        return 'Тестовый';
    }
}

// Тестирование основных ветвей таблицы
echo "Тестирование основных ветвей таблицы\n";
echo "====================================\n\n";

// Создаем экземпляр TabelHandler
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Создаем mock объекты
$conversation = new Conversation(987654321, 123456789);

// Тест 1: Мобильная бригада
echo "Тест 1: Мобильная бригада\n";
$callbackQuery1 = new MockCallbackQuery('category_Табель_Мобильная_Бригада');
$result1 = $tabelHandler->handle($callbackQuery1, 'Табель_Мобильная_Бригада', $conversation);
echo "Результат: " . ($result1->isOk() ? "УСПЕХ" : "ОШИБКА") . "\n\n";

// Тест 2: Отдел ШМР
echo "Тест 2: Отдел ШМР\n";
$callbackQuery2 = new MockCallbackQuery('category_Табель_Шеф_монтаж');
$result2 = $tabelHandler->handle($callbackQuery2, 'Табель_Шеф_монтаж', $conversation);
echo "Результат: " . ($result2->isOk() ? "УСПЕХ" : "ОШИБКА") . "\n\n";

// Тест 3: Отдел ПНР
echo "Тест 3: Отдел ПНР\n";
$callbackQuery3 = new MockCallbackQuery('category_Табель_Пусконаладка');
$result3 = $tabelHandler->handle($callbackQuery3, 'Табель_Пусконаладка', $conversation);
echo "Результат: " . ($result3->isOk() ? "УСПЕХ" : "ОШИБКА") . "\n\n";

// Тест 4: Текущая деятельность
echo "Тест 4: Текущая деятельность\n";
$callbackQuery4 = new MockCallbackQuery('category_Табель_Текущая');
$result4 = $tabelHandler->handle($callbackQuery4, 'Табель_Текущая', $conversation);
echo "Результат: " . ($result4->isOk() ? "УСПЕХ" : "ОШИБКА") . "\n\n";

echo "Тестирование завершено!\n";
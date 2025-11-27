<?php
// Тестовый скрипт для проверки формирования клавиатуры

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Начинаем тест формирования клавиатуры для заявок на доступ...\n";

try {
    // Создаем экземпляры сервисов
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Получаем все заявки
    $requests = $adminService->getAllAccessRequests();
    
    echo "Количество заявок: " . count($requests) . "\n";
    
    // Имитируем формирование клавиатуры как в showAccessRequestsList
    $keyboard = [];
    
    if (empty($requests)) {
        echo "Формируем клавиатуру для пустого списка заявок\n";
        $keyboard = [
            [
                ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
            ]
        ];
    } else {
        echo "Формируем клавиатуру для непустого списка заявок\n";
        
        // Создаем клавиатуру с кнопками для каждой заявки
        $keyboard = [];
        foreach ($requests as $request) {
            $userName = $request['user_name'] ?? 'Неизвестный пользователь';
            $userId = $request['user_id'];
            
            // Ограничиваем длину имени пользователя для кнопки
            if (strlen($userName) > 20) {
                $userName = substr($userName, 0, 17) . '...';
            }
            
            $keyboard[] = [
                ['text' => $userName, 'callback_data' => "category_Админка_Заявки_Просмотр_{$userId}"]
            ];
        }
        
        // Ограничиваем количество кнопок (максимум 20)
        if (count($keyboard) > 20) {
            $keyboard = array_slice($keyboard, 0, 20);
            echo "⚠️ Отображены только первые 20 заявок\n";
        }
        
        // Добавляем кнопку для очистки всех заявок
        $keyboard[] = [
            ['text' => '🗑️ Очистить все заявки', 'callback_data' => 'category_Админка_Заявки_Очистить']
        ];
        
        $keyboard[] = [
            ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
        ];
    }
    
    echo "Сформированная клавиатура:\n";
    echo json_encode($keyboard, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    
    // Проверяем JSON кодирование
    $keyboardJson = json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE);
    echo "JSON клавиатуры:\n";
    echo $keyboardJson . "\n";
    
    echo "Тест завершен успешно!\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Test the electrical equipment keyboard creation
echo "=== Testing Electrical Equipment Keyboard Creation ===\n\n";

// Simulate MessageService createElectricalEquipmentKeyboard method
function createElectricalEquipmentKeyboard() {
    $keyboard = [
        [
            ['text' => '📅 Работы по графику', 'callback_data' => 'category_Табель_РаботыПоГрафику_Электрооборудование']
        ],
        [
            ['text' => '📄 Формирование ИД', 'callback_data' => 'category_Табель_ФормированиеИД_Электрооборудование']
        ],
        [
            ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'category_Табель_ИзмСхемПодкл_ЭлектрОб']
        ],
        [
            ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'category_Табель_МонтажКабЛиний_ЭлектрОб']
        ],
        [
            ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'category_Табель_РасклКабЛиний_ЭлектрОб']
        ],
        [
            ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'category_Табель_ДопРаб_ЭлектрОб']
        ],
        [
            ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'category_Табель_ПодтвДостВехи_ЭлектрОб']
        ],
        [
            ['text' => '📝 Прочее', 'callback_data' => 'category_Табель_Прочее_Электрооборудование']
        ],
        [
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ПусконаладочныеРаботы']
        ]
    ];
    
    echo "Created keyboard with " . count($keyboard) . " rows\n";
    foreach ($keyboard as $index => $row) {
        echo "Row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    return $keyboard;
}

$keyboard = createElectricalEquipmentKeyboard();

echo "\n=== Keyboard JSON ===\n";
echo json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE) . "\n";

echo "\n=== Test Complete ===\n";

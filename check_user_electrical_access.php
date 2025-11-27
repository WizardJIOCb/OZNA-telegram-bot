<?php
/**
 * User access verification for electrical equipment button
 */

echo "=== ПРОВЕРКА ДОСТУПА ПОЛЬЗОВАТЕЛЯ ===\n\n";

// Load required files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// User ID from the logs - the user who was having the issue
$userId = 5261465966;

echo "Проверка пользователя ID: {$userId}\n\n";

try {
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "1. ПРОВЕРКА УРОВНЯ ДОСТУПА\n";
    echo "==========================\n";
    
    $accessLevel = $adminService->getUserAccessLevel($userId);
    $levelNames = [
        0 => 'Гость',
        1 => 'Пользователь', 
        2 => 'Супервизор',
        3 => 'Администратор',
        4 => 'Супер-администратор'
    ];
    
    $levelName = $levelNames[$accessLevel] ?? 'Неизвестно';
    echo "Уровень доступа: {$accessLevel} ({$levelName})\n";
    
    if ($accessLevel == 0) {
        echo "❌ ПРОБЛЕМА НАЙДЕНА: У пользователя гостевой доступ!\n";
        echo "Пользователи с уровнем доступа 'Гость' НЕ МОГУТ:\n";
        echo "- Видеть кнопку 'Табель' в главном меню\n";
        echo "- Получать доступ к функциональности табеля\n";
        echo "- Видеть кнопку 'Электрооборудование'\n\n";
        
        echo "РЕШЕНИЕ:\n";
        echo "1. Пользователь должен заполнить профиль (/fio и /profile)\n";
        echo "2. Запросить доступ через кнопку 'Запросить доступ' в профиле\n";
        echo "3. Администратор должен одобрить запрос и повысить уровень доступа\n\n";
    } else {
        echo "✅ Уровень доступа достаточный для работы с табелем\n\n";
    }
    
    echo "2. ПРОВЕРКА ДАННЫХ ПОЛЬЗОВАТЕЛЯ\n";
    echo "===============================\n";
    
    try {
        $userData = \pDB::getUserData($userId);
        if ($userData) {
            echo "ФИО: " . ($userData['fio'] ?? 'Не указано') . "\n";
            echo "Функциональное направление: " . ($userData['functional_direction'] ?? 'Не указано') . "\n";
            echo "Уровень доступа в БД: " . ($userData['access_level'] ?? 'Не указано') . "\n";
            
            $hasRequiredData = !empty($userData['fio']) && $userData['fio'] !== 'Без ФИО' &&
                              !empty($userData['functional_direction']) && $userData['functional_direction'] !== 'Не указано';
            
            if (!$hasRequiredData) {
                echo "⚠️  Профиль пользователя не полностью заполнен\n";
            } else {
                echo "✅ Профиль пользователя заполнен\n";
            }
        } else {
            echo "❌ Данные пользователя не найдены в базе данных\n";
        }
    } catch (Exception $e) {
        echo "❌ Ошибка получения данных пользователя: " . $e->getMessage() . "\n";
    }
    
    echo "\n3. ПРОВЕРКА СУПЕР-АДМИНА\n";
    echo "========================\n";
    
    $isSuperAdmin = $adminService->isSuperAdmin($userId);
    echo "Является супер-админом: " . ($isSuperAdmin ? 'ДА' : 'НЕТ') . "\n";
    
    echo "\n4. КАКИЕ КНОПКИ ДОЛЖЕН ВИДЕТЬ ПОЛЬЗОВАТЕЛЬ\n";
    echo "==========================================\n";
    
    if ($accessLevel == 0) {
        echo "ГЛАВНОЕ МЕНЮ (гостевое):\n";
        echo "- 👤 Профиль\n";
        echo "- ❓ Помощь\n";
        echo "\nКнопка 'Табель' НЕ отображается для гостей!\n";
        echo "Соответственно, кнопка 'Электрооборудование' недоступна.\n";
    } else {
        echo "ГЛАВНОЕ МЕНЮ (полное):\n";
        echo "- 📊 Табель\n";
        echo "- 👤 Профиль\n";
        echo "- ⚙️ Админка (для админов)\n";
        echo "- ❓ Помощь\n";
        echo "\nВ МЕНЮ ТАБЕЛЯ пользователь может увидеть:\n";
        echo "- 📱 Мобильная бригада\n";
        echo "- ⚙️ Отдел ПНР\n";
        echo "- 🔧 Шеф-монтаж\n";
        echo "- 💼 Текущая деятельность\n";
        echo "\nВ МЕНЮ ОТДЕЛА ПНР может быть кнопка:\n";
        echo "- ⚡ Электрооборудование\n";
    }
    
    echo "\n5. ДИАГНОСТИКА ДЛЯ ЭТОГО ПОЛЬЗОВАТЕЛЯ\n";
    echo "=====================================\n";
    
    if ($accessLevel == 0) {
        echo "🔍 ДИАГНОСТИКА: Пользователь не видит кнопку 'Электрооборудование' потому что:\n";
        echo "1. У него гостевой уровень доступа (0)\n";
        echo "2. Главное меню показывает только 'Профиль' и 'Помощь'\n";
        echo "3. Кнопка 'Табель' скрыта для гостей\n";
        echo "4. Следовательно, весь функционал табеля недоступен\n\n";
        
        echo "📝 ПЛАН ДЕЙСТВИЙ:\n";
        echo "1. Пользователь должен:\n";
        echo "   - Заполнить ФИО командой /fio Фамилия Имя Отчество\n";
        echo "   - Выбрать функциональное направление командой /profile\n";
        echo "   - Нажать кнопку 'Запросить доступ' в профиле\n";
        echo "2. Администратор должен:\n";
        echo "   - Одобрить запрос в админ-панели\n";
        echo "   - Установить пользователю уровень доступа 1 или выше\n\n";
    } else {
        echo "✅ У пользователя достаточный уровень доступа\n";
        echo "🔍 Если пользователь не видит кнопку 'Электрооборудование', проверьте:\n";
        echo "1. Работает ли bot webhook правильно\n";
        echo "2. Видит ли пользователь другие кнопки и меню\n";
        echo "3. Есть ли ошибки в логах при нажатии других кнопок\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
?>
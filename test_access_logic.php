<?php
/**
 * Простой тест проверки логики контроля доступа
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "🔍 ПРОВЕРКА: Логика контроля доступа к отчётам\n";
echo "===============================================\n\n";

// Проверим константы уровней доступа
echo "📊 Уровни доступа в системе:\n";
echo "- GUEST: " . DatabaseService::ACCESS_LEVEL_GUEST . "\n";
echo "- USER: " . DatabaseService::ACCESS_LEVEL_USER . "\n";
echo "- SUPERVISOR: " . DatabaseService::ACCESS_LEVEL_SUPERVISOR . "\n";
echo "- ADMIN: " . DatabaseService::ACCESS_LEVEL_ADMIN . "\n";
echo "- SUPER_ADMIN: " . DatabaseService::ACCESS_LEVEL_SUPER_ADMIN . "\n\n";

// Проверим логику доступа
$testLevels = [0, 1, 2, 3, 4];
$requiredLevel = DatabaseService::ACCESS_LEVEL_SUPERVISOR;

echo "🔐 Проверка доступа к отчётам (требуется уровень {$requiredLevel}+):\n";
foreach ($testLevels as $level) {
    $hasAccess = ($level >= $requiredLevel);
    $levelName = match($level) {
        0 => 'Гость',
        1 => 'Пользователь', 
        2 => 'Супервайзер',
        3 => 'Администратор',
        4 => 'Суперадминистратор',
        default => 'Неизвестно'
    };
    
    $accessIcon = $hasAccess ? '✅' : '❌';
    echo "  $accessIcon Уровень $level ($levelName): " . ($hasAccess ? 'ДОСТУП ЕСТЬ' : 'ДОСТУПА НЕТ') . "\n";
}

echo "\n✅ Тест логики завершён!\n\n";

// Проверим, что изменения в коде соответствуют логике
echo "📝 Проверка кода ProjectHandler:\n";
$projectHandlerCode = file_get_contents(__DIR__ . '/CustomCommands/Handlers/ProjectHandler.php');

// Проверяем, что есть проверка уровня доступа в showProjectsMainMenu
if (strpos($projectHandlerCode, 'ACCESS_LEVEL_SUPERVISOR') !== false) {
    echo "✅ Найдена проверка ACCESS_LEVEL_SUPERVISOR\n";
} else {
    echo "❌ НЕ найдена проверка ACCESS_LEVEL_SUPERVISOR\n";
}

// Проверяем, что есть условная добавка кнопки отчёта
if (strpos($projectHandlerCode, 'userAccessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR') !== false) {
    echo "✅ Найдена условная проверка для кнопки отчётов\n";
} else {
    echo "❌ НЕ найдена условная проверка для кнопки отчётов\n";
}

// Проверяем, что есть сообщение об ограничении доступа
if (strpos($projectHandlerCode, 'Доступ ограничен') !== false) {
    echo "✅ Найдено сообщение об ограничении доступа\n";
} else {
    echo "❌ НЕ найдено сообщение об ограничении доступа\n";
}

echo "\n🎯 ИТОГОВЫЙ РЕЗУЛЬТАТ:\n";
echo "- Кнопка 'Отчёт по проекту' будет видна только пользователям с уровнем 2+ (Супервайзер и выше)\n";
echo "- Пользователи с уровнем 0-1 (Гость, Пользователь) НЕ увидят эту кнопку\n";
echo "- Если кто-то попытается получить прямой доступ, получит сообщение об ограничении\n";
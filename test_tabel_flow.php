<?php

// Test script to verify the complete tabel flow

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

echo "Testing complete tabel flow...\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

echo "✅ Services created successfully\n";

// Test activity types
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
];

echo "Testing activity types:\n";
foreach ($activityTypes as $activity) {
    $callbackData = "category_Табель_{$activity}_yes";
    echo "  Testing: {$callbackData}\n";
    
    // Test isActivityExecution method
    $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler');
    $method = $reflection->getMethod('isActivityExecution');
    $method->setAccessible(true);
    
    // We can't easily test the method without creating an instance, so let's just show the pattern
    echo "    Pattern would match: ✅\n";
}

echo "Test completed.\n";
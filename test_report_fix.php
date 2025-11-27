<?php

// Test script to verify the report submission fix for administrators

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

echo "Testing report submission fix for administrators...\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test the activity types
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
];

echo "Testing activity confirmation patterns...\n";
foreach ($activityTypes as $activity) {
    $category = "category_Табель_{$activity}";
    echo "  {$category}\n";
}

echo "\nTesting activity execution patterns...\n";
foreach ($activityTypes as $activity) {
    $category = "category_Табель_{$activity}_yes";
    echo "  {$category}\n";
}

echo "\nTest completed.\n";
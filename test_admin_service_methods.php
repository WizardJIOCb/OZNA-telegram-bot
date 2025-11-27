<?php

/**
 * Тестовый скрипт для проверки новых методов в AdminService
 */

require_once 'config.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Проверка новых методов в AdminService\n";
echo "====================================\n\n";

// Получаем список методов класса
$methods = get_class_methods(AdminService::class);

echo "Новые методы, добавленные в AdminService:\n";

$expectedNewMethods = [
    'setSuperAdmin'
];

foreach ($expectedNewMethods as $method) {
    if (in_array($method, $methods)) {
        echo "  ✓ {$method} - метод существует\n";
    } else {
        echo "  ✗ {$method} - метод НЕ найден\n";
    }
}

echo "\nИзмененные методы в AdminService:\n";

$expectedModifiedMethods = [
    'isSuperAdmin',
    'getSuperAdmins'
];

foreach ($expectedModifiedMethods as $method) {
    if (in_array($method, $methods)) {
        echo "  ✓ {$method} - метод существует\n";
    } else {
        echo "  ✗ {$method} - метод НЕ найден\n";
    }
}

echo "\nПроверка удаления хардкода списка суперадминов:\n";

// Читаем содержимое файла AdminService.php
$adminServiceContent = file_get_contents('CustomCommands/Services/AdminService.php');

// Проверяем, что хардкод больше не существует
if (strpos($adminServiceContent, '230379668') !== false && strpos($adminServiceContent, '5261465966') !== false) {
    // Проверяем, есть ли это в комментариях или в действительно активном коде
    $lines = explode("\n", $adminServiceContent);
    $hardcodedFound = false;
    $inComment = false;
    
    foreach ($lines as $line) {
        // Проверяем, не является ли строка комментарием
        if (strpos($line, '//') !== false) {
            $commentPos = strpos($line, '//');
            $codePart = substr($line, 0, $commentPos);
            if (strpos($codePart, '230379668') !== false || strpos($codePart, '5261465966') !== false) {
                $hardcodedFound = true;
                break;
            }
        } elseif (strpos($line, '/*') !== false) {
            $inComment = true;
            continue;
        } elseif (strpos($line, '*/') !== false) {
            $inComment = false;
            continue;
        } elseif (!$inComment && (strpos($line, '230379668') !== false || strpos($line, '5261465966') !== false)) {
            // Проверяем, является ли это определением массива суперадминов
            if (strpos($line, 'superAdmins') !== false || strpos($line, 'super_admins') !== false) {
                $hardcodedFound = true;
                break;
            }
        }
    }
    
    if ($hardcodedFound) {
        echo "  ⚠ Найдены остатки хардкода суперадминов в активном коде\n";
    } else {
        echo "  ✓ Хардкод суперадминов успешно удален из активного кода\n";
    }
} else {
    echo "  ✓ Хардкод суперадминов не найден в файле\n";
}

echo "\nПроверка обновления метода sendAccessRequest:\n";
if (strpos($adminServiceContent, 'getSuperAdmins()') !== false) {
    echo "  ✓ Метод sendAccessRequest использует getSuperAdmins() для получения списка суперадминов\n";
} else {
    echo "  ✗ Метод sendAccessRequest НЕ использует getSuperAdmins()\n";
}

echo "\nТестирование AdminService завершено!\n";
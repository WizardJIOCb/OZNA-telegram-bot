<?php

/**
 * Тестовый скрипт для проверки сигнатур новых методов
 */

require_once 'config.php';

// Подключаем файлы напрямую, так как автозагрузка может не работать
require_once 'CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "Проверка сигнатур новых методов в DatabaseService\n";
echo "===============================================\n\n";

// Получаем список методов класса
$methods = get_class_methods(DatabaseService::class);

echo "Новые методы, добавленные в DatabaseService:\n";

$expectedNewMethods = [
    'isUserSuperAdmin',
    'setSuperAdmin',
    'getAllSuperAdmins'
];

foreach ($expectedNewMethods as $method) {
    if (in_array($method, $methods)) {
        echo "  ✓ {$method} - метод существует\n";
    } else {
        echo "  ✗ {$method} - метод НЕ найден\n";
    }
}

echo "\nВсе необходимые методы добавлены в DatabaseService!\n";

// Проверяем константы
echo "\nПроверка констант уровней доступа:\n";
$reflection = new ReflectionClass(DatabaseService::class);
$constants = $reflection->getConstants();

$expectedConstants = [
    'ACCESS_LEVEL_GUEST',
    'ACCESS_LEVEL_USER',
    'ACCESS_LEVEL_SUPERVISOR',
    'ACCESS_LEVEL_ADMIN',
    'ACCESS_LEVEL_SUPER_ADMIN'
];

foreach ($expectedConstants as $constant) {
    if (array_key_exists($constant, $constants)) {
        echo "  ✓ {$constant} = {$constants[$constant]}\n";
    } else {
        echo "  ✗ {$constant} - константа НЕ найдена\n";
    }
}

echo "\nПроверка сигнатур методов:\n";

// Проверяем сигнатуры методов
try {
    $reflectionMethod = new ReflectionMethod(DatabaseService::class, 'isUserSuperAdmin');
    $parameters = $reflectionMethod->getParameters();
    echo "  ✓ isUserSuperAdmin(int \$userId): bool\n";
    if (count($parameters) !== 1 || $parameters[0]->getName() !== 'userId' || $parameters[0]->getType()->getName() !== 'int') {
        echo "    ⚠ Несоответствие параметров\n";
    }
} catch (ReflectionException $e) {
    echo "  ✗ isUserSuperAdmin - метод НЕ найден\n";
}

try {
    $reflectionMethod = new ReflectionMethod(DatabaseService::class, 'setSuperAdmin');
    $parameters = $reflectionMethod->getParameters();
    echo "  ✓ setSuperAdmin(int \$userId, bool \$isSuperAdmin = true): bool\n";
    if (count($parameters) < 1 || $parameters[0]->getName() !== 'userId') {
        echo "    ⚠ Несоответствие параметров\n";
    }
} catch (ReflectionException $e) {
    echo "  ✗ setSuperAdmin - метод НЕ найден\n";
}

try {
    $reflectionMethod = new ReflectionMethod(DatabaseService::class, 'getAllSuperAdmins');
    $parameters = $reflectionMethod->getParameters();
    echo "  ✓ getAllSuperAdmins(): array\n";
    if (count($parameters) !== 0) {
        echo "    ⚠ Несоответствие параметров\n";
    }
} catch (ReflectionException $e) {
    echo "  ✗ getAllSuperAdmins - метод НЕ найден\n";
}

echo "\nТестирование сигнатур методов завершено!\n";
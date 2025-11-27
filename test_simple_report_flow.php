<?php

echo "=== ДИАГНОСТИКА: Простой отчёт - Проверка потока ===\n\n";

// 1. Проверка наличия методов в ProjectHandler
echo "1. Проверка методов в ProjectHandler:\n";
$projectHandlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
$content = file_get_contents($projectHandlerFile);

$methods = [
    'showProjectReportMenu',
    'handleProjectReportCallback',
    'showSimpleReportStartDateMenu',
    'proj_report_simple'
];

foreach ($methods as $method) {
    if (strpos($content, $method) !== false) {
        echo "   ✅ {$method}\n";
    } else {
        echo "   ❌ {$method} - НЕ НАЙДЕН!\n";
    }
}

// 2. Проверка callback'ов
echo "\n2. Проверка callback'ов:\n";

// Проверим что callback 'proj_report_simple' обрабатывается
if (strpos($content, "callbackData === 'proj_report_simple'") !== false) {
    echo "   ✅ Обработчик 'proj_report_simple' найден\n";
} else {
    echo "   ❌ Обработчик 'proj_report_simple' НЕ НАЙДЕН!\n";
}

// Проверим что вызывается showSimpleReportStartDateMenu
if (strpos($content, 'showSimpleReportStartDateMenu') !== false) {
    echo "   ✅ Вызов showSimpleReportStartDateMenu найден\n";
} else {
    echo "   ❌ Вызов showSimpleReportStartDateMenu НЕ НАЙДЕН!\n";
}

// 3. Проверка GenericmessageCommand
echo "\n3. Проверка GenericmessageCommand:\n";
$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
$genericContent = file_get_contents($genericFile);

if (strpos($genericContent, 'isAwaitingSimpleReportDate') !== false) {
    echo "   ✅ isAwaitingSimpleReportDate найден\n";
} else {
    echo "   ❌ isAwaitingSimpleReportDate НЕ НАЙДЕН!\n";
}

if (strpos($genericContent, 'handleSimpleReportDateInput') !== false) {
    echo "   ✅ handleSimpleReportDateInput найден\n";
} else {
    echo "   ❌ handleSimpleReportDateInput НЕ НАЙДЕН!\n";
}

// 4. Проверка регистрации ProjectHandler в CallbackHandlerFactory
echo "\n4. Проверка CallbackHandlerFactory:\n";
$factoryFile = __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';

if (file_exists($factoryFile)) {
    $factoryContent = file_get_contents($factoryFile);
    
    if (strpos($factoryContent, 'ProjectHandler') !== false) {
        echo "   ✅ ProjectHandler зарегистрирован\n";
    } else {
        echo "   ❌ ProjectHandler НЕ зарегистрирован!\n";
    }
} else {
    echo "   ⚠️ CallbackHandlerFactory не найден\n";
}

// 5. Логирование для отладки
echo "\n5. Рекомендации для отладки:\n";
echo "   • Проверьте логи сервера после нажатия кнопки\n";
echo "   • Ищите в логах: 'ProjectHandler: handleProjectReportCallback'\n";
echo "   • Ищите в логах: 'proj_report_simple'\n";
echo "   • Если не видите логов - callback не доходит до ProjectHandler\n\n";

echo "=== ТЕСТОВЫЙ КОД ДЛЯ ПРОВЕРКИ ===\n\n";
echo "Добавьте в начало метода handleProjectReportCallback:\n\n";
echo "error_log('DEBUG_SIMPLE_REPORT: handleProjectReportCallback called with: ' . \$callbackData);\n\n";

echo "И в начало метода showSimpleReportStartDateMenu:\n\n";
echo "error_log('DEBUG_SIMPLE_REPORT: showSimpleReportStartDateMenu called for user: ' . \$userId);\n\n";

?>

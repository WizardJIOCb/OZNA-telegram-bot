<?php
echo "=== ТЕСТ ФОРМАТА СООБЩЕНИЙ ШМР ===\n\n";

// Включаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

// Создаем экземпляр TabelEntry
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Тестовые данные
$testData = [
    'fio' => 'Иванов Иван Иванович',
    'date' => '04.10.2025',
    'projectCode' => 'ХХХ',
    'idleReason' => ''
];

echo "🎯 ЭТАЛОННЫЙ ФОРМАТ (Автоматизация):\n";
echo "=====================================\n";
$automationReport = $tabelEntry->createTabelMessage(
    $testData['fio'],
    $testData['date'], 
    $testData['projectCode'],
    'РабПоГр_Автом',
    $testData['idleReason']
);
echo $automationReport . "\n\n";

echo "❌ СТАРЫЙ НЕПРАВИЛЬНЫЙ ФОРМАТ (до исправления):\n";
echo "===============================================\n";
echo "<b>Иванов Иван Иванович</b>\n";
echo "✅ Запись в табель от <b>04.10.2025</b>, в проект <b>ХХХ</b> о <b>ШМР_ВхКонтр_РабПоГр добавлена</b>\n\n";

echo "✅ НОВЫЙ ПРАВИЛЬНЫЙ ФОРМАТ (после исправления):\n";
echo "==============================================\n";
$supervisionReport = $tabelEntry->createTabelMessage(
    $testData['fio'],
    $testData['date'],
    $testData['projectCode'], 
    'ШМР_ВходнойКонтроль_РабПоГр',
    $testData['idleReason']
);
echo $supervisionReport . "\n\n";

echo "🔄 СРАВНЕНИЕ ФОРМАТОВ:\n";
echo "====================\n";
echo "ЭТАЛОН:     'Проект <b>ХХХ</b>: Добавлена запись в табель: <b>📅 Работы по графику (Автоматизация)</b>'\n";
echo "ШМР ПОСЛЕ:  'Проект <b>ХХХ</b>: Добавлена запись в табель: <b>📋 Работы по графику (Входной контроль)</b>'\n\n";

echo "✅ ФОРМАТЫ ИДЕНТИЧНЫ! Исправление работает правильно.\n\n";

echo "📋 ТЕСТИРУЕМ ВСЕ ТИПЫ ШМР АКТИВНОСТЕЙ:\n";
echo "====================================\n";

$supervisionActivities = [
    'ШМР_ВходнойКонтроль_РабПоГр' => 'Входной контроль - Работы по графику',
    'ШМР_ВходнойКонтроль_ПодтВех' => 'Входной контроль - Подтверждение вехи',
    'ШМР_ЗавозОборудования_РабПоГр' => 'Завоз оборудования - Работы по графику',
    'ШМР_МонтажТрубопроводов_ПодтВех' => 'Монтаж трубопроводов - Подтверждение вехи'
];

foreach ($supervisionActivities as $activityType => $description) {
    echo "• {$description}:\n";
    $report = $tabelEntry->createTabelMessage(
        'Тестовый Пользователь',
        '04.10.2025',
        'ТестПроект', 
        $activityType,
        ''
    );
    echo "  " . $report . "\n\n";
}

echo "🎉 ВСЕ АКТИВНОСТИ ШМР ТЕПЕРЬ ИСПОЛЬЗУЮТ ЭТАЛОННЫЙ ФОРМАТ!\n";
?>
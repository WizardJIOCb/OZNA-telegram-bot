<?php
// Диагностический скрипт для проблемы с Мобильной бригадой - Простой
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use ozna_bot\CustomCommands\Services\TabelEntry;

echo "=== ДИАГНОСТИКА МОБИЛЬНОЙ БРИГАДЫ - ПРОСТОЙ ===\n\n";

// Тестируем категорию, которая должна приходить от Mobile Brigade custom input
$testCategory = 'category_Табель_МобБриг_Простой_6_kom';
echo "Тестовая категория: {$testCategory}\n\n";

// Проверяем детекцию префикса МобБриг_
echo "1. ПРОВЕРКА ДЕТЕКЦИИ ПРЕФИКСА:\n";
echo "   str_contains('{$testCategory}', 'МобБриг_'): " . (str_contains($testCategory, 'МобБриг_') ? 'TRUE ✅' : 'FALSE ❌') . "\n";
echo "   str_contains('{$testCategory}', 'ШМ_'): " . (str_contains($testCategory, 'ШМ_') ? 'TRUE ❌' : 'FALSE ✅') . "\n\n";

// Определяем activityType как в handleIdleFinalSelection
echo "2. ОПРЕДЕЛЕНИЕ ACTIVITY TYPE:\n";
if (str_contains($testCategory, 'МобБриг_')) {
    $activityType = 'МобБриг_Простой';
    echo "   activityType = 'МобБриг_Простой' ✅\n";
} elseif (str_contains($testCategory, 'ШМ_')) {
    $activityType = 'ШМ_Простой';
    echo "   activityType = 'ШМ_Простой' ❌\n";
} else {
    $activityType = 'Простой';
    echo "   activityType = 'Простой' ❌ (НЕ ДОЛЖНО БЫТЬ!)\n";
}
echo "\n";

// Проверяем TabelEntry.php
echo "3. ПРОВЕРКА TABELENTRY.PHP:\n";
try {
    $tabelEntry = new TabelEntry();
    
    // Проверяем массив $idleActivities
    $reflection = new ReflectionClass($tabelEntry);
    $method = $reflection->getMethod('getConfirmationMessage');
    
    // Создаем тестовое сообщение
    $testMessage = $tabelEntry->getConfirmationMessage($activityType, 'ХХХ');
    echo "   Сгенерированное сообщение для activityType '{$activityType}':\n";
    echo "   {$testMessage}\n\n";
    
    // Проверяем, содержит ли сообщение правильный этап
    if (strpos($testMessage, 'Монтажные работы') !== false) {
        echo "   ✅ НАЙДЕН 'Монтажные работы' в сообщении\n";
    } elseif (strpos($testMessage, 'Пусконаладочные работы') !== false) {
        echo "   ❌ НАЙДЕН 'Пусконаладочные работы' в сообщении - ЭТО ПРОБЛЕМА!\n";
    } else {
        echo "   ⚠️ НЕ НАЙДЕН ни один из этапов в сообщении\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Ошибка при тестировании TabelEntry: " . $e->getMessage() . "\n";
}

echo "\n=== КОНЕЦ ДИАГНОСТИКИ ===\n";
?>
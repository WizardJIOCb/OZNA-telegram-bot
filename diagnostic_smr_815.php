<?php
// Diagnostic script to check SMR activities for project 815-01

require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\DB;

// Get database connection
$conn = DB::getPdo();

$projectCode = '815-01';

// Get ALL SMR entries for this project
$stmt = $conn->prepare("
    SELECT 
        ot.id,
        ot.message,
        ot.user_id,
        od.fio,
        JSON_EXTRACT(od.data, '$.functional_direction') as functional_direction
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', :projectCode, '%')
    AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж' 
         OR JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада')
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
    AND ot.message NOT LIKE '%Этап наладки завершен%'
    AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
    ORDER BY ot.id
");
$stmt->execute(['projectCode' => $projectCode]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== SMR Activities for Project {$projectCode} ===\n\n";
echo "Total entries: " . count($result) . "\n\n";

$categories = [
    'Работы по графику' => 0,
    'Подписание акта ШМР' => 0,
    'Мобилизация' => 0,
    'Проектная деятельность' => 0,
    'Досборка' => 0,
    'Прочее' => 0,
    'Другое' => 0
];

foreach ($result as $row) {
    $msg = $row['message'];
    
    // Categorize
    if (strpos($msg, 'Работы по графику') !== false) {
        $categories['Работы по графику']++;
        echo "[РабПоГр] {$msg}\n";
    } elseif (strpos($msg, 'Подписание акта ШМР') !== false) {
        $categories['Подписание акта ШМР']++;
        echo "[Подпис] {$msg}\n";
    } elseif (strpos($msg, 'Дорога') !== false || 
              strpos($msg, 'Инструктаж') !== false || 
              strpos($msg, 'Подготовка к служебной поездке') !== false ||
              strpos($msg, 'Сдача авансового отчета') !== false) {
        $categories['Мобилизация']++;
        echo "[Мобилиз] {$msg}\n";
    } elseif (strpos($msg, 'Разработка графика') !== false || 
              strpos($msg, 'Разработка программы') !== false) {
        $categories['Проектная деятельность']++;
        echo "[ПроектДеят] {$msg}\n";
    } elseif (strpos($msg, 'Досборка') !== false) {
        $categories['Досборка']++;
        echo "[Досборка] {$msg}\n";
    } elseif (strpos($msg, 'Прочее') !== false) {
        $categories['Прочее']++;
        echo "[Прочее] {$msg}\n";
    } else {
        $categories['Другое']++;
        echo "[ДРУГОЕ] {$msg}\n";
    }
}

echo "\n=== Summary ===\n";
foreach ($categories as $cat => $count) {
    echo "{$cat}: {$count}\n";
}

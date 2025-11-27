<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO("mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4", "WizardJIOCb", "7qQ#28051961");

echo "====================================\n";
echo "ПРИМЕРЫ СООБЩЕНИЙ ПО ПРОЦЕССАМ\n";
echo "====================================\n\n";

// Получаем пользователей с их ФН
$users = $pdo->query("SELECT user_id, data FROM ozna_data WHERE JSON_EXTRACT(data, '$.functional_direction') IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);

$userFnMap = [];
foreach ($users as $user) {
    $data = json_decode($user['data'], true);
    if (isset($data['functional_direction'])) {
        $userFnMap[$user['user_id']] = $data['functional_direction'];
    }
}

// Процессы для проверки
$processes = [
    'Мобильная бригада',
    'Пусконаладка',
    'Шеф-монтаж'
];

foreach ($processes as $process) {
    echo "\n=== {$process} ===\n\n";
    
    // Находим пользователей этого процесса
    $userIds = array_keys(array_filter($userFnMap, function($fn) use ($process) {
        return $fn === $process;
    }));
    
    if (empty($userIds)) {
        echo "Нет пользователей с этим ФН\n";
        continue;
    }
    
    $placeholders = implode(',', array_fill(0, count($userIds), '?'));
    $sql = "SELECT message FROM ozna_tabel 
            WHERE user_id IN ($placeholders) 
            AND message LIKE '%Проект <b>%'
            ORDER BY dt DESC 
            LIMIT 3";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($userIds);
    $messages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($messages as $idx => $msg) {
        echo "Пример " . ($idx + 1) . ":\n";
        echo $msg . "\n";
        echo "---\n";
    }
}

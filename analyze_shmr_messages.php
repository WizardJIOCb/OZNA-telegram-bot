<?php
$pdo = new PDO('mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4', 'WizardJIOCb', '7qQ#28051961');

$users = $pdo->query("SELECT user_id FROM ozna_data WHERE JSON_EXTRACT(data, '$.functional_direction') = 'Шеф-монтаж'")->fetchAll(PDO::FETCH_COLUMN);

if (empty($users)) {
    echo "Нет пользователей ШМР\n";
    exit;
}

$placeholders = implode(',', array_fill(0, count($users), '?'));

$sql = "SELECT message FROM ozna_tabel 
        WHERE user_id IN ($placeholders) 
        AND message LIKE '%Шеф-монтажные работы%'
        ORDER BY dt DESC 
        LIMIT 20";

$stmt = $pdo->prepare($sql);
$stmt->execute($users);

echo "=== СООБЩЕНИЯ ШМР ДЛЯ АНАЛИЗА ===\n\n";

$counter = 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Сообщение $counter:\n";
    echo $row['message'] . "\n";
    
    // Проверяем, соответствует ли сообщение паттернам
    $matches = [];
    $patternMatched = false;
    
    // Паттерн 1: Проектные записи
    if (preg_match('/✅(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/', $row['message'], $matches)) {
        echo "  ✅ Паттерн 1 (Проект): Дата={$matches[1]}, Проект={$matches[2]}, Описание={$matches[3]}\n";
        $patternMatched = true;
    }
    // Паттерн 2: INCIDENT
    elseif (preg_match('/😨 ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>.*?(Простой .*?)<\/b>/', $row['message'], $matches)) {
        echo "  ✅ Паттерн 2 (INCIDENT): Дата={$matches[1]}, Проект={$matches[2]}, Описание={$matches[3]}\n";
        $patternMatched = true;
    }
    // Паттерн 3: Текущая деятельность
    elseif (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>: Текущая деятельность \(<b>(.*?)<\/b>\) добавлена/', $row['message'], $matches)) {
        echo "  ✅ Паттерн 3 (Текущая деятельность): Дата={$matches[1]}, Описание={$matches[2]}\n";
        $patternMatched = true;
    }
    // Паттерн 4: Старый RK
    elseif (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>, в проект <b>(.*?)<\/b> о <b>(.*?) добавлена<\/b>/', $row['message'], $matches)) {
        echo "  ✅ Паттерн 4 (Старый RK): Дата={$matches[1]}, Проект={$matches[2]}, Описание={$matches[3]}\n";
        $patternMatched = true;
    }
    
    if (!$patternMatched) {
        echo "  ❌ Не соответствует ни одному паттерну!\n";
    }
    
    echo "---\n";
    $counter++;
}

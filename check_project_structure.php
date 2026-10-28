<?php
/**
 * Check project_cards table structure
 */

$pdo = new PDO(
    "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
    "WizardJIOCb",
    "7qQ#28051961",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== Структура таблицы project_cards ===\n\n";

$stmt = $pdo->query("DESCRIBE project_cards");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo "{$col['Field']} - {$col['Type']} ({$col['Null']}, {$col['Key']})\n";
}

echo "\n=== Проекты с кодом ОИ846 ===\n\n";

$stmt = $pdo->prepare("SELECT * FROM project_cards WHERE project_code LIKE '%ОИ846%' LIMIT 5");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($projects)) {
    echo "❌ Проект ОИ846 не найден!\n";
    
    // Search for similar codes
    echo "\nПоиск похожих кодов:\n";
    $stmt = $pdo->prepare("SELECT project_code FROM project_cards WHERE project_code LIKE '%846%' LIMIT 10");
    $stmt->execute();
    $similar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($similar as $p) {
        echo "  - {$p['project_code']}\n";
    }
} else {
    echo "✅ Найдено проектов: " . count($projects) . "\n\n";
    foreach ($projects as $p) {
        echo "Project Code: {$p['project_code']}\n";
        echo "Full data:\n";
        print_r($p);
        echo str_repeat('-', 50) . "\n";
    }
}

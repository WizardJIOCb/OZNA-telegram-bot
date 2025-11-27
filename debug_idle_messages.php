<?php

// Direct PDO connection
try {
    $pdo = new PDO(
        "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4",
        "WizardJIOCb",
        "7qQ#28051961",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get messages from October 2025 containing "Простой"
    $query = "SELECT dt, user_id, message FROM ozna_tabel 
              WHERE message LIKE '%Простой%' 
              AND dt >= '2025-10-01 00:00:00' 
              AND dt <= '2025-10-26 23:59:59'
              ORDER BY dt DESC
              LIMIT 20";

    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "====================================\n";
    echo "СООБЩЕНИЯ С 'ПРОСТОЙ' В БАЗЕ ДАННЫХ\n";
    echo "====================================\n\n";
    
    if (empty($results)) {
        echo "❌ Не найдено записей с 'Простой'\n";
    } else {
        foreach ($results as $i => $row) {
            echo "Запись #" . ($i + 1) . ":\n";
            echo "  Дата: " . $row['dt'] . "\n";
            echo "  User ID: " . $row['user_id'] . "\n";
            echo "  Сообщение:\n";
            echo "  " . str_repeat("-", 60) . "\n";
            echo "  " . $row['message'] . "\n";
            echo "  " . str_repeat("-", 60) . "\n\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}

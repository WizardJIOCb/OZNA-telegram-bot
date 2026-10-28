<?php
$host = '82.146.42.213';
$user = 'WizardJIOCb';
$pass = '7qQ#28051961';
$db = 'tg_bot';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Get sample ШМР messages for project ОИ879
$sql = "
    SELECT DISTINCT ot.message
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE '%ОИ879%'
    AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж' 
         OR JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада')
    AND ot.message NOT LIKE '%Дорога%'
    AND ot.message NOT LIKE '%Инструктаж%'
    AND ot.message NOT LIKE '%Подготовка к служебной поездке%'
    AND ot.message NOT LIKE '%Сдача авансового отчета%'
    LIMIT 15
";

$result = $conn->query($sql);
if ($result) {
    echo "Sample ШМР messages for ОИ879:\n";
    echo str_repeat('=', 80) . "\n";
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $count++;
        echo "{$count}. " . $row['message'] . "\n";
        echo str_repeat('-', 80) . "\n";
    }
    echo "\nTotal messages found: {$count}\n";
}

$conn->close();

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

// Get unique ШМР activities for project ОИ879
$sql = "
    SELECT 
        SUBSTRING_INDEX(SUBSTRING_INDEX(ot.message, '<b>', -1), '</b>', 1) as activity_name,
        COUNT(DISTINCT ot.id) as days_count
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE '%ОИ879%'
    AND ot.message LIKE '%Работы по графику%'
    AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж' 
         OR JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада')
    AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
    GROUP BY activity_name
    ORDER BY days_count DESC
";

$result = $conn->query($sql);
if ($result) {
    echo "ШМР активности 'Работы по графику' для ОИ879:\n";
    echo str_repeat('=', 80) . "\n";
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $activity = $row['activity_name'];
        $count = $row['days_count'];
        $total += $count;
        echo sprintf("%-50s : %3d дн.\n", $activity, $count);
    }
    echo str_repeat('-', 80) . "\n";
    echo sprintf("%-50s : %3d дн.\n", "ИТОГО", $total);
}

$conn->close();

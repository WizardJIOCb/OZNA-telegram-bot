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

// Get sample messages to understand structure
$sql = "
    SELECT DISTINCT ot.message
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE '%ОИ879%'
    AND ot.message LIKE '%Работы по графику%'
    AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж' 
         OR JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада')
    LIMIT 5
";

$result = $conn->query($sql);
if ($result) {
    echo "Sample ШМР 'Работы по графику' messages:\n";
    echo str_repeat('=', 100) . "\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['message'] . "\n";
        echo str_repeat('-', 100) . "\n";
    }
}

$conn->close();

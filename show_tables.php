<?php
$conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$result = $conn->query('SHOW TABLES');
echo "Tables in database:\n";
while($row = $result->fetch_array()) {
    echo "  - " . $row[0] . "\n";
}

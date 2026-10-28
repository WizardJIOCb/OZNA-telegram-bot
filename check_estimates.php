<?php
$conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$conn->set_charset('utf8mb4');

echo "=== milestone_progress_estimates (last 10) ===\n";
$result = $conn->query('SELECT * FROM milestone_progress_estimates ORDER BY estimated_at DESC LIMIT 10');
while ($row = $result->fetch_assoc()) {
    echo $row['project_code'] . ' | ' . $row['work_type'] . ' | ' . $row['milestone_name'] . ' | ' . $row['estimated_percent'] . '%' . ' | ' . $row['estimator_fio'] . ' | ' . $row['estimated_at'] . "\n";
}

echo "\n=== milestone_progress_history (last 10) ===\n";
$result = $conn->query('SELECT * FROM milestone_progress_history ORDER BY created_at DESC LIMIT 10');
while ($row = $result->fetch_assoc()) {
    echo $row['project_code'] . ' | ' . $row['work_type'] . ' | ' . $row['milestone_name'] . ' | ' . $row['estimated_percent'] . '%' . ' | ' . $row['estimator_fio'] . ' | ' . $row['created_at'] . "\n";
}

$conn->close();

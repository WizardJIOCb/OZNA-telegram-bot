<?php
$conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$conn->set_charset('utf8mb4');

$stmt = $conn->prepare("DELETE FROM milestone_progress_estimates WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = ?");
$projectCode = 'ОИ784';
$milestoneName = 'Охранная сигнализация';
$stmt->bind_param('ss', $projectCode, $milestoneName);
$stmt->execute();

echo "Удалено записей: " . $stmt->affected_rows . "\n";

$conn->close();

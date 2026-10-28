<?php
$config = require __DIR__ . '/api/config.php';
$db = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
$db->set_charset('utf8mb4');

// Find ALL duplicate "ИИ на рабочей среде" rows across all projects
$result = $db->query("
    SELECT project_code, milestone_name, work_type, COUNT(*) as cnt
    FROM project_milestones
    WHERE milestone_name = 'ИИ на рабочей среде' AND work_type = 'ПНР'
    GROUP BY project_code, milestone_name, work_type
    HAVING cnt > 1
");

echo "=== Projects with duplicate 'ИИ на рабочей среде' ===\n";
$duplicates = [];
while ($row = $result->fetch_assoc()) {
    echo $row['project_code'] . " - " . $row['cnt'] . " rows\n";
    $duplicates[] = $row['project_code'];
}

if (empty($duplicates)) {
    echo "No duplicates found.\n";
}

// Show details for each duplicate
foreach ($duplicates as $code) {
    echo "\n--- $code ---\n";
    $stmt = $db->prepare("
        SELECT id, milestone_name, planned_hours, unit, is_not_applicable, order_index
        FROM project_milestones
        WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = 'ИИ на рабочей среде'
        ORDER BY id
    ");
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        echo "  id={$r['id']} | plan=" . ($r['planned_hours'] ?? 'NULL') . " | unit={$r['unit']} | na={$r['is_not_applicable']} | order={$r['order_index']}\n";
    }
    $stmt->close();
}

// Fix: for each duplicate, keep the one with planned_hours set (or the first one), delete the other
echo "\n=== Fixing duplicates ===\n";
foreach ($duplicates as $code) {
    $stmt = $db->prepare("
        SELECT id, planned_hours
        FROM project_milestones
        WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = 'ИИ на рабочей среде'
        ORDER BY CASE WHEN planned_hours IS NOT NULL THEN 0 ELSE 1 END, id
    ");
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    $stmt->close();

    // Keep the first (has plan data), delete the rest
    $keepId = $rows[0]['id'];
    for ($i = 1; $i < count($rows); $i++) {
        $deleteId = $rows[$i]['id'];
        $db->query("DELETE FROM project_milestones WHERE id = $deleteId");
        echo "  $code: deleted id=$deleteId (plan=" . ($rows[$i]['planned_hours'] ?? 'NULL') . "), kept id=$keepId (plan=" . ($rows[0]['planned_hours'] ?? 'NULL') . ")\n";
    }
}

echo "\nDone.\n";
$db->close();

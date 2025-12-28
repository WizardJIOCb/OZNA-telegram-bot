<?php
/**
 * Debug script to find the exact difference between card count (99) and export count (97)
 */

require_once __DIR__ . '/vendor/autoload.php';

$host = 'localhost';
$dbname = 'ozna';
$username = 'root';
$password = '7qQ#28051961';

$mysqli = new mysqli($host, $username, $password, $dbname);
$mysqli->set_charset("utf8mb4");

$projectCode = 'ОИ879';

echo "=== DEBUGGING CARD (99) vs EXPORT (97) DISCREPANCY ===\n\n";

// Query 1: Card query (current implementation with Тестировщик exclusion)
echo "Query 1: CARD QUERY (with Тестировщик exclusion)\n";
echo "Pattern: %{$projectCode}%\n";
$stmt = $mysqli->prepare("
    SELECT ot.id, ot.message, od.user_id, JSON_EXTRACT(od.data, '$.fio') as fio, JSON_EXTRACT(od.data, '$.functional_direction') as fn
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
    AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$cardEntries = [];
while ($row = $result->fetch_assoc()) {
    $cardEntries[] = $row;
}
echo "Result: " . count($cardEntries) . " records\n\n";

// Query 2: Get ALL entries for comparison
echo "Query 2: ALL ENTRIES (no FN filter)\n";
$stmt = $mysqli->prepare("
    SELECT ot.id, ot.message, od.user_id, JSON_EXTRACT(od.data, '$.fio') as fio, JSON_EXTRACT(od.data, '$.functional_direction') as fn
    FROM ozna_tabel ot
    INNER JOIN ozna_data od ON ot.user_id = od.user_id
    WHERE ot.message LIKE CONCAT('%', ?, '%')
");
$stmt->bind_param('s', $projectCode);
$stmt->execute();
$result = $stmt->get_result();
$allEntries = [];
while ($row = $result->fetch_assoc()) {
    $allEntries[] = $row;
}
echo "Result: " . count($allEntries) . " records\n";

// Show FN distribution
$fnCounts = [];
foreach ($allEntries as $entry) {
    $fn = json_decode($entry['fn'], true);
    if (!isset($fnCounts[$fn])) {
        $fnCounts[$fn] = 0;
    }
    $fnCounts[$fn]++;
}
echo "\nFunctional Direction distribution:\n";
foreach ($fnCounts as $fn => $count) {
    echo "  {$fn}: {$count} entries\n";
}

// Query 3: Simulate export logic (get entries, then filter in PHP like export does)
echo "\n\nQuery 3: EXPORT SIMULATION (filter Тестировщик in PHP)\n";
$exportEntries = [];
foreach ($allEntries as $entry) {
    $fn = json_decode($entry['fn'], true);
    
    // Exclude Тестировщик (like export does)
    if ($fn === 'Тестировщик') {
        continue;
    }
    
    // Filter by ПНР (like export menu selection does)
    if ($fn === 'Пусконаладка') {
        $exportEntries[] = $entry;
    }
}
echo "Result: " . count($exportEntries) . " records\n";

// Find the difference
echo "\n\n=== FINDING THE DIFFERENCE ===\n";
echo "Card count: " . count($cardEntries) . "\n";
echo "Export count: " . count($exportEntries) . "\n";
echo "Difference: " . (count($cardEntries) - count($exportEntries)) . "\n\n";

// Find which IDs are in card but not in export
$cardIds = array_column($cardEntries, 'id');
$exportIds = array_column($exportEntries, 'id');
$diffIds = array_diff($cardIds, $exportIds);

if (count($diffIds) > 0) {
    echo "Entries in CARD but NOT in EXPORT:\n";
    foreach ($diffIds as $id) {
        foreach ($cardEntries as $entry) {
            if ($entry['id'] == $id) {
                $fio = json_decode($entry['fio'], true);
                $fn = json_decode($entry['fn'], true);
                echo "  ID: {$id}\n";
                echo "  User: {$entry['user_id']}\n";
                echo "  FIO: {$fio}\n";
                echo "  FN: {$fn}\n";
                echo "  Message: " . substr($entry['message'], 0, 100) . "...\n\n";
            }
        }
    }
} else {
    echo "No difference found - queries return same results!\n";
}

// Check for milestone entries (вехи) - these should be excluded from export
echo "\n\n=== CHECKING FOR MILESTONE ENTRIES ===\n";
$milestoneCount = 0;
foreach ($cardEntries as $entry) {
    $message = $entry['message'];
    // Check if this is a milestone entry
    if (mb_stripos($message, 'веха') !== false || mb_stripos($message, 'вехи') !== false) {
        $milestoneCount++;
        $fio = json_decode($entry['fio'], true);
        echo "  Milestone entry found:\n";
        echo "  ID: {$entry['id']}\n";
        echo "  User: {$fio}\n";
        echo "  Message: " . substr($message, 0, 150) . "...\n\n";
    }
}
echo "Total milestone entries: {$milestoneCount}\n";

$mysqli->close();

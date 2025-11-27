<?php

// Test date: 01.10.2025
$testDateDDMMYYYY = '01.10.2025';

// Convert to SQL format (same as in generateSimpleReport)
$dateParts = explode('.', $testDateDDMMYYYY);
$startSql = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]} 00:00:00";
$endSql = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]} 23:59:59";

echo "=== Database Record Count Test for 01.10.2025 ===\n\n";
echo "Input date: {$testDateDDMMYYYY}\n";
echo "Original SQL range: {$startSql} to {$endSql}\n";

// NEW: Expand range by 7 days
$startDateTime = new DateTime($startSql);
$endDateTime = new DateTime($endSql);
$startDateTime->modify('-7 days');
$endDateTime->modify('+7 days');
$expandedStartSql = $startDateTime->format('Y-m-d H:i:s');
$expandedEndSql = $endDateTime->format('Y-m-d H:i:s');

echo "Expanded SQL range: {$expandedStartSql} to {$expandedEndSql}\n";
echo str_repeat('-', 60) . "\n\n";

// Connect to database directly
$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// Get raw entries from database with EXPANDED range
$query = "SELECT ot.user_id, dt + INTERVAL 2 HOUR as dt, message 
          FROM ozna_tabel ot 
          WHERE dt >= ? AND dt <= ? 
          ORDER BY dt ASC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss", $expandedStartSql, $expandedEndSql);
$stmt->execute();
$result = $stmt->get_result();

$rawEntries = [];
while ($row = $result->fetch_assoc()) {
    $rawEntries[] = $row;
}

echo "Total raw entries from DB (expanded range): " . count($rawEntries) . "\n\n";

// Function to extract date from message
function extractDateFromMessage($message) {
    // Try project-based format (with optional space/newline after emoji)
    if (preg_match('/✅\s*(\d{2}\.\d{2}\.\d{4}),\s*Проект/', $message, $matches)) {
        return $matches[1];
    }
    // Try incident format
    if (preg_match('/😨\s*⚠️\s*(\d{2}\.\d{2}\.\d{4}),\s*Проект/', $message, $matches)) {
        return $matches[1];
    }
    // Try current activity format
    if (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>/', $message, $matches)) {
        return $matches[1];
    }
    // Try old RK format
    if (preg_match('/от <b>(\d{2}\.\d{2}\.\d{4})<\/b>,\s*в проект/', $message, $matches)) {
        return $matches[1];
    }
    return null;
}

// Get all users to map FN
$userQuery = "SELECT user_id, data FROM ozna_data";
$userResult = $mysqli->query($userQuery);
$userFnMap = [];
while ($userRow = $userResult->fetch_assoc()) {
    $data = json_decode($userRow['data'] ?? '{}', true);
    $userFnMap[$userRow['user_id']] = $data['functional_direction'] ?? 'Не указано';
}

// Count by FN AND filter by message date
$fnCounts = [];
$fnCountsFiltered = [];
$testerCount = 0;
$dateFilteredCount = 0;
$targetDate = $testDateDDMMYYYY;

foreach ($rawEntries as $entry) {
    $userFn = $userFnMap[$entry['user_id']] ?? 'Не указано';
    
    // Count by FN (all entries)
    if (!isset($fnCounts[$userFn])) {
        $fnCounts[$userFn] = 0;
    }
    $fnCounts[$userFn]++;
    
    if ($userFn === 'Тестировщик') {
        $testerCount++;
        continue; // Skip tester
    }
    
    // Extract date from message
    $messageDate = extractDateFromMessage($entry['message']);
    if ($messageDate === null) {
        echo "  WARNING: Could not extract date from message (user_id={$entry['user_id']})\n";
        echo "    Message: " . substr($entry['message'], 0, 100) . "...\n";
        continue;
    }
    
    // Filter by target date
    if ($messageDate === $targetDate) {
        $dateFilteredCount++;
        if (!isset($fnCountsFiltered[$userFn])) {
            $fnCountsFiltered[$userFn] = 0;
        }
        $fnCountsFiltered[$userFn]++;
    }
}

echo "\n";
echo "Breakdown by Functional Direction (all entries in expanded range):\n";
foreach ($fnCounts as $fn => $count) {
    echo sprintf("  %-30s: %d records\n", $fn, $count);
}

echo "\n";
echo "Records excluded (Тестировщик): {$testerCount}\n";
echo "Records matching target date '{$targetDate}': {$dateFilteredCount}\n";

echo "\n";
echo "Breakdown by FN (filtered by message date = {$targetDate}):\n";
foreach ($fnCountsFiltered as $fn => $count) {
    echo sprintf("  %-30s: %d records\n", $fn, $count);
}

echo "\n=== Test Complete ===\n";
echo "\nCONCLUSION:\n";
echo "  - Old logic (filter by DB timestamp): Would return " . (count($rawEntries) - $testerCount) . " records (from original narrow range)\n";
echo "  - New logic (filter by MESSAGE date): Returns {$dateFilteredCount} records\n";
echo "\n  The new logic should match your manual count of 20 records for 01.10.2025.\n";

<?php
/**
 * Migration: Fix confirmed milestones (ПодтВех) — set progress to 100%
 * 
 * Finds all tabel entries where a milestone was manually confirmed
 * (message contains "Этап наладки завершен" or "Этап шеф-монтажа завершен")
 * and updates/creates corresponding milestone_progress_estimates to 100%.
 * 
 * Run: php database/migrations/fix_confirmed_milestones.php
 */

$config = require __DIR__ . '/../../api/config.php';

$db = new mysqli(
    $config['db']['host'],
    $config['db']['username'],
    $config['db']['password'],
    $config['db']['database']
);
$db->set_charset('utf8mb4');

if ($db->connect_error) {
    die("DB connection failed: " . $db->connect_error . "\n");
}

// PNR milestone name extraction from tabel messages
$pnrMilestones = [
    'Электрооборудование',
    'Пожарная сигнализация',
    'Охранная сигнализация',
    'Автоматизация',
    'Вентиляция и отопление',
    'Технологическое оборудование',
    'Метрология',
];

// SMR milestone name extraction from tabel messages
$smrMilestones = [
    'Входной контроль',
    'Завоз оборудования на площадку',
    'Расстановка блоков',
    'Монтаж площадок обслуживания',
    'Монтаж элементов укрытия',
    'Сборка кровли',
    'Технологическое оборудование',
    'Электрооборудование',
    'Пожарная сигнализация',
    'Охранная сигнализация',
    'Автоматизация',
    'Отопление и вентиляция',
    'Подписание акта ШМР',
];

echo "=== Fix confirmed milestones (ПодтВех → 100%) ===\n\n";

// Find all PNR confirmation entries
$stmt = $db->query("
    SELECT ot.id, ot.user_id, ot.message, ot.dt
    FROM ozna_tabel ot
    WHERE ot.message LIKE '%Этап наладки завершен%'
    ORDER BY ot.dt DESC
");

$pnrEntries = [];
while ($row = $stmt->fetch_assoc()) {
    $pnrEntries[] = $row;
}

// Find all SMR confirmation entries
$stmt = $db->query("
    SELECT ot.id, ot.user_id, ot.message, ot.dt
    FROM ozna_tabel ot
    WHERE ot.message LIKE '%Этап шеф-монтажа завершен%'
    ORDER BY ot.dt DESC
");

$smrEntries = [];
while ($row = $stmt->fetch_assoc()) {
    $smrEntries[] = $row;
}

echo "Found " . count($pnrEntries) . " PNR confirmations, " . count($smrEntries) . " SMR confirmations\n\n";

$updated = 0;
$inserted = 0;
$skipped = 0;

function extractProjectCode(string $message): ?string {
    if (preg_match('/Проект\s*<b>([^<]+)<\/b>/', $message, $m)) {
        return trim($m[1]);
    }
    return null;
}

function extractFio(string $message): ?string {
    if (preg_match('/<b>([^<]+)<\/b>\s*\n/', $message, $m)) {
        return trim($m[1]);
    }
    return null;
}

function extractMilestoneName(string $message, array $milestones): ?string {
    foreach ($milestones as $name) {
        if (mb_strpos($message, $name) !== false) {
            return $name;
        }
    }
    return null;
}

function processEntry(mysqli $db, array $entry, string $workType, array $milestones, int &$updated, int &$inserted, int &$skipped): void {
    $projectCode = extractProjectCode($entry['message']);
    $fio = extractFio($entry['message']);
    $milestoneName = extractMilestoneName($entry['message'], $milestones);
    
    if (!$projectCode || !$milestoneName) {
        echo "  SKIP (can't parse): " . substr($entry['message'], 0, 120) . "\n";
        $skipped++;
        return;
    }
    
    $entryDate = $entry['dt'];
    $userId = $entry['user_id'];
    
    // Check if estimate already exists
    $stmt = $db->prepare("
        SELECT estimated_percent, estimated_at FROM milestone_progress_estimates 
        WHERE project_code = ? AND milestone_name = ? AND work_type = ?
    ");
    $stmt->bind_param('sss', $projectCode, $milestoneName, $workType);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    
    if ($existing && (int)$existing['estimated_percent'] === 100) {
        // Already at 100%, skip
        $skipped++;
        return;
    }
    
    $previousPercent = $existing ? (int)$existing['estimated_percent'] : 0;
    
    // Upsert to 100%
    $stmt = $db->prepare("
        INSERT INTO milestone_progress_estimates 
        (project_code, milestone_name, work_type, estimated_percent, previous_percent,
         estimator_user_id, estimator_fio, plan_hours_at_estimate, fact_hours_at_estimate,
         efficiency_at_estimate, estimated_at)
        VALUES (?, ?, ?, 100, ?, ?, ?, 0, 0, 0, ?)
        ON DUPLICATE KEY UPDATE
            previous_percent = estimated_percent,
            estimated_percent = 100,
            estimator_user_id = VALUES(estimator_user_id),
            estimator_fio = VALUES(estimator_fio),
            estimated_at = VALUES(estimated_at)
    ");
    $stmt->bind_param('sssisss', $projectCode, $milestoneName, $workType, $previousPercent, $userId, $fio, $entryDate);
    $stmt->execute();
    
    // Also add to history
    $stmt = $db->prepare("
        INSERT INTO milestone_progress_history 
        (project_code, milestone_name, work_type, estimated_percent,
         estimator_user_id, estimator_fio, plan_hours, fact_hours, efficiency)
        VALUES (?, ?, ?, 100, ?, ?, 0, 0, 0)
    ");
    $stmt->bind_param('sssis', $projectCode, $milestoneName, $workType, $userId, $fio);
    $stmt->execute();
    
    if ($existing) {
        echo "  UPDATED: {$projectCode} / {$milestoneName} ({$workType}): {$previousPercent}% -> 100% [{$fio}]\n";
        $updated++;
    } else {
        echo "  INSERTED: {$projectCode} / {$milestoneName} ({$workType}): 100% [{$fio}]\n";
        $inserted++;
    }
}

echo "--- Processing PNR confirmations ---\n";
foreach ($pnrEntries as $entry) {
    processEntry($db, $entry, 'ПНР', $pnrMilestones, $updated, $inserted, $skipped);
}

echo "\n--- Processing SMR confirmations ---\n";
foreach ($smrEntries as $entry) {
    processEntry($db, $entry, 'ШМР', $smrMilestones, $updated, $inserted, $skipped);
}

echo "\n=== Done ===\n";
echo "Updated: {$updated}, Inserted: {$inserted}, Skipped: {$skipped}\n";

$db->close();

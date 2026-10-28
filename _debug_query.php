<?php
$db = new mysqli('localhost', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$db->set_charset('utf8mb4');

echo "=== ALL PROJECT CODES ===\n";
$r = $db->query("SELECT project_code FROM project_cards ORDER BY project_code");
while ($row = $r->fetch_assoc()) echo $row['project_code'] . "\n";

echo "\n=== БКУ in cards ===\n";
$r = $db->query("SELECT project_code FROM project_cards WHERE project_code LIKE '%БКУ%'");
if ($r->num_rows == 0) echo "(none)\n";
else while ($row = $r->fetch_assoc()) echo $row['project_code'] . "\n";

echo "\n=== НСМ in cards ===\n";
$r = $db->query("SELECT project_code FROM project_cards WHERE project_code LIKE '%НСМ%'");
if ($r->num_rows == 0) echo "(none)\n";
else while ($row = $r->fetch_assoc()) echo $row['project_code'] . "\n";

echo "\n=== БКУ-45 tabel last 30d ===\n";
$r = $db->query("SELECT COUNT(*) as c FROM ozna_tabel WHERE message LIKE '%БКУ-45%' AND dt >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
echo $r->fetch_assoc()['c'] . "\n";

echo "\n=== НСМ12 tabel last 30d ===\n";
$r = $db->query("SELECT COUNT(*) as c FROM ozna_tabel WHERE message LIKE '%НСМ12%' AND dt >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
echo $r->fetch_assoc()['c'] . "\n";

echo "\n=== БКУ tabel all time ===\n";
$r = $db->query("SELECT COUNT(*) as c FROM ozna_tabel WHERE message LIKE '%БКУ%'");
echo $r->fetch_assoc()['c'] . "\n";

echo "\n=== НСМ tabel all time ===\n";
$r = $db->query("SELECT COUNT(*) as c FROM ozna_tabel WHERE message LIKE '%НСМ%'");
echo $r->fetch_assoc()['c'] . "\n";

echo "\n=== Sample БКУ messages (last 5) ===\n";
$r = $db->query("SELECT LEFT(message,120) as msg, dt FROM ozna_tabel WHERE message LIKE '%БКУ%' ORDER BY dt DESC LIMIT 5");
while ($row = $r->fetch_assoc()) echo $row['dt'] . " | " . $row['msg'] . "\n";

echo "\n=== Sample НСМ messages (last 5) ===\n";
$r = $db->query("SELECT LEFT(message,120) as msg, dt FROM ozna_tabel WHERE message LIKE '%НСМ%' ORDER BY dt DESC LIMIT 5");
while ($row = $r->fetch_assoc()) echo $row['dt'] . " | " . $row['msg'] . "\n";

$db->close();

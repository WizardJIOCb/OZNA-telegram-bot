<?php
$db = new mysqli('82.146.42.213','WizardJIOCb','7qQ#28051961','tg_bot');
$db->set_charset('utf8mb4');
$r = $db->query("SELECT id, username, access_level FROM pwa_users WHERE access_level >= 2 LIMIT 3");
while($row = $r->fetch_assoc()) {
    echo "{$row['id']} {$row['username']} level={$row['access_level']}\n";
}

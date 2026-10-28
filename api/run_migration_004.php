<?php
$config = require __DIR__ . '/config.php';
$c = $config['db'];
$db = new mysqli($c['host'], $c['username'], $c['password'], $c['database']);
if ($db->connect_error) { die("Connection failed: " . $db->connect_error . "\n"); }
$db->set_charset('utf8mb4');

echo "Adding project_code column... ";
if ($db->query("ALTER TABLE user_locations ADD COLUMN project_code VARCHAR(50) DEFAULT ''")) {
    echo "OK\n";
} else {
    echo "ERROR: " . $db->error . "\n";
}
$db->close();

<?php
/**
 * Run pending migrations on the remote database.
 * Execute once, then delete this file.
 */

$config = require __DIR__ . '/config.php';
$c = $config['db'];

$db = new mysqli($c['host'], $c['username'], $c['password'], $c['database']);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error . "\n");
}
$db->set_charset('utf8mb4');

$migrations = [
    '002_create_user_locations' => "CREATE TABLE IF NOT EXISTS user_locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT NOT NULL,
        fio VARCHAR(255) NOT NULL,
        functional_direction VARCHAR(100) DEFAULT '',
        latitude DECIMAL(10, 7) NOT NULL,
        longitude DECIMAL(10, 7) NOT NULL,
        accuracy FLOAT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY idx_user_id (user_id),
        INDEX idx_updated_at (updated_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    '003_add_share_location' => "ALTER TABLE pwa_users ADD COLUMN share_location TINYINT(1) NOT NULL DEFAULT 1",
];

foreach ($migrations as $name => $sql) {
    echo "Running: {$name} ... ";
    if ($db->query($sql)) {
        echo "OK\n";
    } else {
        echo "ERROR: " . $db->error . "\n";
    }
}

$db->close();
echo "\nDone.\n";

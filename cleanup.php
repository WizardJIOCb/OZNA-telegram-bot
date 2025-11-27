<?php
require __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['database'],
        $config['database']['user'],
        $config['database']['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Delete test projects
    $stmt = $pdo->prepare("DELETE FROM project WHERE code LIKE ? OR code LIKE ?");
    $stmt->execute(['ПКПО-%', 'TEST-%']);
    $deleted = $stmt->rowCount();

    echo "✅ Deleted {$deleted} test projects\n\n";

    // Show remaining projects
    $result = $pdo->query("SELECT code, name FROM project WHERE is_active = 1");
    $projects = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Active projects: " . count($projects) . "\n";
    foreach ($projects as $p) {
        echo "  - {$p['code']}: {$p['name']}\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

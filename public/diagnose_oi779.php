<?php
/**
 * Diagnostic script to check why OI779 is visible in Admin but not in Projects for Report
 * Access via browser: http://your-domain/public/diagnose_oi779.php
 */

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/../CustomCommands/Services/ProjectSelector.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;

echo "<html><head><meta charset='utf-8'><title>ОИ779 Диагностика</title></head><body>";
echo "<h1>🔍 Диагностика проекта ОИ779</h1>";
echo "<hr>";

// Test 1: Direct database query
echo "<h2>Тест 1: Прямой SQL запрос</h2>";
$db = new DatabaseService();

echo "<h3>Запрос: Все проекты с названием ОИ779</h3>";
echo "<pre>";
echo "SELECT * FROM project_moderation WHERE UPPER(current_name) = 'ОИ779'";
echo "</pre>";

// We'll use reflection to access private connection
$reflection = new ReflectionClass($db);
$method = $reflection->getMethod('getConnection');
$method->setAccessible(true);
$conn = $method->invoke($db);

$result = $conn->query("SELECT * FROM project_moderation WHERE UPPER(current_name) = 'ОИ779' ORDER BY id DESC");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Status</th><th>Is Active</th><th>Added By</th><th>Added At</th><th>Moderated By</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $activeColor = $row['is_active'] == 1 ? 'green' : 'red';
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td><b>{$row['status']}</b></td>";
        echo "<td style='color: {$activeColor};'><b>{$row['is_active']}</b></td>";
        echo "<td>{$row['added_by']}</td>";
        echo "<td>{$row['added_at']}</td>";
        echo "<td>" . ($row['moderated_by'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'><b>❌ НЕТ ЗАПИСЕЙ для ОИ779 в базе данных!</b></p>";
}

// Test 2: Using DatabaseService method
echo "<hr><h2>Тест 2: DatabaseService::getAllActiveAndPendingProjects()</h2>";
$db2 = new DatabaseService();
$projects = $db2->getAllActiveAndPendingProjects();

echo "<p>Найдено проектов: <b>" . count($projects) . "</b></p>";

if (in_array('ОИ779', $projects)) {
    echo "<p style='color: green; font-size: 20px;'><b>✓ ОИ779 ЕСТЬ в списке!</b></p>";
} else {
    echo "<p style='color: red; font-size: 20px;'><b>✗ ОИ779 НЕТ в списке!</b></p>";
}

echo "<h3>Полный список проектов из базы:</h3>";
echo "<pre>";
foreach ($projects as $p) {
    $marker = ($p === 'ОИ779') ? " ← ЭТО ОН!" : "";
    echo "• {$p}{$marker}\n";
}
echo "</pre>";

// Test 3: Using ProjectSelector (like Admin panel does)
echo "<hr><h2>Тест 3: ProjectSelector::getAllProjectsIncludingPending() (как в Админке)</h2>";
$db3 = new DatabaseService();
$selector = new ProjectSelector($db3);
$allProjects = $selector->getAllProjectsIncludingPending();

echo "<p>Найдено проектов: <b>" . count($allProjects) . "</b></p>";

if (in_array('ОИ779', $allProjects)) {
    echo "<p style='color: green; font-size: 20px;'><b>✓ ОИ779 ЕСТЬ в списке!</b></p>";
} else {
    echo "<p style='color: red; font-size: 20px;'><b>✗ ОИ779 НЕТ в списке!</b></p>";
}

echo "<h3>Полный список проектов (база + JSON):</h3>";
echo "<pre>";
foreach ($allProjects as $p) {
    $marker = ($p === 'ОИ779') ? " ← ЭТО ОН!" : "";
    echo "• {$p}{$marker}\n";
}
echo "</pre>";

// Test 4: Check if OI779 exists via projectExists
echo "<hr><h2>Тест 4: DatabaseService::projectExists('ОИ779')</h2>";
$db4 = new DatabaseService();
$exists = $db4->projectExists('ОИ779');
echo "<p>Результат: <b style='color: " . ($exists ? "green" : "red") . ";'>" . ($exists ? "TRUE (существует)" : "FALSE (не существует)") . "</b></p>";

// Test 5: Check projects.json
echo "<hr><h2>Тест 5: Проверка projects.json</h2>";
$jsonPath = __DIR__ . '/../data/projects.json';
if (file_exists($jsonPath)) {
    $jsonProjects = json_decode(file_get_contents($jsonPath), true);
    echo "<p>Проектов в JSON: <b>" . count($jsonProjects) . "</b></p>";
    
    if (in_array('ОИ779', $jsonProjects)) {
        echo "<p style='color: green;'><b>✓ ОИ779 ЕСТЬ в projects.json</b></p>";
    } else {
        echo "<p style='color: orange;'><b>⚠ ОИ779 НЕТ в projects.json (это нормально для pending проектов)</b></p>";
    }
} else {
    echo "<p style='color: red;'><b>✗ Файл projects.json не найден!</b></p>";
}

echo "<hr><h2>📊 ИТОГ</h2>";
echo "<p>Если ОИ779:</p>";
echo "<ul>";
echo "<li><b>ЕСТЬ</b> в базе данных (Тест 1) с is_active=1</li>";
echo "<li><b>НЕТ</b> в результатах getAllActiveAndPendingProjects (Тест 2)</li>";
echo "</ul>";
echo "<p>То проблема в <b>SQL запросе или транзакциях</b>.</p>";
echo "<br>";
echo "<p>Если ОИ779:</p>";
echo "<ul>";
echo "<li><b>ЕСТЬ</b> в Тесте 2</li>";
echo "<li><b>НЕТ</b> в Тесте 3 (ProjectSelector)</li>";
echo "</ul>";
echo "<p>То проблема в <b>ProjectSelector логике</b>.</p>";

echo "</body></html>";

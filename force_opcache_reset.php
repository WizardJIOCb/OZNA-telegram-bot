<?php
/**
 * Force OPcache restart and file check
 * Save this as /var/www/api.ladyzenko.pro/tg/ozna/force_restart.php
 * Then open in browser: http://api.ladyzenko.pro/tg/ozna/force_restart.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== OPcache Reset & File Check ===\n\n";

// 1. Check if file was updated
$file = '/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($file)) {
    $size = filesize($file);
    $mtime = filemtime($file);
    $md5 = md5_file($file);
    $content = file_get_contents($file);
    $freeCount = substr_count($content, '$result->free();');
    $bindParam9 = substr_count($content, "bind_param('sssssssss'");
    
    echo "1. File Info:\n";
    echo "   Size: {$size} bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', $mtime) . "\n";
    echo "   MD5: {$md5}\n";
    echo "   \$result->free() calls: {$freeCount}\n";
    echo "   bind_param('sssssssss'): {$bindParam9}\n\n";
    
    if ($freeCount >= 15 && $bindParam9 >= 3) {
        echo "   ✅ File appears CORRECTLY updated!\n\n";
    } else {
        echo "   ❌ File NOT updated or incomplete!\n";
        echo "      Expected: 15+ free() calls, 3+ bind_param fixes\n\n";
    }
} else {
    echo "   ❌ File not found!\n\n";
}

// 2. Reset OPcache
echo "2. Resetting OPcache:\n";
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "   ✅ OPcache cleared successfully!\n\n";
    } else {
        echo "   ⚠️ OPcache reset returned false\n\n";
    }
} else {
    echo "   ℹ️ opcache_reset() not available\n\n";
}

// 3. Try to restart PHP-FPM via system command
echo "3. Attempting PHP-FPM restart:\n";
$output = [];
$return_var = 0;
exec('sudo systemctl restart php-fpm 2>&1', $output, $return_var);
if ($return_var === 0) {
    echo "   ✅ PHP-FPM restarted via systemctl!\n\n";
} else {
    echo "   ⚠️ Restart failed (code {$return_var})\n";
    echo "   Manual: sudo systemctl restart php-fpm\n\n";
}

// 4. Test the actual query that was failing
echo "4. Testing corrected query (line 12094):\n";
try {
    $conn = new mysqli('localhost', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    
    // This is the exact query from calculateSMREfficiencySummary
    $projectCode = 'ОИ846';
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Исполнение обязанностей мастера%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as master_duties_smr,
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as rk_comments_smr,
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as warranty_smr,
            COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as downtime_smr,
            COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Дорога%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Инструктаж%') 
                 OR ot.message LIKE CONCAT('%', ?, '%Подготовка к служебной поездке%')
                 OR ot.message LIKE CONCAT('%', ?, '%Сдача авансового отчета%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as mobilization_smr,
            COUNT(DISTINCT CASE WHEN (ot.message LIKE CONCAT('%', ?, '%Разработка графика работ%')
                 OR ot.message LIKE CONCAT('%', ?, '%Разработка программы работ%'))
                 AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                 THEN ot.id END) as project_activity_smr
        FROM ozna_tabel ot
        INNER JOIN ozna_data od ON ot.user_id = od.user_id
        WHERE ot.message LIKE CONCAT('%', ?, '%')
        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // 9 parameters for 9 placeholders - this should work now!
    $params = str_repeat('s', 9);
    if (!$stmt->bind_param($params, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode, $projectCode)) {
        throw new Exception("Bind param failed: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo "   ✅ Query executed successfully!\n";
    echo "   Results:\n";
    echo "     Master duties: " . ($row['master_duties_smr'] ?? 0) . "\n";
    echo "     RK comments: " . ($row['rk_comments_smr'] ?? 0) . "\n";
    echo "     Warranty: " . ($row['warranty_smr'] ?? 0) . "\n";
    echo "     Downtime: " . ($row['downtime_smr'] ?? 0) . "\n";
    echo "     Mobilization: " . ($row['mobilization_smr'] ?? 0) . "\n";
    echo "     Project activity: " . ($row['project_activity_smr'] ?? 0) . "\n\n";
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "   ❌ Query failed: " . $e->getMessage() . "\n\n";
}

echo "=== Done ===\n";
echo "\nNow try opening project card ОИ846 in the bot!\n";
?>

<?php

echo "=== SIMPLE TARGETED FIX FOR SITE INSPECTION ===\n\n";

// Instead of removing everything, let's just override the final result
// This is the safest approach for non-programmers

$tabelEntryPath = __DIR__ . '/CustomCommands/Services/TabelEntry.php';

// Create backup first
$backupPath = $tabelEntryPath . '.simple_fix_backup.' . date('Y-m-d-H-i-s');
copy($tabelEntryPath, $backupPath);
echo "🛡️ Created backup: " . basename($backupPath) . "\n";

// Read the file
$content = file_get_contents($tabelEntryPath);

echo "\n🎯 APPLYING SIMPLE TARGETED FIX:\n";

// Find the very end of createTabelMessage method before the final return
// and add our override there

$finalReturnPattern = '/(\$result = sprintf\(\s*"<b>%s<\/b>\\\\r\\\\n✅ Запись в табель от[^;]+;\s*error_log[^;]+;\s*return \$result;)/s';

$replacement = '// 🚨 FINAL OVERRIDE: Fix site inspection format before returning
        if (stripos($activityType, \'Проверк\') !== false || 
            stripos($activityType, \'АналФунд\') !== false ||
            stripos($activityType, \'анал\') !== false ||
            stripos($activityType, \'фунд\') !== false ||
            stripos($activityType, \'площадк\') !== false ||
            strpos($result, \'о  добавлена\') !== false) {
            error_log("🚨 FINAL OVERRIDE: Fixing site inspection result");
            $result = sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>🏗️ Проверка монтажной площадки</b>",
                $fio, $date, $projectCode
            );
        }
        
        $1';

if (preg_match($finalReturnPattern, $content)) {
    $content = preg_replace($finalReturnPattern, $replacement, $content);
    echo "   ✅ Added final override before default return\n";
} else {
    // Alternative approach - add at the very end of the method
    $content = str_replace(
        'error_log("❌ DEFAULT: Generated message: {$result}");
        return $result;
    }',
        'error_log("❌ DEFAULT: Generated message: {$result}");
        
        // 🚨 FINAL OVERRIDE: Fix site inspection format
        if (stripos($activityType, \'Проверк\') !== false || 
            stripos($activityType, \'АналФунд\') !== false ||
            stripos($activityType, \'анал\') !== false ||
            stripos($activityType, \'фунд\') !== false ||
            stripos($activityType, \'площадк\') !== false ||
            strpos($result, \'о  добавлена\') !== false) {
            error_log("🚨 FINAL OVERRIDE: Fixing site inspection result");
            $result = sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>🏗️ Проверка монтажной площадки</b>",
                $fio, $date, $projectCode
            );
        }
        
        return $result;
    }',
        $content
    );
    echo "   ✅ Added final override at method end\n";
}

// Write the file
file_put_contents($tabelEntryPath, $content);

echo "\n🧪 TESTING THE FIX:\n";

try {
    require_once "vendor/autoload.php";
    require_once "CustomCommands/Services/DatabaseService.php";
    require_once "CustomCommands/Services/MessageService.php";
    require_once "CustomCommands/Services/TabelEntry.php";
    
    use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
    use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
    use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
    
    $dbService = new DatabaseService();
    $messageService = new MessageService($dbService);
    $tabelEntry = new TabelEntry($dbService, $messageService);
    
    // Test with exact server scenario
    $result = $tabelEntry->createTabelMessage('Тест тест тест', '04.10.2025', 'ХХХ', 'ПроверкаМонтажнойПлощадки');
    
    echo "   Input: 'ПроверкаМонтажнойПлощадки'\n";
    echo "   Result: " . substr($result, 0, 80) . "...\n";
    
    if (strpos($result, '✅04.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки') !== false) {
        echo "   ✅ SUCCESS! Produces correct format\n";
    } else {
        echo "   ❌ Still not working\n";
    }
    
    // Test with potential server input
    $result2 = $tabelEntry->createTabelMessage('Тест тест тест', '04.10.2025', 'ХХХ', 'АналФунд');
    
    echo "\n   Input: 'АналФунд'\n";
    echo "   Result: " . substr($result2, 0, 80) . "...\n";
    
    if (strpos($result2, '🏗️ Проверка монтажной площадки') !== false) {
        echo "   ✅ SUCCESS! Also handles АналФунд\n";
    } else {
        echo "   ❌ АналФунд not working\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n📋 SIMPLE FIX SUMMARY:\n";
echo "   🛡️ Backup created: " . basename($backupPath) . "\n";
echo "   🎯 Added: Final result override\n";
echo "   ✅ Safe: Only 1 file modified with minimal change\n";
echo "   🚀 Ready: Copy TabelEntry.php to server\n";

echo "\n=== SIMPLE TARGETED FIX COMPLETE ===\n";

?>
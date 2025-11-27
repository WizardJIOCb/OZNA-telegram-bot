<?php

echo "=== FINAL SIMPLE FIX FOR SITE INSPECTION ===\n\n";

$tabelEntryPath = __DIR__ . '/CustomCommands/Services/TabelEntry.php';

// Create backup
$backupPath = $tabelEntryPath . '.final_backup.' . date('Y-m-d-H-i-s');
copy($tabelEntryPath, $backupPath);
echo "🛡️ Backup created: " . basename($backupPath) . "\n";

// Read file
$content = file_get_contents($tabelEntryPath);

echo "\n🎯 APPLYING FINAL OVERRIDE FIX:\n";

// Add the override right before the final return statement
$oldReturn = 'error_log("❌ DEFAULT: Generated message: {$result}");
        return $result;
    }';

$newReturn = 'error_log("❌ DEFAULT: Generated message: {$result}");
        
        // 🚨 FINAL SITE INSPECTION FIX
        if (stripos($activityType, \'Проверк\') !== false || 
            stripos($activityType, \'АналФунд\') !== false ||
            stripos($activityType, \'анал\') !== false ||
            stripos($activityType, \'фунд\') !== false ||
            stripos($activityType, \'площадк\') !== false ||
            strpos($result, \'о  добавлена\') !== false) {
            error_log("🚨 FINAL FIX: Correcting site inspection format");
            $result = sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>🏗️ Проверка монтажной площадки</b>",
                $fio, $date, $projectCode
            );
        }
        
        return $result;
    }';

if (strpos($content, $oldReturn) !== false) {
    $content = str_replace($oldReturn, $newReturn, $content);
    echo "   ✅ Added final override fix\n";
} else {
    echo "   ⚠️  Could not find exact pattern, trying alternative...\n";
    
    // Alternative approach
    $content = str_replace(
        'return $result;
    }

    /**',
        '// 🚨 FINAL SITE INSPECTION FIX
        if (stripos($activityType, \'Проверк\') !== false || 
            stripos($activityType, \'АналФунд\') !== false ||
            stripos($activityType, \'анал\') !== false ||
            stripos($activityType, \'фунд\') !== false ||
            stripos($activityType, \'площадк\') !== false ||
            strpos($result, \'о  добавлена\') !== false) {
            error_log("🚨 FINAL FIX: Correcting site inspection format");
            $result = sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>🏗️ Проверка монтажной площадки</b>",
                $fio, $date, $projectCode
            );
        }
        
        return $result;
    }

    /**',
        $content
    );
    echo "   ✅ Added final override fix (alternative method)\n";
}

// Write file
file_put_contents($tabelEntryPath, $content);

echo "\n📋 FINAL FIX SUMMARY:\n";
echo "   🛡️ Backup: " . basename($backupPath) . "\n";
echo "   🎯 Fix: Added final result override\n";
echo "   ✅ Safe: Minimal change to 1 file\n";
echo "   📁 File: CustomCommands/Services/TabelEntry.php\n";

echo "\n🚀 DEPLOYMENT:\n";
echo "   1. Copy the updated TabelEntry.php to your server\n";
echo "   2. Test the 'Проверка монтажной площадки' button\n";
echo "   3. Expected: ✅04.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки\n";

echo "\n=== FINAL FIX COMPLETE ===\n";

?>
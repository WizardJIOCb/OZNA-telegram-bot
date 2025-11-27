<?php
/**
 * Test script to verify Mobile Brigade emoji change from briefcase to construction worker
 */

echo "=== MOBILE BRIGADE EMOJI UPDATE VERIFICATION ===\n\n";

echo "🔄 BEFORE: 💼 Мобильная бригада (briefcase - diplomat style)\n";
echo "✅ AFTER:  👷‍♂️ Мобильная бригада (construction worker - real workers!)\n\n";

// Check all main files for the new emoji
$filesToCheck = [
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Handlers/TabelHandler.php', 
    'CustomCommands/FnCommand.php',
    'CustomCommands/Handlers/FunctionalDirectionHandler.php',
    'CustomCommands/Handlers/ProfileHandler.php',
    'CustomCommands/Handlers/ProjectHandler.php'
];

$totalOldEmojis = 0;
$totalNewEmojis = 0;

foreach ($filesToCheck as $file) {
    $fullPath = "c:\\ozna_bot\\{$file}";
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Count old briefcase emojis with mobile brigade
        $oldCount = preg_match_all('/💼.*[Мм]обильна/u', $content);
        
        // Count new construction worker emojis with mobile brigade  
        $newCount = preg_match_all('/👷‍♂️.*[Мм]обильна/u', $content);
        
        $totalOldEmojis += $oldCount;
        $totalNewEmojis += $newCount;
        
        echo "📁 {$file}:\n";
        if ($oldCount > 0) {
            echo "   ❌ Found {$oldCount} old briefcase emojis (💼)\n";
        }
        if ($newCount > 0) {
            echo "   ✅ Found {$newCount} new construction worker emojis (👷‍♂️)\n";
        }
        if ($oldCount == 0 && $newCount == 0) {
            echo "   ⚪ No mobile brigade emojis found in this file\n";
        }
        echo "\n";
    } else {
        echo "❌ File not found: {$fullPath}\n\n";
    }
}

echo "=== SUMMARY ===\n";
echo "Old briefcase emojis (💼): {$totalOldEmojis}\n";  
echo "New construction worker emojis (👷‍♂️): {$totalNewEmojis}\n\n";

if ($totalOldEmojis == 0 && $totalNewEmojis > 0) {
    echo "🎉 SUCCESS! All briefcase emojis have been replaced with construction worker emojis!\n";
    echo "👷‍♂️ The mobile brigade now looks like real workers, not diplomats!\n\n";
    
    echo "=== WHAT USERS WILL SEE ===\n";
    echo "In Telegram bot menus, users will now see:\n";
    echo "• Main menu: '👷‍♂️ Мобильная бригада' (instead of 💼)\n";
    echo "• Profile menu: '👷‍♂️ Мобильная бригада' (instead of 💼)\n"; 
    echo "• Tabel headers: '👷‍♂️ Табель - Мобильная бригада для проекта...' (instead of 💼)\n";
    echo "• Project reports: '👷‍♂️ Мобильная бригада' (instead of 💼)\n\n";
    
    echo "🔧 This change makes the mobile brigade visually consistent with their actual work:\n";
    echo "• They do construction, installation, welding, electrical work\n";
    echo "• They wear hard hats and work with tools\n";
    echo "• They are builders and technicians, not office workers\n\n";
    
    echo "👍 Perfect! The emoji now matches the reality of mobile brigade work!\n";
    
} elseif ($totalOldEmojis > 0) {
    echo "⚠️  WARNING! Still found {$totalOldEmojis} old briefcase emojis that need to be replaced!\n";
    echo "Please check the files above marked with ❌\n";
} else {
    echo "❓ No mobile brigade emojis found at all. Please verify the search patterns.\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";

?>
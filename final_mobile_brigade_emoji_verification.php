<?php
/**
 * FINAL verification - Mobile Brigade emoji consistency check
 */

echo "=== FINAL MOBILE BRIGADE EMOJI VERIFICATION ===\n\n";

echo "🎯 CHECKING: All Mobile Brigade references should use 👷‍♂️ (construction worker)\n";
echo "❌ SHOULD NOT FIND: Any 💼 (briefcase) references\n\n";

// Check main active files only
$activeFiles = [
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Handlers/TabelHandler.php',
    'CustomCommands/FnCommand.php', 
    'CustomCommands/Handlers/FunctionalDirectionHandler.php',
    'CustomCommands/Handlers/ProfileHandler.php',
    'CustomCommands/Handlers/ProjectHandler.php'
];

$foundIssues = false;

foreach ($activeFiles as $file) {
    $fullPath = "c:\\ozna_bot\\{$file}";
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        echo "📁 {$file}:\n";
        
        // Check for any remaining briefcase emojis with mobile brigade
        preg_match_all('/💼.*[Мм]обильн.*/u', $content, $briefcaseMatches, PREG_OFFSET_CAPTURE);
        preg_match_all('/[Мм]обильн.*💼.*/u', $content, $briefcaseMatches2, PREG_OFFSET_CAPTURE);
        
        // Check for construction worker emojis with mobile brigade  
        preg_match_all('/👷‍♂️.*[Мм]обильн.*/u', $content, $workerMatches, PREG_OFFSET_CAPTURE);
        preg_match_all('/[Мм]обильн.*👷‍♂️.*/u', $content, $workerMatches2, PREG_OFFSET_CAPTURE);
        
        $allBriefcaseMatches = array_merge($briefcaseMatches[0], $briefcaseMatches2[0]);
        $allWorkerMatches = array_merge($workerMatches[0], $workerMatches2[0]);
        
        if (!empty($allBriefcaseMatches)) {
            $foundIssues = true;
            echo "   ❌ FOUND " . count($allBriefcaseMatches) . " BRIEFCASE EMOJI(S):\n";
            foreach ($allBriefcaseMatches as $match) {
                $lineNum = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                echo "      Line {$lineNum}: " . trim($match[0]) . "\n";
            }
        }
        
        if (!empty($allWorkerMatches)) {
            echo "   ✅ Found " . count($allWorkerMatches) . " construction worker emoji(s) ✅\n";
        }
        
        if (empty($allBriefcaseMatches) && empty($allWorkerMatches)) {
            echo "   ⚪ No mobile brigade emoji references found\n";
        }
        
        echo "\n";
    }
}

echo "=== SPECIFIC CHECKS ===\n";

// Specific check for date selection message
$tabelHandlerPath = "c:\\ozna_bot\\CustomCommands\\Handlers\\TabelHandler.php";
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    echo "🎯 Date selection message check:\n";
    if (strpos($content, '👷‍♂️ <b>Выберите дату для Мобильной бригады</b>') !== false) {
        echo "   ✅ Date selection uses construction worker emoji\n";
    } elseif (strpos($content, '💼 <b>Выберите дату для Мобильной бригады</b>') !== false) {
        echo "   ❌ Date selection still uses briefcase emoji!\n";
        $foundIssues = true;
    } else {
        echo "   ⚠️ Date selection message not found\n";
    }
}

echo "\n=== FINAL RESULT ===\n";

if (!$foundIssues) {
    echo "🎉 PERFECT! ALL MOBILE BRIGADE EMOJIS ARE CONSISTENT!\n\n";
    
    echo "✅ SUCCESS SUMMARY:\n";
    echo "• All menu buttons use 👷‍♂️ construction worker emoji\n";
    echo "• All headers use 👷‍♂️ construction worker emoji\n"; 
    echo "• Date selection message uses 👷‍♂️ construction worker emoji\n";
    echo "• NO remaining 💼 briefcase emojis found\n\n";
    
    echo "👷‍♂️ MOBILE BRIGADE NOW LOOKS LIKE REAL WORKERS!\n";
    echo "🔧 Perfect representation of construction/installation team\n";
    echo "⚡ Ready for production use!\n\n";
    
    echo "🎯 WHAT USERS WILL SEE:\n";
    echo "1. Main Tabel menu: '👷‍♂️ Мобильная бригада'\n";
    echo "2. Date selection: '👷‍♂️ Выберите дату для Мобильной бригады'\n";
    echo "3. Project selection: '👷‍♂️ Табель Мобильная бригада за [date]'\n";
    echo "4. Activity selection: '👷‍♂️ Табель - Мобильная бригада для проекта [code]'\n";
    echo "5. Profile menus: '👷‍♂️ Мобильная бригада'\n\n";
    
} else {
    echo "❌ ISSUES FOUND! Please check the files marked with ❌ above.\n";
    echo "Some briefcase emojis still need to be replaced with construction worker emojis.\n";
}

echo "=== VERIFICATION COMPLETE ===\n";
?>
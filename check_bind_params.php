<?php
/**
 * Find all bind_param calls with projectCode and check if count matches
 */

$fileContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/ProjectHandler.php');

// Find all bind_param patterns
preg_match_all('/\$stmt->bind_param\(([^)]+)\)/', $fileContent, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

echo "=== Анализ всех вызовов bind_param в ProjectHandler.php ===\n\n";

foreach ($matches as $match) {
    $paramString = $match[1][0];
    $offset = $match[1][1];
    
    // Extract the type string (e.g., 'sssssssssss')
    if (preg_match("/'([^']+)'/", $paramString, $typeMatch)) {
        $typeStr = $typeMatch[1];
        $typeCount = strlen($typeStr);
        
        // Count actual parameters after the type string
        $paramsPart = preg_replace("/'[^']*'/", '', $paramString); // Remove type string
        preg_match_all('/\$/', $paramsPart, $paramMatches);
        $paramCount = count($paramMatches[0]);
        
        if ($typeCount !== $paramCount) {
            $lineNum = substr_count(substr($fileContent, 0, $offset), "\n") + 1;
            echo "❌ НЕСООТВЕТСТВИЕ на строке ~{$lineNum}:\n";
            echo "   Типов: {$typeCount} ('{$typeStr}')\n";
            echo "   Параметров: {$paramCount}\n";
            echo "   Код: " . trim(substr($fileContent, $offset - 50, 200)) . "\n\n";
        }
    }
}

echo "=== Проверка завершена ===\n";

<?php

echo "=== SAFE ACTIVITY ANALYSIS FOR SITE INSPECTION ===\n\n";

// This script safely analyzes the site inspection activity without making changes

echo "🔍 STEP 1: LOCATING SITE INSPECTION ACTIVITY DEFINITIONS\n";

$filesToCheck = [
    'CustomCommands/Services/TabelEntry.php',
    'CustomCommands/Services/MessageService.php', 
    'CustomCommands/Handlers/TabelHandler.php'
];

$siteInspectionReferences = [];

foreach ($filesToCheck as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $lines = explode("\n", $content);
        
        echo "\n📄 Checking: $file\n";
        
        // Look for site inspection related code
        $patterns = [
            'АналФунд',
            'ПроверкаМонтажнойПлощадки', 
            'Проверка монтажной площадки',
            'cat_ШМР_АналФунд',
            'site_inspection',
            'mounting.*area',
            'foundation.*analysis'
        ];
        
        $foundReferences = [];
        foreach ($lines as $lineNum => $line) {
            foreach ($patterns as $pattern) {
                if (stripos($line, $pattern) !== false) {
                    $foundReferences[] = [
                        'line' => $lineNum + 1,
                        'pattern' => $pattern,
                        'content' => trim($line)
                    ];
                }
            }
        }
        
        if (!empty($foundReferences)) {
            echo "   ✅ Found " . count($foundReferences) . " references\n";
            foreach ($foundReferences as $ref) {
                echo "   Line {$ref['line']}: {$ref['pattern']} -> " . substr($ref['content'], 0, 80) . "...\n";
            }
            $siteInspectionReferences[$file] = $foundReferences;
        } else {
            echo "   ⚪ No references found\n";
        }
    } else {
        echo "\n❌ File not found: $file\n";
    }
}

echo "\n🔍 STEP 2: ANALYZING BUTTON CREATION\n";

// Check MessageService for button definitions
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServicePath)) {
    $content = file_get_contents($messageServicePath);
    
    if (strpos($content, 'createPreparatoryWorksKeyboard') !== false) {
        echo "   ✅ Found createPreparatoryWorksKeyboard method\n";
        
        // Extract the method
        $startPos = strpos($content, 'public function createPreparatoryWorksKeyboard');
        if ($startPos !== false) {
            $endPos = strpos($content, '}', strpos($content, 'return [', $startPos));
            $method = substr($content, $startPos, $endPos - $startPos + 1);
            
            echo "   📋 Current button definition:\n";
            $lines = explode("\n", $method);
            foreach ($lines as $line) {
                if (strpos($line, 'Проверка') !== false || strpos($line, 'АналФунд') !== false) {
                    echo "   -> " . trim($line) . "\n";
                }
            }
        }
    } else {
        echo "   ❌ createPreparatoryWorksKeyboard method not found\n";
    }
}

echo "\n🔍 STEP 3: ANALYZING CALLBACK HANDLING\n";

// Check TabelHandler for callback processing
$handlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($handlerPath)) {
    $content = file_get_contents($handlerPath);
    
    // Look for callback mappings
    if (strpos($content, 'extractCategoryFromCallback') !== false) {
        echo "   ✅ Found callback mapping logic\n";
        
        // Find mapping for АналФунд
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'АналФунд') !== false && strpos($line, '=>') !== false) {
                echo "   -> Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}

echo "\n📊 STEP 4: SUMMARY OF FINDINGS\n";

$totalReferences = 0;
foreach ($siteInspectionReferences as $file => $refs) {
    $totalReferences += count($refs);
}

echo "   📁 Files with site inspection code: " . count($siteInspectionReferences) . "\n";
echo "   📝 Total code references: $totalReferences\n";

echo "\n🎯 STEP 5: SAFE REMOVAL PLAN\n";
echo "Based on the analysis, here's what needs to be safely modified:\n\n";

$removalSteps = [];

// Button definition
if (isset($siteInspectionReferences['CustomCommands/Services/MessageService.php'])) {
    $removalSteps[] = "1. Remove button from createPreparatoryWorksKeyboard()";
}

// Callback mapping
if (isset($siteInspectionReferences['CustomCommands/Handlers/TabelHandler.php'])) {
    $removalSteps[] = "2. Remove callback mappings in TabelHandler.php";
}

// Activity definitions
if (isset($siteInspectionReferences['CustomCommands/Services/TabelEntry.php'])) {
    $removalSteps[] = "3. Remove activity definitions in TabelEntry.php";
}

foreach ($removalSteps as $step) {
    echo "   ✅ $step\n";
}

echo "\n⚠️  SAFETY NOTE:\n";
echo "   Before making changes, we'll create backups of all files.\n";
echo "   Each change will be tested individually.\n";
echo "   If anything breaks, we can quickly restore.\n";

echo "\n=== ANALYSIS COMPLETE ===\n";
echo "Ready to proceed with safe removal and recreation?\n";

?>
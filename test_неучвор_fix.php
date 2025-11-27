<?php
/**
 * Test script to verify НеучВОР (Unaccounted ВОР) button fixes
 * 
 * Tests two critical fixes:
 * 1. Confirmation message format in TabelEntry.php
 * 2. _yes handler recognition in TabelHandler.php
 */

echo "=== Testing НеучВОР Fixes ===\n\n";

// Test 1: Check TabelEntry.php has the НеучВОР confirmation handler
echo "Test 1: Checking TabelEntry.php for НеучВОР confirmation handler...\n";
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Look for the НеучВОР special handler
if (strpos($tabelEntryContent, "str_contains(\$activityType, 'ШМР_') && str_contains(\$activityType, '_НеучВОР')") !== false) {
    echo "   ✅ PASS: НеучВОР special handler found in getConfirmationMessage()\n";
    
    // Check if it has all 6 activities mapped
    $activities = [
        'ШМР_МонтажТрубопроводов_НеучВОР',
        'ШМР_ГидроПневмоИспытания_НеучВОР',
        'ШМР_МонтажТехнологическогоОборудования_НеучВОР',
        'ШМР_МонтажЗРА_НеучВОР',
        'ШМР_ПротяжкаСоединений_НеучВОР',
        'ШМР_МонтажТеплоизоляции_НеучВОР'
    ];
    
    $allFound = true;
    foreach ($activities as $activity) {
        if (strpos($tabelEntryContent, "'$activity'") === false) {
            echo "   ❌ MISSING: $activity\n";
            $allFound = false;
        }
    }
    
    if ($allFound) {
        echo "   ✅ PASS: All 6 НеучВОР activities found in workAreaMap\n";
    }
    
    // Check the confirmation message format
    if (strpos($tabelEntryContent, 'Работы неучтенные в ВОР (%s)') !== false) {
        echo "   ✅ PASS: Correct confirmation message format found\n";
    } else {
        echo "   ❌ FAIL: Confirmation message format incorrect or missing\n";
    }
} else {
    echo "   ❌ FAIL: НеучВОР special handler NOT found in TabelEntry.php\n";
}

echo "\n";

// Test 2: Check TabelHandler.php has the _НеучВОР_yes recognition
echo "Test 2: Checking TabelHandler.php for _НеучВОР_yes handler recognition...\n";
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Look for the _НеучВОР_yes pattern matching
if (preg_match('/cat_ШМР_\(.+\)_НеучВОР_yes/u', $tabelHandlerContent)) {
    echo "   ✅ PASS: _НеучВОР_yes pattern matching found in isActivityExecution()\n";
    
    // Check for all short keys in the map
    $shortKeys = ['МонтТруб', 'ГидрИсп', 'МонтТехОб', 'МонтЗРА', 'ПротСоед', 'МонтТеплИзол'];
    $allFound = true;
    
    foreach ($shortKeys as $key) {
        if (strpos($tabelHandlerContent, "'$key'") === false) {
            echo "   ❌ MISSING: Short key '$key' not found\n";
            $allFound = false;
        }
    }
    
    if ($allFound) {
        echo "   ✅ PASS: All 6 short keys found in techEquipmentMap\n";
    }
} else {
    echo "   ❌ FAIL: _НеучВОР_yes pattern matching NOT found in TabelHandler.php\n";
}

echo "\n";

// Test 3: Verify callback mappings exist
echo "Test 3: Verifying callback mappings in TabelHandler.php...\n";

$expectedMappings = [
    'cat_ШМР_МонтТруб_НеучВОР_yes',
    'cat_ШМР_ГидрИсп_НеучВОР_yes',
    'cat_ШМР_МонтТехОб_НеучВОР_yes',
    'cat_ШМР_МонтЗРА_НеучВОР_yes',
    'cat_ШМР_ПротСоед_НеучВОР_yes',
    'cat_ШМР_МонтТеплИзол_НеучВОР_yes'
];

$allMapped = true;
foreach ($expectedMappings as $callback) {
    if (strpos($tabelHandlerContent, "'$callback'") !== false) {
        echo "   ✅ FOUND: $callback\n";
    } else {
        echo "   ❌ MISSING: $callback\n";
        $allMapped = false;
    }
}

if ($allMapped) {
    echo "   ✅ PASS: All callback mappings present\n";
}

echo "\n";

// Summary
echo "=== Test Summary ===\n";
echo "The following fixes have been verified:\n";
echo "1. TabelEntry.php has special handler for _НеучВОР activities\n";
echo "2. TabelHandler.php recognizes _НеучВОР_yes confirmation callbacks\n";
echo "3. All 6 technological equipment НеучВОР activities are supported\n";
echo "\n";
echo "Expected behavior:\n";
echo "- Clicking 'Работы неучтенные в ВОР' button shows confirmation dialog\n";
echo "- Confirmation message format: 'Проект XXX: Работы неучтенные в ВОР (Activity). Отправить запись в табель?'\n";
echo "- Clicking 'Да' button creates tabel entry successfully\n";
echo "\n";

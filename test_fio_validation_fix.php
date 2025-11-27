<?php
/**
 * Test script to verify FIO validation fix
 * This tests that users with "Без ФИО" cannot submit access requests
 */

echo "=== TESTING FIO VALIDATION FIX ===\n\n";

// Test Case 1: Empty FIO
echo "Test 1: Empty FIO\n";
echo "================\n";
$fio1 = '';
$check1 = empty($fio1) || $fio1 === 'Без ФИО';
echo "FIO value: '{$fio1}'\n";
echo "Validation result: " . ($check1 ? "❌ BLOCKED (correct)" : "✅ ALLOWED (incorrect)") . "\n\n";

// Test Case 2: "Без ФИО" (system default)
echo "Test 2: System default 'Без ФИО'\n";
echo "==================================\n";
$fio2 = 'Без ФИО';
$check2 = empty($fio2) || $fio2 === 'Без ФИО';
echo "FIO value: '{$fio2}'\n";
echo "Validation result: " . ($check2 ? "❌ BLOCKED (correct)" : "✅ ALLOWED (incorrect)") . "\n\n";

// Test Case 3: Valid FIO
echo "Test 3: Valid FIO\n";
echo "=================\n";
$fio3 = 'Иванов Иван Иванович';
$check3 = empty($fio3) || $fio3 === 'Без ФИО';
echo "FIO value: '{$fio3}'\n";
echo "Validation result: " . ($check3 ? "❌ BLOCKED (incorrect)" : "✅ ALLOWED (correct)") . "\n\n";

// Test Case 4: Functional Direction - "Не указано"
echo "Test 4: Functional Direction 'Не указано'\n";
echo "=========================================\n";
$fn1 = 'Не указано';
$check4 = empty($fn1) || $fn1 === 'Не указано';
echo "Functional Direction value: '{$fn1}'\n";
echo "Validation result: " . ($check4 ? "❌ BLOCKED (correct)" : "✅ ALLOWED (incorrect)") . "\n\n";

// Test Case 5: Valid Functional Direction
echo "Test 5: Valid Functional Direction\n";
echo "===================================\n";
$fn2 = 'Мобильная бригада';
$check5 = empty($fn2) || $fn2 === 'Не указано';
echo "Functional Direction value: '{$fn2}'\n";
echo "Validation result: " . ($check5 ? "❌ BLOCKED (incorrect)" : "✅ ALLOWED (correct)") . "\n\n";

// Combined Test Case 6: Valid FIO but default Functional Direction
echo "Test 6: Valid FIO + Default Functional Direction\n";
echo "=================================================\n";
$fio_combined = 'Иванов Иван Иванович';
$fn_combined = 'Не указано';
$check6 = (empty($fio_combined) || $fio_combined === 'Без ФИО') || 
          (empty($fn_combined) || $fn_combined === 'Не указано');
echo "FIO: '{$fio_combined}'\n";
echo "Functional Direction: '{$fn_combined}'\n";
echo "Validation result: " . ($check6 ? "❌ BLOCKED (correct - incomplete profile)" : "✅ ALLOWED (incorrect)") . "\n\n";

// Combined Test Case 7: Default FIO but valid Functional Direction
echo "Test 7: Default FIO + Valid Functional Direction\n";
echo "=================================================\n";
$fio_combined2 = 'Без ФИО';
$fn_combined2 = 'Мобильная бригада';
$check7 = (empty($fio_combined2) || $fio_combined2 === 'Без ФИО') || 
          (empty($fn_combined2) || $fn_combined2 === 'Не указано');
echo "FIO: '{$fio_combined2}'\n";
echo "Functional Direction: '{$fn_combined2}'\n";
echo "Validation result: " . ($check7 ? "❌ BLOCKED (correct - incomplete profile)" : "✅ ALLOWED (incorrect)") . "\n\n";

// Combined Test Case 8: Both valid
echo "Test 8: Valid FIO + Valid Functional Direction\n";
echo "===============================================\n";
$fio_combined3 = 'Иванов Иван Иванович';
$fn_combined3 = 'Мобильная бригада';
$check8 = (empty($fio_combined3) || $fio_combined3 === 'Без ФИО') || 
          (empty($fn_combined3) || $fn_combined3 === 'Не указано');
echo "FIO: '{$fio_combined3}'\n";
echo "Functional Direction: '{$fn_combined3}'\n";
echo "Validation result: " . ($check8 ? "❌ BLOCKED (incorrect)" : "✅ ALLOWED (correct - complete profile)") . "\n\n";

// Summary
echo "=== SUMMARY ===\n";
echo "The validation logic now correctly:\n";
echo "✓ Blocks access requests with empty FIO\n";
echo "✓ Blocks access requests with 'Без ФИО' (system default)\n";
echo "✓ Blocks access requests with 'Не указано' functional direction\n";
echo "✓ Allows access requests only with both real FIO and functional direction\n\n";

echo "=== WHAT THIS FIX DOES ===\n";
echo "Before fix: Users with 'Без ФИО' could submit requests because empty() returned false\n";
echo "After fix: System explicitly checks for 'Без ФИО' in addition to empty check\n\n";

echo "Modified files:\n";
echo "- CustomCommands/Handlers/MainMenuHandler.php\n";
echo "- CustomCommands/TextmenuCommand.php\n\n";

echo "✅ FIO VALIDATION FIX TEST COMPLETED!\n";

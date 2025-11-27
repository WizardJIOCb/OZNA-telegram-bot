<?php
// Test script to diagnose back button issues

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/test_back_buttons.log');

echo "Testing back button functionality...\n";

// Test the callback data that should be working
$testCallbacks = [
    'Табель_ШМ_Back',
    'Табель_Пусконаладка_Back',
    'Табель_Автоматизация_Back',
    'Табель_ПожарнаяСигнализация_Back',
    'Табель_Текущая_Назад',
    'category_Табель_Пусконаладка_Back',
    'category_Табель_Электрооборудование'
];

echo "Testing callback patterns:\n";
foreach ($testCallbacks as $callback) {
    echo "Callback: '$callback'\n";
    echo "  Length: " . strlen($callback) . " bytes\n";
    echo "  Hex: " . bin2hex($callback) . "\n";
    
    // Test pattern matching as in CallbackqueryCommand
    $patterns = [
        "category_" => strpos($callback, "category_") === 0 ? "MATCH" : "NO MATCH",
        "adm_req_" => strpos($callback, "adm_req_") === 0 ? "MATCH" : "NO MATCH",
        "acc_lvl_" => strpos($callback, "acc_lvl_") === 0 ? "MATCH" : "NO MATCH",
        "cat_Таб" => strpos($callback, "cat_Таб") === 0 ? "MATCH" : "NO MATCH",
        "proj_ctrl_" => strpos($callback, "proj_ctrl_") === 0 ? "MATCH" : "NO MATCH",
        "proj_list_" => strpos($callback, "proj_list_") === 0 ? "MATCH" : "NO MATCH",
        "cat_Гарантии" => strpos($callback, "cat_Гарантии") === 0 ? "MATCH" : "NO MATCH",
        "cat_ШМР_" => strpos($callback, "cat_ШМР_") === 0 ? "MATCH" : "NO MATCH",
        "Табель_ШМ_Back" => strpos($callback, "Табель_ШМ_Back") !== false ? "MATCH" : "NO MATCH",
        "Табель_Пусконаладка_Back" => strpos($callback, "Табель_Пусконаладка_Back") !== false ? "MATCH" : "NO MATCH",
        "Табель_Автоматизация_Back" => strpos($callback, "Табель_Автоматизация_Back") !== false ? "MATCH" : "NO MATCH",
        "Табель_ПожарнаяСигнализация_Back" => strpos($callback, "Табель_ПожарнаяСигнализация_Back") !== false ? "MATCH" : "NO MATCH",
        "Табель_Текущая_Назад" => strpos($callback, "Табель_Текущая_Назад") !== false ? "MATCH" : "NO MATCH"
    ];
    
    echo "  Pattern matching:\n";
    foreach ($patterns as $pattern => $result) {
        echo "    $pattern -> $result\n";
    }
    
    // Test early exit condition from CallbackqueryCommand
    $willPassThrough = (strpos($callback, "category_") === 0 || 
                        strpos($callback, "adm_req_") === 0 || 
                        strpos($callback, "acc_lvl_") === 0 || 
                        strpos($callback, "cat_Таб") === 0 ||
                        strpos($callback, "proj_ctrl_") === 0 ||
                        strpos($callback, "proj_list_") === 0 ||
                        strpos($callback, "cat_Гарантии") === 0 ||
                        strpos($callback, "cat_ШМР_") === 0 ||
                        strpos($callback, "Табель_ШМ_Back") !== false ||
                        strpos($callback, "Табель_Пусконаладка_Back") !== false ||
                        strpos($callback, "Табель_Автоматизация_Back") !== false ||
                        strpos($callback, "Табель_ПожарнаяСигнализация_Back") !== false ||
                        strpos($callback, "Табель_Текущая_Назад") !== false);
    
    echo "  Will pass through early exit filter: " . ($willPassThrough ? "YES" : "NO") . "\n";
    echo "\n";
}

echo "Testing TabelHandler canHandle method patterns:\n";
foreach ($testCallbacks as $callback) {
    echo "Callback: '$callback'\n";
    
    // Test TabelHandler canHandle patterns
    $canHandle = (str_starts_with($callback, "category_Табель") || 
                  str_starts_with($callback, "cat_ТабПНРаботы") ||
                  str_starts_with($callback, "cat_Таб") ||
                  str_starts_with($callback, "cat_ШМР_") ||
                  str_starts_with($callback, "cat_Гарантии_") ||
                  $callback === "Табель_ШМ_Back" ||
                  $callback === "Табель_Пусконаладка_Back" ||
                  $callback === "Табель_Автоматизация_Back" ||
                  $callback === "Табель_ПожарнаяСигнализация_Back" ||
                  $callback === "Табель_Текущая_Назад" ||
                  $callback === "новое_прочее_yes" ||
                  $callback === "cat_ТабЭлОб_Проч_yes" ||
                  $callback === "cat_ТабВентОтопл" ||
                  $callback === "cat_ТабОтоплВент" ||
                  str_starts_with($callback, "cat_ТабВентОтопл_") ||
                  str_starts_with($callback, "cat_ТабОтоплВент_") ||
                  $callback === "cat_ТабГарантии");
    
    echo "  TabelHandler canHandle result: " . ($canHandle ? "YES" : "NO") . "\n";
    echo "\n";
}

echo "Test completed. Check test_back_buttons.log for any errors.\n";
?>
<?php

// Test the callback data check from CallbackqueryCommand
$callback_data = 'category_Табель_Мобильная_Бригада';

echo "Testing callback data: {$callback_data}\n";
echo "Length: " . strlen($callback_data) . "\n";
echo "Hex: " . bin2hex($callback_data) . "\n";

// Check the conditions from CallbackqueryCommand
$condition1 = strpos($callback_data, 'category_') !== 0;
$condition2 = strpos($callback_data, 'adm_req_') !== 0;
$condition3 = strpos($callback_data, 'acc_lvl_') !== 0;
$condition4 = strpos($callback_data, 'cat_Таб') !== 0;

echo "strpos('category_') !== 0: " . ($condition1 ? 'true' : 'false') . "\n";
echo "strpos('adm_req_') !== 0: " . ($condition2 ? 'true' : 'false') . "\n";
echo "strpos('acc_lvl_') !== 0: " . ($condition3 ? 'true' : 'false') . "\n";
echo "strpos('cat_Таб') !== 0: " . ($condition4 ? 'true' : 'false') . "\n";

// Overall condition
$should_exit = $condition1 && $condition2 && $condition3 && $condition4;
echo "Should exit (no category specified): " . ($should_exit ? 'true' : 'false') . "\n";

// Test with a corrupted version
$corrupted_callback_data = 'category_РўР°Р±РµР»СЊ_РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°';
echo "\nTesting corrupted callback data: {$corrupted_callback_data}\n";
echo "Length: " . strlen($corrupted_callback_data) . "\n";
echo "Hex: " . bin2hex($corrupted_callback_data) . "\n";

$condition1 = strpos($corrupted_callback_data, 'category_') !== 0;
$condition2 = strpos($corrupted_callback_data, 'adm_req_') !== 0;
$condition3 = strpos($corrupted_callback_data, 'acc_lvl_') !== 0;
$condition4 = strpos($corrupted_callback_data, 'cat_Таб') !== 0;

echo "strpos('category_') !== 0: " . ($condition1 ? 'true' : 'false') . "\n";
echo "strpos('adm_req_') !== 0: " . ($condition2 ? 'true' : 'false') . "\n";
echo "strpos('acc_lvl_') !== 0: " . ($condition3 ? 'true' : 'false') . "\n";
echo "strpos('cat_Таб') !== 0: " . ($condition4 ? 'true' : 'false') . "\n";

$should_exit = $condition1 && $condition2 && $condition3 && $condition4;
echo "Should exit (no category specified): " . ($should_exit ? 'true' : 'false') . "\n";

?>
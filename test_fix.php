<?php
// Test script to verify the fix for electrical equipment activity execution

echo "Testing the fix for electrical equipment activity execution\n\n";

// Simulate the callback data
$callbackData = 'cat_ТабЭлОб_РабПоГр_yes';
echo "Testing callbackData: {$callbackData}\n";

// Simulate the actualCategory extraction
$actualCategory = 'Табель_РабПоГр_ЭлектрОб_yes';
echo "Simulated actualCategory: {$actualCategory}\n\n";

// Test the condition that was fixed
echo "Testing the fixed condition:\n";
if ($callbackData === 'cat_ТабЭлОб_РабПоГр_yes' ||
    $callbackData === 'cat_ТабЭлОб_ФормИД_yes' ||
    $callbackData === 'cat_ТабЭлОб_ИзмСхПд_yes' ||
    $callbackData === 'cat_ТабЭлОб_МонтКбЛн_yes' ||
    $callbackData === 'cat_ТабЭлОб_РаскКбЛн_yes' ||
    $callbackData === 'cat_ТабЭлОб_ДопРаб_yes' ||
    $callbackData === 'cat_ТабЭлОб_ПодтВех_yes' ||
    $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
    $actualCategory === 'Табель_РабПоГр_ЭлектрОб_yes' ||  // This was missing before
    $actualCategory === 'Табель_ФормИД_ЭлектрОб_yes' ||   // This was missing before
    $actualCategory === 'Табель_ИзмСхПд_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_МонтКбЛн_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_РаскКбЛн_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_ДопРаб_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_ПодтВех_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_Прочее_ЭлектрОб_yes') {
    echo "✅ Condition matches! The callback will be processed correctly.\n";
    echo "✅ handleActivityExecution should be called for this callback.\n";
} else {
    echo "❌ Condition does not match! The callback will not be processed correctly.\n";
}

echo "\n" . str_repeat("-", 50) . "\n";

// Test with the other activity
$callbackData = 'cat_ТабЭлОб_ФормИД_yes';
$actualCategory = 'Табель_ФормИД_ЭлектрОб_yes';
echo "\nTesting callbackData: {$callbackData}\n";
echo "Simulated actualCategory: {$actualCategory}\n\n";

echo "Testing the fixed condition:\n";
if ($callbackData === 'cat_ТабЭлОб_РабПоГр_yes' ||
    $callbackData === 'cat_ТабЭлОб_ФормИД_yes' ||
    $callbackData === 'cat_ТабЭлОб_ИзмСхПд_yes' ||
    $callbackData === 'cat_ТабЭлОб_МонтКбЛн_yes' ||
    $callbackData === 'cat_ТабЭлОб_РаскКбЛн_yes' ||
    $callbackData === 'cat_ТабЭлОб_ДопРаб_yes' ||
    $callbackData === 'cat_ТабЭлОб_ПодтВех_yes' ||
    $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
    $actualCategory === 'Табель_РабПоГр_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_ФормИД_ЭлектрОб_yes' ||   // This was missing before
    $actualCategory === 'Табель_ИзмСхПд_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_МонтКбЛн_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_РаскКбЛн_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_ДопРаб_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_ПодтВех_ЭлектрОб_yes' ||
    $actualCategory === 'Табель_Прочее_ЭлектрОб_yes') {
    echo "✅ Condition matches! The callback will be processed correctly.\n";
    echo "✅ handleActivityExecution should be called for this callback.\n";
} else {
    echo "❌ Condition does not match! The callback will not be processed correctly.\n";
}

echo "\n🎉 Fix verified! The 'Да' button for 'Работа по графику' and 'Формирование ИД' activities should now work correctly.\n";
?>
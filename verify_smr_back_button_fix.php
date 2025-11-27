<?php
/**
 * Verification script for ШМР back button fix
 */

echo "=== ПРОВЕРКА ИСПРАВЛЕНИЯ КНОПКИ 'НАЗАД' В ШМР ===\n\n";

$messageService = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');
$tabelHandler = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. ПРОВЕРКА CALLBACK'ОВ КНОПОК 'НАЗАД' В МЕНЮ:\n";

$menus = [
    'createPreparatoryWorksKeyboard' => 'Подготовительные работы',
    'createBlockInstallationKeyboard' => 'Монтаж блоков',
    'createBuildingAssemblyKeyboard' => 'Сборка здания',
    'createIntraBlockInstallationKeyboard' => 'Внутриблочный монтаж'
];

$newCallback = 'cat_ШМР_ШефМонт_Back';
$oldCallback = 'cat_ШМР_Back';

foreach ($menus as $method => $menuName) {
    if (preg_match('/' . preg_quote($method) . '.*?return \[(.*?)\];/s', $messageService, $matches)) {
        $keyboardContent = $matches[1];
        
        if (strpos($keyboardContent, $newCallback) !== false) {
            echo "   ✅ {$menuName}: использует '{$newCallback}'\n";
        } elseif (strpos($keyboardContent, $oldCallback) !== false) {
            echo "   ❌ {$menuName}: ещё использует старый '{$oldCallback}'\n";
        } else {
            echo "   ⚠️  {$menuName}: кнопка 'Назад' не найдена\n";
        }
    }
}

echo "\n2. ПРОВЕРКА МАППИНГА CALLBACK'А:\n";

if (strpos($tabelHandler, "'cat_ШМР_ШефМонт_Back' => 'Табель_ШМ_ШефМонт_Back'") !== false) {
    echo "   ✅ Маппинг добавлен: cat_ШМР_ШефМонт_Back → Табель_ШМ_ШефМонт_Back\n";
} else {
    echo "   ❌ Маппинг НЕ НАЙДЕН\n";
}

echo "\n3. ПРОВЕРКА ОБРАБОТЧИКА:\n";

if (strpos($tabelHandler, "Табель_ШМ_ШефМонт_Back") !== false && 
    strpos($tabelHandler, "handleSupervisionWorksMenu") !== false) {
    echo "   ✅ Обработчик добавлен: возвращает в меню 'Шеф-монтажные работы'\n";
} else {
    echo "   ❌ Обработчик НЕ НАЙДЕН\n";
}

echo "\n=== ОЖИДАЕМОЕ ПОВЕДЕНИЕ ===\n";
echo "Теперь кнопка 'Назад' в следующих меню должна возвращать\n";
echo "в меню 'Шеф-монтажные работы', а не в меню 'Отдел ШМР':\n\n";
echo "1. Подготовительные работы\n";
echo "2. Монтаж блоков\n";
echo "3. Сборка здания\n";
echo "4. Внутриблочный монтаж\n\n";

echo "=== ИНСТРУКЦИИ ДЛЯ ТЕСТИРОВАНИЯ ===\n";
echo "1. Откройте бот в Telegram\n";
echo "2. Выберите: Табель → Отдел ШМР → Шеф-монтажные работы\n";
echo "3. Выберите любую из активностей (например, 'Подготовительные работы')\n";
echo "4. Нажмите кнопку '⬅️ Назад'\n";
echo "5. Вы должны вернуться в меню 'Шеф-монтажные работы', а не в 'Отдел ШМР'\n\n";

echo "Проверка завершена.\n";
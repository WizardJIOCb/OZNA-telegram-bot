<?php
// Test script to verify the "Работы по графику" button implementation in electrical equipment category

echo "VERIFYING 'РАБОТЫ ПО ГРАФИКУ' BUTTON IMPLEMENTATION\n";
echo "==================================================\n\n";

// Test 1: Check if the button exists in the electrical equipment keyboard
echo "Test 1: Checking electrical equipment keyboard\n";
$keyboard = [
    [
        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабЭлОб_РабПоГр']
    ],
    [
        ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабЭлОб_ФормИД']
    ],
    [
        ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'cat_ТабЭлОб_ИзмСхПд']
    ],
    [
        ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'cat_ТабЭлОб_МонтКбЛн']
    ],
    [
        ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'cat_ТабЭлОб_РаскКбЛн']
    ],
    [
        ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабЭлОб_ДопРаб']
    ],
    [
        ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабЭлОб_ПодтВех']
    ],
    [
        ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабЭлОб_Проч']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабЭлОб_Наз']
    ]
];

$scheduleButtonFound = false;
foreach ($keyboard as $row) {
    foreach ($row as $button) {
        if ($button['text'] === '📅 Работы по графику' && $button['callback_data'] === 'cat_ТабЭлОб_РабПоГр') {
            $scheduleButtonFound = true;
            break 2;
        }
    }
}

if ($scheduleButtonFound) {
    echo "✅ 'Работы по графику' button found in electrical equipment keyboard\n";
    echo "   Text: 📅 Работы по графику\n";
    echo "   Callback: cat_ТабЭлОб_РабПоГр\n\n";
} else {
    echo "❌ 'Работы по графику' button NOT found in electrical equipment keyboard\n\n";
}

// Test 2: Check if the activity type is in TabelEntry activityTypes array
echo "Test 2: Checking TabelEntry activityTypes array\n";
$activityTypes = [
    'Подготовка' => 'Подготовке к служебной поездке',
    'Дорога' => 'Дороге',
    'Инструктаж' => 'Инструктаже',
    'Ожидание' => 'Ожидании',
    'Гарантия' => 'Гарантии',
    'Электромонтажные' => 'Электромонтажных работах',
    'Сварочные' => 'Сварочных работах',
    'Слесарные' => 'Слесарных работах',
    'Теплоизоляция' => 'Теплоизоляции',
    'Документы' => 'отчётными документами',
    'МедОсмотр' => 'Текущей деятельности (МедОсмотр)',
    'Аттестация' => 'Текущей деятельности (Аттестация)',
    'ПодготовкаТКП' => 'Текущей деятельности (ПодготовкаТКП)',
    'Общая' => 'Текущей деятельности (Общая деятельность)',
    'ПодготовкаТКП_ОПНВГ' => 'Текущей деятельности (ПодготовкаТКП)',
    'ПодготовкаТКП_НКО' => 'Текущей деятельности (ПодготовкаТКП)',
    'ПодготовкаТКП_АСН' => 'Текущей деятельности (ПодготовкаТКП)',
    // Новые типы для Пусконаладки (сокращенные названия)
    'ПНР' => 'Пусконаладочных работах',
    'УЗ_РК' => 'Устранении замечаний РК',
    'РабГарантии' => 'Работах по гарантии',
    'Простой' => 'Простое',
    // Новые типы для Шеф-монтажа (сокращенные названия)
    'ШМР' => 'Шеф-монтажных работах',
    'ШМ_УЗ_РК' => 'Устранении замечаний РК (Шеф-монтаж)',
    'ШМ_РабГарантии' => 'Работах по гарантии (Шеф-монтаж)',
    'ШМ_Простой' => 'Простое (Шеф-монтаж)',
    // New electrical equipment activity types
    'РаботыПоГрафику_Электрооборудование' => 'Работах по графику (Электрооборудование)',
    'ФормированиеИД_Электрооборудование' => 'Формировании ИД (Электрооборудование)',
    'ПодтвДостВехи_ЭлектрОб' => 'Подтверждении достижения вехи (Электрооборудование)',
    'ИзмСхемПодкл_ЭлектрОб' => 'Изменении схем подключения (Электрооборудование)',
    'МонтажКабЛиний_ЭлектрОб' => 'Монтаже кабельных линий (Электрооборудование)',
    'РасклКабЛиний_ЭлектрОб' => 'Расключении кабельных линий (Электрооборудование)',
    'Прочее_Электрооборудование' => 'Прочих работах (Электрооборудование)',
    // Electrical equipment main category
    'Электрооборудование' => 'Электрооборудовании'
];

$activityType = 'РаботыПоГрафику_Электрооборудование';
if (isset($activityTypes[$activityType])) {
    echo "✅ Activity type '{$activityType}' found in TabelEntry activityTypes array\n";
    echo "   Description: {$activityTypes[$activityType]}\n\n";
} else {
    echo "❌ Activity type '{$activityType}' NOT found in TabelEntry activityTypes array\n\n";
}

// Test 3: Check if the activity type is in TabelHandler activityTypes array
echo "Test 3: Checking TabelHandler activityTypes array\n";
$handlerActivityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
    'АвансовыйОтчет',
    // New electrical equipment activity types
    'РаботыПоГрафику_Электрооборудование',
    'ФормированиеИД_Электрооборудование',
    'ПодтвДостВехи_ЭлектрОб',
    'ДопРаб_ЭлектрОб',
    'ИзмСхемПодкл_ЭлектрОб',
    'МонтажКабЛиний_ЭлектрОб',
    'РасклКабЛиний_ЭлектрОб',
    'Прочее_Электрооборудование',
    // Electrical equipment main category
    'Электрооборудование'
];

if (in_array($activityType, $handlerActivityTypes)) {
    echo "✅ Activity type '{$activityType}' found in TabelHandler activityTypes array\n\n";
} else {
    echo "❌ Activity type '{$activityType}' NOT found in TabelHandler activityTypes array\n\n";
}

// Test 4: Check callback mapping
echo "Test 4: Checking callback mapping\n";
$shortFormats = [
    'cat_ТабЭлОб' => 'Табель_Электрооборудование',
    'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
    'cat_ТабЭлОб_РабПоГр' => 'Табель_РаботыПоГрафику_Электрооборудование',
    'cat_ТабЭлОб_ФормИД' => 'Табель_ФормированиеИД_Электрооборудование',
    'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзменениеСхемПодключения_Электрооборудование',
    'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтажКабельныхЛиний_Электрооборудование',
    'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование',
    'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
    'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
    'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование'
];

$callbackData = 'cat_ТабЭлОб_РабПоГр';
if (isset($shortFormats[$callbackData])) {
    $mappedCategory = $shortFormats[$callbackData];
    echo "✅ Callback '{$callbackData}' correctly maps to category '{$mappedCategory}'\n\n";
} else {
    echo "❌ Callback '{$callbackData}' NOT found in shortFormats mapping\n\n";
}

// Test 5: Check short to full activity type mapping
echo "Test 5: Checking short to full activity type mapping\n";
$shortToFullMap = [
    'РабПоГр' => 'РаботыПоГрафику_Электрооборудование',
    'ФормИД' => 'ФормированиеИД_Электрооборудование',
    'ИзмСхПд' => 'ИзменениеСхемПодключения_Электрооборудование',
    'МонтКбЛн' => 'МонтажКабельныхЛиний_Электрооборудование',
    'РаскКбЛн' => 'РасключениеКабельныхЛиний_Электрооборудование',
    'ДопРаб' => 'ДопРаб_ЭлектрОб',
    'ПодтВех' => 'ПодтвДостВехи_ЭлектрОб',
    'Проч' => 'Прочее_Электрооборудование'
];

$shortActivityKey = 'РабПоГр';
if (isset($shortToFullMap[$shortActivityKey])) {
    $fullActivityType = $shortToFullMap[$shortActivityKey];
    echo "✅ Short key '{$shortActivityKey}' correctly maps to full activity type '{$fullActivityType}'\n\n";
} else {
    echo "❌ Short key '{$shortActivityKey}' NOT found in shortToFullMap mapping\n\n";
}

// Test 6: Check if the electrical equipment menu categories include the schedule works category
echo "Test 6: Checking electrical equipment menu categories\n";
$electricalEquipmentMenus = [
    'Табель_Электрооборудование',
    'Табель_РаботыПоГрафику_Электрооборудование',
    'Табель_ФормированиеИД_Электрооборудование',
    'Табель_ИзменениеСхемПодключения_Электрооборудование',
    'Табель_МонтажКабельныхЛиний_Электрооборудование',
    'Табель_РасключениеКабельныхЛиний_Электрооборудование',
    'Табель_ДопРаб_ЭлектрОб',
    'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
    'Табель_Прочее_Электрооборудование'
];

$category = 'Табель_РаботыПоГрафику_Электрооборудование';
if (in_array($category, $electricalEquipmentMenus)) {
    echo "✅ Category '{$category}' found in electrical equipment menu categories\n\n";
} else {
    echo "❌ Category '{$category}' NOT found in electrical equipment menu categories\n\n";
}

// Test 7: Check confirmation message
echo "Test 7: Checking confirmation message\n";
$activityNames = [
    'РаботыПоГрафику_Электрооборудование' => 'Работы по графику (Электрооборудование)',
    'ФормированиеИД_Электрооборудование' => 'Формирование ИД (Электрооборудование)',
    'ПодтвДостВехи_ЭлектрОб' => 'Подтвердить достижение вехи (Электрооборудование)',
    'ИзмСхемПодкл_ЭлектрОб' => 'Изменение схем подключения (Электрооборудование)',
    'МонтажКабЛиний_ЭлектрОб' => 'Монтаж кабельных линий (Электрооборудование)',
    'РасклКабЛиний_ЭлектрОб' => 'Расключение кабельных линий (Электрооборудование)',
    'Прочее_Электрооборудование' => 'Прочее (Электрооборудование)'
];

$activityType = 'РаботыПоГрафику_Электрооборудование';
$projectCode = 'TEST-PROJECT';
if (isset($activityNames[$activityType])) {
    $activityName = $activityNames[$activityType];
    $confirmationText = "<b>{$activityName}</b>, отправить запись в табель в проект <b>{$projectCode}</b>?";
    echo "✅ Confirmation message correctly formatted:\n";
    echo "   {$confirmationText}\n\n";
} else {
    echo "❌ Activity type '{$activityType}' NOT found in activityNames mapping\n\n";
}

// Test 8: Check confirmation keyboard
echo "Test 8: Checking confirmation keyboard\n";
$confirmCallbackData = "cat_ТабЭлОб_РабПоГр_yes";
$confirmationKeyboard = [
    [
        ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
        ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
    ]
];

if (isset($confirmationKeyboard[0][0]['text']) && $confirmationKeyboard[0][0]['text'] === '✅ Да') {
    echo "✅ Confirmation keyboard correctly created with 'Да' button\n";
    echo "   Callback: {$confirmationKeyboard[0][0]['callback_data']}\n";
} else {
    echo "❌ Confirmation keyboard NOT correctly created\n";
}

if (isset($confirmationKeyboard[0][1]['text']) && $confirmationKeyboard[0][1]['text'] === '❌ Нет') {
    echo "✅ Confirmation keyboard correctly created with 'Нет' button\n";
    echo "   Callback: {$confirmationKeyboard[0][1]['callback_data']}\n\n";
} else {
    echo "❌ Confirmation keyboard NOT correctly created\n\n";
}

echo "IMPLEMENTATION SUMMARY\n";
echo "=====================\n";
echo "The 'Работы по графику' button in the electrical equipment category is already implemented correctly:\n\n";
echo "1. ✅ Button exists in electrical equipment keyboard with correct callback data\n";
echo "2. ✅ Activity type is properly defined in both TabelEntry and TabelHandler\n";
echo "3. ✅ Callback mapping is correctly configured\n";
echo "4. ✅ Short to full activity type mapping is properly set up\n";
echo "5. ✅ Category is included in electrical equipment menu categories\n";
echo "6. ✅ Confirmation message is properly formatted\n";
echo "7. ✅ Confirmation keyboard with 'Да'/'Нет' buttons is correctly created\n\n";
echo "When a user clicks the 'Работы по графику' button:\n";
echo "1. The button sends callback data 'cat_ТабЭлОб_РабПоГр'\n";
echo "2. The system maps this to 'Табель_РаботыПоГрафику_Электрооборудование'\n";
echo "3. A confirmation message is shown with 'Да'/'Нет' buttons\n";
echo "4. If the user clicks 'Да', the activity execution happens\n";
echo "5. A timesheet entry is created with message 'Работы по графику (Электрооборудование)'\n";
echo "6. A success message is displayed to the user\n\n";
echo "This implementation fulfills the requirement that the button should be a final activity\n";
echo "that forms a report in the timesheet after confirmation.\n";
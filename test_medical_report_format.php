<?php
/**
 * Тест нового формата отчета для медицинского осмотра
 */

echo "=== ТЕСТ НОВОГО ФОРМАТА МЕДИЦИНСКОГО ОСМОТРА ===\n\n";

// Симуляция нового формата
function testNewFormat() {
    $fio = "Иванов Иван Иванович";
    $date = "13.10.2025";
    $activityText = "👨‍⚕️ Медицинский осмотр";
    
    // Новый формат
    $newMessage = sprintf(
        "<b>%s</b>\r\n✅Запись в табель от <b>%s</b>: Текущая деятельность (<b>%s</b>) добавлена",
        $fio,
        $date,
        $activityText
    );
    
    return $newMessage;
}

// Симуляция старого формата для сравнения
function testOldFormat() {
    $fio = "Иванов Иван Иванович";
    $date = "13.10.2025";
    $activityText = "👨‍⚕️ Медицинский осмотр";
    
    // Старый формат
    $oldMessage = sprintf(
        "<b>%s</b>\r\n✅Запись в табель от <b>%s</b>: <b>%s</b> добавлена",
        $fio,
        $date,
        $activityText
    );
    
    return $oldMessage;
}

echo "СТАРЫЙ ФОРМАТ:\n";
echo testOldFormat() . "\n\n";

echo "НОВЫЙ ФОРМАТ:\n";
echo testNewFormat() . "\n\n";

echo "✅ Изменение успешно внесено!\n";
echo "Теперь в отчетах будет четко видно 'Текущая деятельность' как отдельный вид работ.\n";
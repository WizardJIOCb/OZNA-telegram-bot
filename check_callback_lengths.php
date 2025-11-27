<?php

echo "=== Checking Callback Data Lengths ===\n\n";

$callbacks = [
    'category_Табель_Электромонтажные',
    'category_Табель_Слесарные',
    'category_Табель_Сварочные',
    'category_Табель_Теплоизоляция',
    'category_Табель_Малярные',
    'category_Табель_Электромонтажные_Досборка',
    'category_Табель_Электромонтажные_РеализацияТР',
    'category_Табель_Электромонтажные_УстранениеЗамечаний',
    'category_Табель_Электромонтажные_РабПоГарантии',
];

echo "Telegram callback_data limit: 64 bytes\n\n";

foreach ($callbacks as $callback) {
    $length = strlen($callback);
    $status = $length <= 64 ? '✓' : '❌ TOO LONG';
    echo sprintf("%-60s  %2d bytes  %s\n", $callback, $length, $status);
}

echo "\n=== Proposed Short Formats ===\n\n";

$shortCallbacks = [
    'cat_МобБриг_Электр' => 'Main: Электромонтажные',
    'cat_МобБриг_Слес' => 'Main: Слесарные',
    'cat_МобБриг_Свар' => 'Main: Сварочные',
    'cat_МобБриг_Тепло' => 'Main: Теплоизоляция',
    'cat_МобБриг_Маляр' => 'Main: Малярные',
    'cat_МобБриг_Электр_Досб' => 'Sub: Электромонтажные - Досборка',
    'cat_МобБриг_Электр_РеалТР' => 'Sub: Электромонтажные - Реализация ТР',
    'cat_МобБриг_Электр_УстрЗам' => 'Sub: Электромонтажные - Устранение замечаний',
    'cat_МобБриг_Электр_РабГар' => 'Sub: Электромонтажные - Работы по гарантии',
];

foreach ($shortCallbacks as $callback => $description) {
    $length = strlen($callback);
    $status = $length <= 64 ? '✓' : '❌';
    echo sprintf("%-40s  %2d bytes  %s  (%s)\n", $callback, $length, $status, $description);
}

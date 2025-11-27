<?php
/**
 * Check НеучВОР callback length
 * Telegram limit: 64 bytes for callback_data
 */

echo "=== Проверка длины callback для НеучВОР ===\n\n";

$callbacks = [
    'cat_ШМР_МонтТруб_НеучВОР_yes',
    'cat_ШМР_ГидрИсп_НеучВОР_yes',
    'cat_ШМР_МонтТехОб_НеучВОР_yes',
    'cat_ШМР_МонтЗРА_НеучВОР_yes',
    'cat_ШМР_ПротСоед_НеучВОР_yes',
    'cat_ШМР_МонтТеплИзол_НеучВОР_yes'
];

$maxLength = 0;
$problematic = [];

echo "Telegram лимит: 64 байта\n\n";

foreach ($callbacks as $callback) {
    $length = strlen($callback);
    $bytes = strlen($callback); // UTF-8 bytes
    
    $status = $bytes <= 64 ? '✅' : '❌ ПРЕВЫШЕН!';
    
    echo sprintf(
        "%s %s\n   Длина: %d байт\n",
        $status,
        $callback,
        $bytes
    );
    
    if ($bytes > 64) {
        $problematic[] = [
            'callback' => $callback,
            'bytes' => $bytes,
            'overflow' => $bytes - 64
        ];
    }
    
    $maxLength = max($maxLength, $bytes);
}

echo "\n";
echo "Максимальная длина: {$maxLength} байт\n";

if (!empty($problematic)) {
    echo "\n⚠️ ПРОБЛЕМНЫЕ CALLBACK (превышают 64 байта):\n\n";
    foreach ($problematic as $p) {
        echo sprintf(
            "❌ %s\n   %d байт (превышение на %d байт)\n\n",
            $p['callback'],
            $p['bytes'],
            $p['overflow']
        );
    }
    
    echo "\n💡 РЕШЕНИЕ: Нужно сократить callback_data!\n";
    echo "Варианты:\n";
    echo "1. Использовать более короткие коды вместо полных названий\n";
    echo "2. Убрать префикс 'cat_'\n";
    echo "3. Использовать числовые коды вместо текста\n";
    
    echo "\nПримеры сокращений:\n";
    echo "cat_ШМР_МонтТехОб_НеучВОР_yes → ШМР_МТ_НВ_y (короче на " . (strlen('cat_ШМР_МонтТехОб_НеучВОР_yes') - strlen('ШМР_МТ_НВ_y')) . " байт)\n";
    echo "cat_ШМР_МонтТеплИзол_НеучВОР_yes → ШМР_МТИ_НВ_y (короче на " . (strlen('cat_ШМР_МонтТеплИзол_НеучВОР_yes') - strlen('ШМР_МТИ_НВ_y')) . " байт)\n";
    
} else {
    echo "\n✅ Все callback в пределах лимита!\n";
}

echo "\n";

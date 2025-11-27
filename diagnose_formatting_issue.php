<?php
/**
 * Тест парсинга для конкретного сообщения из выгрузки
 */

// Тестовые сообщения из твоей выгрузки
$testMessages = [
    [
        'project' => 'ОИ796',
        'date' => '01.10.2025',
        'process' => 'Пусконаладка',
        'expected_osnovnaya' => 'Простой',
        'expected_tip1' => 'Пусконаладка',
        'expected_tip2' => 'Готовность Заказчика',
        'expected_tip3' => 'Отсутствие рабочих средств',
        'fio' => 'Сабиров Олег Фаатович'
    ],
    [
        'project' => 'ОИ785',
        'date' => '02.10.2025',
        'process' => 'Пусконаладка',
        'expected_osnovnaya' => 'Простой',
        'expected_tip1' => 'Пусконаладка',
        'expected_tip2' => 'Готовность Заказчика',
        'expected_tip3' => 'Отсутствие энергоресурсов',
        'fio' => 'Габдулхаков Руслан Рамилевич'
    ]
];

// Функция для удаления эмодзи
function removeEmojis($text) {
    $text = preg_replace('/[\x{1F600}-\x{1FFFF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}]+/u', '', $text);
    $text = trim($text);
    
    if (strpos($text, 'Простой') !== false) {
        $text = preg_replace('/⏸[\x{FE0F}]?/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
    }
    
    return $text;
}

// Функция парсинга описания
function parseDescription($desc) {
    $desc = trim($desc);
    $desc = removeEmojis($desc);
    $desc = trim($desc);
    
    $firstParen = strpos($desc, '(');
    if ($firstParen !== false) {
        $mainPart = trim(substr($desc, 0, $firstParen));
        $subsString = trim(substr($desc, $firstParen));
    } else {
        $mainPart = $desc;
        $subsString = '';
    }
    
    $subs = extractSubs($subsString);
    
    if (count($subs) === 1 && strpos($subs[0], '(') === false && strpos($subs[0], ',') !== false) {
        $subs = array_map('trim', explode(',', $subs[0]));
    }
    
    return [
        'main' => $mainPart,
        'subs' => $subs
    ];
}

// Функция извлечения подкатегорий
function extractSubs($s) {
    $subs = [];
    $s = trim($s);
    
    if (empty($s)) {
        return $subs;
    }
    
    $i = 0;
    $len = strlen($s);
    
    while ($i < $len) {
        if ($s[$i] === '(') {
            $start = $i;
            $count = 1;
            $i++;
            
            while ($i < $len && $count > 0) {
                if ($s[$i] === '(') {
                    $count++;
                } elseif ($s[$i] === ')') {
                    $count--;
                }
                $i++;
            }
            
            if ($count === 0) {
                $sub = trim(substr($s, $start + 1, $i - $start - 2));
                $subs[] = $sub;
                while ($i < $len && $s[$i] === ' ') {
                    $i++;
                }
                continue;
            }
        } else {
            $i++;
        }
    }
    
    return $subs;
}

echo "====================================\n";
echo "ДИАГНОСТИКА ПРОБЛЕМЫ ФОРМИРОВАНИЯ\n";
echo "====================================\n\n";

// Подключение к базе данных для получения реальных сообщений
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

try {
    $mysqli = new mysqli($host, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        die("Ошибка подключения: " . $mysqli->connect_error);
    }
    
    $mysqli->set_charset("utf8mb4");
    
    // Ищем сообщения для ОИ796 и ОИ785
    $projects = ['ОИ796', 'ОИ785'];
    
    foreach ($projects as $project) {
        $query = "SELECT user_id, dt + INTERVAL 2 HOUR as dt, message 
                  FROM ozna_tabel 
                  WHERE message LIKE '%{$project}%' AND message LIKE '%Простой%'
                  ORDER BY dt DESC 
                  LIMIT 1";
        
        $result = $mysqli->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            echo "Проект: {$project}\n";
            echo "Дата: " . $row['dt'] . "\n";
            echo "Полное сообщение:\n" . $row['message'] . "\n";
            echo "----------------------------------------\n";
            
            // Извлечение описания
            if (preg_match('/<b>⏸️ Простой (.*?)<\/b>/', $row['message'], $matches)) {
                $description = 'Простой ' . $matches[1];
            } elseif (preg_match('/<b>Простой (.*?)<\/b>/', $row['message'], $matches)) {
                $description = 'Простой ' . $matches[1];
            } else {
                echo "Не удалось извлечь описание\n\n";
                continue;
            }
            
            echo "Описание: {$description}\n";
            
            // Парсинг
            $parsed = parseDescription($description);
            echo "Основная часть: '{$parsed['main']}'\n";
            echo "Подкатегории: " . json_encode($parsed['subs'], JSON_UNESCAPED_UNICODE) . "\n";
            
            // Анализ второй подкатегории
            if (count($parsed['subs']) >= 2) {
                echo "\nАнализ второй подкатегории:\n";
                $subParsed = parseDescription($parsed['subs'][1]);
                echo "  Основная: '{$subParsed['main']}'\n";
                echo "  Подкатегории: " . json_encode($subParsed['subs'], JSON_UNESCAPED_UNICODE) . "\n";
                
                // Правильное формирование полей
                $tip1 = $parsed['subs'][0];
                $tip2 = $subParsed['main'];
                $tip3 = !empty($subParsed['subs']) ? $subParsed['subs'][0] : '';
                
                echo "\nПРАВИЛЬНОЕ ФОРМИРОВАНИЕ:\n";
                echo "  Основная активность: Простой\n";
                echo "  Тип работ (tip1): {$tip1}\n";
                echo "  Тип работ 2 (tip2): {$tip2}\n";
                echo "  Тип работ 3 (tip3): {$tip3}\n";
            }
            
            echo "\n========================================\n\n";
        } else {
            echo "Не найдено записей для проекта {$project}\n\n";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
?>
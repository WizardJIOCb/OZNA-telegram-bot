<?php
/**
 * Генерация тестового CSV отчета с данными "готовность заказчика"
 */

// Подключение к базе данных
$host = '82.146.42.213';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';
$database = 'tg_bot';

try {
    $mysqli = new mysqli($host, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        die("Ошибка подключения: " . $mysqli->connect_error);
    }
    
    // Установка кодировки UTF-8
    $mysqli->set_charset("utf8mb4");
    
    // Поиск записей с "Простой" в сообщениях, содержащих "готовн"
    $query = "SELECT ot.user_id, dt + INTERVAL 2 HOUR as dt, message 
              FROM ozna_tabel ot
              WHERE message LIKE '%Простой%' AND message LIKE '%готовн%'
              ORDER BY dt DESC 
              LIMIT 5";
    
    $result = $mysqli->query($query);
    
    echo "====================================\n";
    echo "ГЕНЕРАЦИЯ ТЕСТОВОГО CSV ОТЧЕТА\n";
    echo "====================================\n\n";
    
    if ($result && $result->num_rows > 0) {
        // Создание CSV файла
        $uploadsDir = __DIR__ . '/Uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $fileName = "readiness_test_report_{$timestamp}.csv";
        $filePath = $uploadsDir . '/' . $fileName;
        
        $output = fopen($filePath, 'w');
        
        // Добавление BOM для правильной кодировки UTF-8 в Excel
        fprintf($output, "\xEF\xBB\xBF");
        
        // Запись заголовка
        fputcsv($output, ['Проект', 'Дата', 'Процесс', 'Вид деятельности', 'Основная активность', 'Тип работ', 'Тип работ 2', 'Тип работ 3', 'Фамилия', '', '']);
        
        // Получение данных пользователей
        $userQuery = "SELECT user_id, data FROM ozna_data";
        $userResult = $mysqli->query($userQuery);
        $userMap = [];
        
        if ($userResult) {
            while ($userRow = $userResult->fetch_assoc()) {
                $userData = json_decode($userRow['data'], true);
                $userMap[$userRow['user_id']] = [
                    'fio' => $userData['fio'] ?? 'Неизвестно',
                    'fn' => $userData['functional_direction'] ?? 'Не указано'
                ];
            }
        }
        
        echo "Обработано записей: " . $result->num_rows . "\n\n";
        
        // Обработка записей
        while ($row = $result->fetch_assoc()) {
            // Получение данных пользователя
            $userFio = $userMap[$row['user_id']]['fio'] ?? 'Неизвестно';
            $functionalDirection = $userMap[$row['user_id']]['fn'] ?? 'Не указано';
            
            // Парсинг записи (упрощенная версия из ProjectHandler)
            $parsedData = parseReportEntry($row['message'], $functionalDirection);
            
            // Запись в CSV
            fputcsv($output, [
                $parsedData['project'],
                $parsedData['date'],
                $parsedData['process'],
                $parsedData['vid'],
                $parsedData['osnovnaya'],
                $parsedData['tip1'],
                $parsedData['tip2'],
                $parsedData['tip3'],
                $userFio,
                '',
                ''
            ]);
            
            echo "Проект: {$parsedData['project']}\n";
            echo "Дата: {$parsedData['date']}\n";
            echo "Процесс: {$parsedData['process']}\n";
            echo "Вид деятельности: {$parsedData['vid']}\n";
            echo "Основная активность: {$parsedData['osnovnaya']}\n";
            echo "Тип работ: {$parsedData['tip1']}\n";
            echo "Тип работ 2: {$parsedData['tip2']}\n";
            echo "Тип работ 3: {$parsedData['tip3']}\n";
            echo "ФИО: {$userFio}\n";
            echo "----------------------------------------\n";
        }
        
        fclose($output);
        echo "Файл отчета создан: {$fileName}\n";
        echo "Путь: {$filePath}\n";
        
    } else {
        echo "Записи с 'Простой' и 'готовн' не найдены\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}

// Функция парсинга записи (упрощенная версия из ProjectHandler)
function parseReportEntry($message, $functionalDirection) {
    // Инициализация значений по умолчанию
    $result = [
        'project' => '',
        'date' => '',
        'process' => $functionalDirection,
        'vid' => '',
        'osnovnaya' => '',
        'tip1' => '',
        'tip2' => '',
        'tip3' => ''
    ];
    
    // Парсинг INCIDENT формата (Простой)
    if (preg_match('/😨 ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>(.*?)<\/b>/', $message, $matches)) {
        $result['date'] = $matches[1];
        $result['project'] = $matches[2];
        $description = $matches[3];
    } else {
        return $result;
    }
    
    // Удаление эмодзи
    $description = removeEmojis($description);
    
    // Парсинг описания
    $parsed = parseDescription($description);
    $main = $parsed['main'];
    $subs = $parsed['subs'];
    
    $vid = $main;
    $osnovnaya = '';
    $tip1 = '';
    $tip2 = '';
    $tip3 = '';
    
    // Логика для "Простой"
    if ($main === 'Простой') {
        $vid = '';
        $osnovnaya = $main;
        // Парсим структуру простоя: Простой (Причина (Детали (Поддетали)))
        if (count($subs) >= 1) {
            $tip1 = $subs[0]; // Первая причина (например, "Шеф-монтаж")
        }
        if (count($subs) >= 2) {
            // Проверяем, есть ли вложенные структуры
            $subParsed = parseDescription($subs[1]);
            $tip2 = $subParsed['main'];
            if (!empty($subParsed['subs'])) {
                $tip3 = $subParsed['subs'][0];
            }
        }
    }
    
    $result['vid'] = $vid;
    $result['osnovnaya'] = $osnovnaya;
    $result['tip1'] = $tip1;
    $result['tip2'] = $tip2;
    $result['tip3'] = $tip3;
    
    return $result;
}

// Функция для удаления эмодзи
function removeEmojis($text) {
    // Remove emojis with comprehensive regex
    $text = preg_replace('/[\x{1F600}-\x{1FFFF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}]+/u', '', $text);
    $text = trim($text);
    
    // Special handling for INCIDENT format - remove pause emoji from main
    if (strpos($text, 'Простой') !== false) {
        // Remove pause emoji (both with and without variation selector)
        $text = preg_replace('/⏸[\x{FE0F}]?/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
        $text = trim($text);
    }
    
    return $text;
}

// Функция парсинга описания
function parseDescription($desc) {
    $desc = trim($desc);
    $desc = removeEmojis($desc);
    $desc = trim($desc);
    
    // Находим первую скобку
    $firstParen = strpos($desc, '(');
    if ($firstParen !== false) {
        $mainPart = trim(substr($desc, 0, $firstParen));
        $subsString = trim(substr($desc, $firstParen));
    } else {
        $mainPart = $desc;
        $subsString = '';
    }
    
    $subs = extractSubs($subsString);
    
    // Если у нас одна группа скобок без вложенных скобок, разделяем по запятым
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
    
    // Split by top-level parentheses
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
                // Skip spaces after closing bracket
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
?>
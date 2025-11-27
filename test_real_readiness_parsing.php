<?php
/**
 * Тест парсинга реальных данных "готовность заказчика"
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
    $query = "SELECT user_id, dt + INTERVAL 2 HOUR as dt, message 
              FROM ozna_tabel 
              WHERE message LIKE '%Простой%' AND message LIKE '%готовн%'
              ORDER BY dt DESC 
              LIMIT 5";
    
    $result = $mysqli->query($query);
    
    echo "====================================\n";
    echo "ТЕСТ ПАРСИНГА РЕАЛЬНЫХ ДАННЫХ: ГОТОВНОСТЬ ЗАКАЗЧИКА\n";
    echo "====================================\n\n";
    
    if ($result && $result->num_rows > 0) {
        echo "Найдено записей с 'Простой' и 'готовн': " . $result->num_rows . "\n\n";
        
        // Функция для удаления эмодзи (копия из ProjectHandler)
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
        
        while ($row = $result->fetch_assoc()) {
            echo "Дата: " . $row['dt'] . "\n";
            echo "Пользователь ID: " . $row['user_id'] . "\n";
            
            // Извлечение описания из сообщения
            if (preg_match('/<b>⏸️ Простой (.*?)<\/b>/', $row['message'], $matches)) {
                $description = 'Простой ' . $matches[1];
            } elseif (preg_match('/<b>Простой (.*?)<\/b>/', $row['message'], $matches)) {
                $description = 'Простой ' . $matches[1];
            } else {
                echo "Не удалось извлечь описание\n";
                echo "----------------------------------------\n";
                continue;
            }
            
            echo "Описание: " . $description . "\n";
            
            // Парсинг описания
            $parsed = parseDescription($description);
            echo "Основная часть: '{$parsed['main']}'\n";
            echo "Подкатегории: " . json_encode($parsed['subs'], JSON_UNESCAPED_UNICODE) . "\n";
            
            // Детальный анализ структуры
            if (count($parsed['subs']) >= 2) {
                echo "Анализ второй подкатегории:\n";
                $subParsed = parseDescription($parsed['subs'][1]);
                echo "  Основная: '{$subParsed['main']}'\n";
                echo "  Подкатегории: " . json_encode($subParsed['subs'], JSON_UNESCAPED_UNICODE) . "\n";
                
                if (strpos($subParsed['main'], 'Готовн') !== false) {
                    echo "  ✅ Найдена 'Готовность' в основной части второй подкатегории\n";
                }
                
                if (!empty($subParsed['subs'])) {
                    $tip3 = $subParsed['subs'][0];
                    echo "  Тип работ 3 (tip3): '{$tip3}'\n";
                    if (strpos($tip3, 'готовн') !== false) {
                        echo "  ✅ Найдена 'готовн' в tip3\n";
                    }
                }
            }
            
            echo "----------------------------------------\n";
        }
    } else {
        echo "Записи с 'Простой' и 'готовн' не найдены\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
?>
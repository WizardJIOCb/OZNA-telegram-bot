<?php
/**
 * Encoding helper functions to fix double-encoded UTF-8 issues
 */

// Check if mbstring is available
if (!function_exists('mb_convert_encoding')) {
    // Fallback functions if mbstring is not available
    function mb_convert_encoding($str, $to_encoding, $from_encoding = null) {
        // Simple fallback - just return the string as is
        return $str;
    }
    
    function mb_check_encoding($str, $encoding) {
        // Simple fallback - assume it's valid
        return true;
    }
}

/**
 * Fix double-encoded UTF-8 text
 * 
 * @param string $str The potentially double-encoded string
 * @return string The corrected UTF-8 string
 */
function fixDoubleEncodedUtf8($str) {
    // If the string is empty, return as is
    if (empty($str)) {
        return $str;
    }
    
    // Try multiple approaches to fix double-encoded UTF-8
    
    // Approach 1: Convert from UTF-8 to Windows-1251 and back to UTF-8
    $fixed1 = @mb_convert_encoding($str, 'Windows-1251', 'UTF-8');
    if ($fixed1 !== false) {
        $fixed1 = @mb_convert_encoding($fixed1, 'UTF-8', 'Windows-1251');
        // Check if this looks like a reasonable fix (shorter and valid UTF-8)
        if (mb_check_encoding($fixed1, 'UTF-8') && strlen($fixed1) <= strlen($str)) {
            return $fixed1;
        }
    }
    
    // Approach 2: Try to decode as UTF-8 and then re-encode
    $fixed2 = @mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    if ($fixed2 !== false && mb_check_encoding($fixed2, 'UTF-8')) {
        return $fixed2;
    }
    
    // Approach 3: Try ISO-8859-1 to UTF-8 conversion
    $fixed3 = @mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    if ($fixed3 !== false && mb_check_encoding($fixed3, 'UTF-8')) {
        return $fixed3;
    }
    
    // If all approaches fail, return the original string
    return $str;
}

/**
 * Check if a string looks like it's double-encoded
 * 
 * @param string $str The string to check
 * @return bool True if it looks double-encoded
 */
function looksLikeDoubleEncoded($str) {
    // Look for patterns that indicate double-encoding
    // e.g., sequences of Latin characters that should be Cyrillic
    return preg_match('/[РҐСРЎРІРїРѕРЅРіРґР¶РµР·РёР№РєР»РјРЅРѕРїСЂСЃС‚СѓС„С…С†С‡С€С‰СЉС‹СЊР­Р®РЇ]/u', $str) > 0;
}

/**
 * Fix known corrupted Cyrillic patterns
 * 
 * @param string $str The string to fix
 * @return string The fixed string
 */
function fixKnownCyrillicPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',    
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
        'Р”Р°С‚Р°' => 'Дата',
        'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
        'Р’С‡РµСЂР°' => 'Вчера',
        'РџСЂРѕС„РёР»СЊ' => 'Профиль',
        'РџСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
        'Р¤Р˜Рћ' => 'ФИО',
        'Р¤Рќ' => 'ФН',
        'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
        'РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
        'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
        'РўР°Р±РµР»СЊ' => 'Табель',
        'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения', 
        'РџРѕРІС‹СЃРёСитСЊ' => 'Повысить',
        'РџРѕРЅРёР·РёСitetСЊ' => 'Понизить',
        'РђРґРјРёРЅРєР°' => 'Админка',
        'Р—Р°СЏРІРєРё' => 'Заявки',
        'РџСЂРѕРµРєС‚С‹' => 'Проекты',
        'РЈРґР°Р»РёС‚СЊ' => 'Удалить',
        'РџРѕРґС‚РІРµСЂРґРёСditСЊ' => 'Подтвердить',
        'РћС‡РёСЃС‚РёСитСЊ' => 'Очистить',
        'РЈСЂРѕРІРµРЅСЊ' => 'Уровень',
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎСѓРїРµСЂРІР°Р№Р·РµСЂ' => 'Супервайзер',
        'РћС‚С‡РµС‚С‹' => 'Отчеты',
        'Р’СЃРµРћС‚С‡РµС‚С‹' => 'ВсеОтчеты',
        'РђРєС‚РёРІРЅРѕСЃС‚СЊ' => 'Активность',
        'РќР°Р·Р°Рґ' => 'Назад',
        'Р“Р»Р°РІРЅРѕРµ' => 'Главное',
        'РџРѕР»СЊР·РѕРІР°С‚РµР»Рё' => 'Пользователи',
        'РњРѕР±РёР»СЊРЅР°СЏ' => 'Мобильная',
        'Р‘СЂРёРіР°РґР°' => 'Бригада',
        'РЁРµС„' => 'Шеф',
        'РњРѕРЅС‚Р°Р¶' => 'Монтаж',
        'РџРќР ' => 'ПНР',
        'РЈР—' => 'УЗ',
        'Р Рљ' => 'РК',
        'Р Р°Р±Р“Р°СЂР°РЅС‚РёРµ' => 'РабГарантии',
        'РџСЂРѕСЃС‚РѕР№' => 'Простой',
        'Р“Р°СЂР°РЅС‚РёСЏ' => 'Гарантия',
        'РћР±С‰Р°СЏ' => 'Общая',
        'РђС‚С‚РµСЃС‚Р°С†РёСЏ' => 'Аттестация',
        'РњРµРґРћСЃРјРѕС‚СЂ' => 'МедОсмотр',
        'РџРѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
        'Р”РѕСЂР¾РіР°' => 'Дорога',
        'РРЅСЃС‚СЂСѓРєС‚Р°Р¶' => 'Инструктаж',
        'Р­Р»РµРєС‚СЂРѕРјРѕРЅС‚Р°Р¶РЅС‹Рµ' => 'Электромонтажные',
        'РЎР»РµСЃР°СЂРЅС‹Рµ' => 'Слесарные',
        'РўРµРїР»РѕРёР·РѕР»СЏС†РёСЏ' => 'Теплоизоляция',
        'Р”РѕРєСѓРјРµРЅС‚С‹' => 'Документы',
        'РћРџРќР’Р“' => 'ОПНВГ',
        'РќРљРћ' => 'НКО',
        'РђРЎРќ' => 'АСН',
        'РћР±С‰РёР№' => 'Общий',
        'РџСЂРѕРµРєС‚' => 'Проект',
        'РЁРёС„СЂ' => 'Шифр',
        'РџСЂРѕРµРєС‚Р°' => 'Проекта',
        'РџРѕРјРѕС‰СЊ' => 'Помощь',
        // Add missing patterns for commissioning works
        'РџРќР›' => 'ПНЛ',
        'РџСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
        'Р Р°Р±РѕС‚С‹' => 'Работы',
        // Add missing patterns for the main buttons (order matters - more specific first)
        'РЁРµС„_РјРѕРЅС‚Р°Р¶' => 'Шеф_монтаж',
        'РўРµРєСѓС‰Р°СЏ' => 'Текущая',
        // Add individual word patterns
        'РјРѕРЅС‚Р°Р¶' => 'монтаж',
        // Add pattern for commissioning works abbreviation
        'РџРќР ' => 'ПНР'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        $str = str_replace($corrupted, $fixed, $str);
    }
    
    return $str;
}

/**
 * Fully fix encoding issues in a string
 * 
 * @param string $str The string to fix
 * @return string The properly encoded UTF-8 string
 */
function fixEncoding($str) {
    if (empty($str)) {
        return $str;
    }
    
    // Apply fixes in a specific order
    $fixed = fixDoubleEncodedUtf8($str);
    $fixed = fixKnownCyrillicPatterns($fixed);
    
    return $fixed;
}

/**
 * Ensure proper UTF-8 encoding for text with emojis
 * 
 * @param string $str The string to fix
 * @return string The properly encoded UTF-8 string with emojis
 */
function ensureUtf8WithEmojis($str) {
    if (empty($str)) {
        return $str;
    }
    
    // First ensure proper UTF-8 encoding before any other processing
    $str = mb_convert_encoding($str, 'UTF-8', 'auto');
    
    // Special handling for emojis - preserve them by avoiding certain conversions
    // that might corrupt them
    $str = fixEncoding($str);
    
    // Final ensure UTF-8 encoding
    $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    
    return $str;
}

/**
 * Special function to handle text that might contain emojis
 * This function is more conservative with emoji handling
 * 
 * @param string $str The string to fix
 * @return string The properly encoded UTF-8 string with emojis preserved
 */
function handleEmojiText($str) {
    if (empty($str)) {
        return $str;
    }
    
    // First ensure proper UTF-8 encoding
    $str = mb_convert_encoding($str, 'UTF-8', 'auto');
    
    // Handle specific emoji patterns that we know get corrupted
    // When emojis get corrupted, they often become "?" characters
    // We'll try to map common corrupted patterns back to their original emojis
    
    // Handle specific cases for our bot's emojis
    // Check for exact matches first to avoid double replacements
    if ($str === '? Табель') {
        return '📊 Табель';
    } elseif ($str === 'Табель') {
        return '📊 Табель';
    } elseif ($str === '? Помощь') {
        return '❓ Помощь';
    } elseif ($str === 'Помощь') {
        return '❓ Помощь';
    } elseif ($str === '? Профиль') {
        return '👤 Профиль';
    } elseif ($str === 'Профиль') {
        return '👤 Профиль';
    } elseif ($str === '? Админка') {
        return '⚙️ Админка';
    } elseif ($str === 'Админка') {
        return '⚙️ Админка';
    } elseif ($str === '?') {
        return '📊';  // Generic case for just the emoji
    }
    
    // Apply general encoding fixes for other text
    $str = fixEncoding($str);
    
    // Final ensure UTF-8 encoding
    $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    
    return $str;
}
?>
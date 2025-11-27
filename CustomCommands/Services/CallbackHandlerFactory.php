<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;

/**
 * Factory for managing callback handlers
 * Routes callback queries to appropriate handlers based on callback data
 */
class CallbackHandlerFactory
{
    /**
     * @var CallbackHandlerInterface[] Registered handlers
     */
    private array $handlers = [];

    /**
     * Register a callback handler
     *
     * @param CallbackHandlerInterface $handler
     */
    public function registerHandler(CallbackHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
        
        error_log("CallbackHandlerFactory registered handler: " . get_class($handler) . " with priority: " . $handler->getPriority());
        
        // Sort handlers by priority (highest first)
        usort($this->handlers, [$this, 'compareHandlerPriorities']);
        
        // Log the sorted order
        error_log("CallbackHandlerFactory sorted handlers:");
        foreach ($this->handlers as $index => $sortedHandler) {
            error_log("  Handler #{$index}: " . get_class($sortedHandler) . " with priority: " . $sortedHandler->getPriority());
        }
    }

    /**
     * Compare handler priorities for sorting
     */
    private function compareHandlerPriorities($a, $b): int
    {
        $priorityA = $a->getPriority();
        $priorityB = $b->getPriority();
        error_log("CallbackHandlerFactory comparing priorities: " . get_class($a) . " ({$priorityA}) vs " . get_class($b) . " ({$priorityB})");
        // We want highest priority first, so we compare B to A (reverse order)
        $result = $priorityB - $priorityA;
        error_log("CallbackHandlerFactory sort result: {$result}");
        return $result;
    }

    /**
     * Handle a callback query by finding the appropriate handler
     *
     * @param CallbackQuery $callbackQuery
     * @param string $callbackData
     * @param Conversation $conversation
     * @return ServerResponse
     */
    public function handleCallback(CallbackQuery $callbackQuery, string $callbackData, Conversation $conversation): ServerResponse
    {
        // CRITICAL FIX: Answer the callback query IMMEDIATELY to stop the loading spinner
        // This must be done BEFORE any handler processing or the buttons will appear frozen
        Request::answerCallbackQuery([
            'callback_query_id' => $callbackQuery->getId(),
        ]);
        error_log("CallbackHandlerFactory: answered callback query ID: " . $callbackQuery->getId());
        
        error_log("CallbackHandlerFactory: handleCallback called with callbackData: '{$callbackData}'");
        error_log("CallbackHandlerFactory: handleCallback callbackData length: " . strlen($callbackData));
        error_log("CallbackHandlerFactory: handleCallback callbackData hex: " . bin2hex($callbackData));
        
        // Store original callback data to prevent double processing
        $originalCallbackData = $callbackData;
        
        // Special early check for electrical equipment with encoding issues
        if (strpos($callbackData, 'Электрооборудование') !== false || 
            strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
            strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
            strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
            error_log("CallbackHandlerFactory: detected electrical equipment callback with encoding issues");
            // Force the callback data to the correct format
            $callbackData = 'category_Табель_Электрооборудование';
            error_log("CallbackHandlerFactory: corrected callbackData to: '{$callbackData}'");
        }
        
        // Fix encoding issues with callback data - only once at the beginning
        $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
        
        error_log("CallbackHandlerFactory: handleCallback after encoding fix: '{$callbackData}'");
        error_log("CallbackHandlerFactory: handleCallback after encoding fix length: " . strlen($callbackData));
        error_log("CallbackHandlerFactory: handleCallback after encoding fix hex: " . bin2hex($callbackData));
        
        // Fix known Cyrillic pattern corruptions - only once at the beginning
        $callbackData = $this->fixKnownCyrillicPatterns($callbackData);
        
        error_log("CallbackHandlerFactory: handleCallback after Cyrillic fix: '{$callbackData}'");
        error_log("CallbackHandlerFactory: handleCallback after Cyrillic fix length: " . strlen($callbackData));
        error_log("CallbackHandlerFactory: handleCallback after Cyrillic fix hex: " . bin2hex($callbackData));
        
        // Extract category from callback data
        $category = $this->extractCategory($callbackData);
        
        error_log("CallbackHandlerFactory: handling callback data: '{$callbackData}', extracted category: '{$category}'");
        error_log("CallbackHandlerFactory: handling callback data length: " . strlen($callbackData));
        error_log("CallbackHandlerFactory: handling callback data hex: " . bin2hex($callbackData));
        error_log("CallbackHandlerFactory: extracted category length: " . strlen($category));
        error_log("CallbackHandlerFactory: extracted category hex: " . bin2hex($category));
        
        // Special check for electrical equipment
        if ($category === 'Табель_Электрооборудование' || strpos($callbackData, 'Табель_Электрооборудование') !== false) {
            error_log("CallbackHandlerFactory: detected electrical equipment callback");
            error_log("CallbackHandlerFactory: category comparison result: " . ($category === 'Табель_Электрооборудование' ? 'true' : 'false'));
            error_log("CallbackHandlerFactory: callbackData strpos result: " . (strpos($callbackData, 'Табель_Электрооборудование') !== false ? 'true' : 'false'));
        }
        
        // Log all registered handlers and their priorities
        error_log("CallbackHandlerFactory: registered handlers:");
        foreach ($this->handlers as $index => $handler) {
            error_log("  Handler #{$index}: " . get_class($handler) . " with priority: " . $handler->getPriority());
        }

        // Find the appropriate handler
        foreach ($this->handlers as $index => $handler) {
            error_log("CallbackHandlerFactory: checking handler #{$index}: " . get_class($handler) . " with priority: " . $handler->getPriority());
            error_log("CallbackHandlerFactory: calling canHandle on " . get_class($handler) . " with callbackData: '{$callbackData}'");
            // Pass the already processed callback data to the handler
            $canHandle = $handler->canHandle($callbackData);
            error_log("CallbackHandlerFactory: " . get_class($handler) . "->canHandle('{$callbackData}') returned: " . ($canHandle ? 'true' : 'false'));
            if ($canHandle) {
                error_log("CallbackHandlerFactory: found handler: " . get_class($handler));
                // Also pass the already processed category
                error_log("CallbackHandlerFactory: calling handle on " . get_class($handler) . " with category: '{$category}'");
                $result = $handler->handle($callbackQuery, $category, $conversation);
                error_log("CallbackHandlerFactory: handler result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'success' : 'failed') : 'unknown'));
                return $result;
            } else {
                error_log("CallbackHandlerFactory: handler " . get_class($handler) . " cannot handle callback: '{$callbackData}'");
            }
        }

        error_log("CallbackHandlerFactory: no handler found for callback data: '{$callbackData}'");
        return $this->getDefaultResponse($callbackQuery);
    }

    /**
     * Extract category from callback data (remove 'category_' prefix)
     *
     * @param string $callbackData
     * @return string
     */
    private function extractCategory(string $callbackData): string
    {
        error_log("CallbackHandlerFactory: extractCategory called with callbackData: '{$callbackData}'");
        
        // Handle project management callbacks (pm_ prefix) - return full callback data
        if (strpos($callbackData, 'pm_') === 0) {
            $category = $callbackData; // Pass the full callback data to ProjectManagementHandler
            error_log("CallbackHandlerFactory extracted pm category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle access level callbacks (acc_lvl_ prefix) - return full callback data
        if (strpos($callbackData, 'acc_lvl_') === 0) {
            $category = $callbackData; // Pass the full callback data to AccessLevelHandler
            error_log("CallbackHandlerFactory extracted acc_lvl category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle personnel callbacks (personnel_ prefix) - return full callback data
        if (strpos($callbackData, 'personnel_') === 0) {
            $category = $callbackData; // Pass the full callback data to PersonnelHandler
            error_log("CallbackHandlerFactory extracted personnel category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle admin request callbacks (adm_req_ prefix) - return full callback data
        if (strpos($callbackData, 'adm_req_') === 0) {
            $category = $callbackData; // Pass the full callback data to AdminHandler
            error_log("CallbackHandlerFactory extracted adm_req category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle project control callbacks (proj_ctrl_ prefix)
        if (strpos($callbackData, 'proj_ctrl_') === 0) {
            $category = $callbackData; // Pass the full callback data to ProjectHandler
            error_log("CallbackHandlerFactory extracted proj_ctrl category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle other categories
        if (strpos($callbackData, 'category_') === 0) {
            $category = substr($callbackData, strlen('category_'));
            error_log("CallbackHandlerFactory extracted category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle short callback format (cat_ prefix)
        if (strpos($callbackData, 'cat_') === 0) {
            $category = substr($callbackData, strlen('cat_'));
            error_log("CallbackHandlerFactory extracted short category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle date selection callbacks
        if (strpos($callbackData, 'date_') === 0) {
            $category = substr($callbackData, strlen('date_'));
            error_log("CallbackHandlerFactory extracted date category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle FIO selection callbacks
        if (strpos($callbackData, 'fio_') === 0) {
            $category = substr($callbackData, strlen('fio_'));
            error_log("CallbackHandlerFactory extracted FIO category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle project control callbacks (proj_ctrl_ prefix)
        if (strpos($callbackData, 'proj_ctrl_') === 0) {
            $category = $callbackData; // Return the full callback data as category
            error_log("CallbackHandlerFactory extracted project control category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle project list callbacks (proj_list_ prefix)
        if (strpos($callbackData, 'proj_list_') === 0) {
            $category = $callbackData; // Return the full callback data as category
            error_log("CallbackHandlerFactory extracted project list category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle project moderation callbacks (proj_mod_ prefix)
        if (strpos($callbackData, 'proj_mod_') === 0) {
            $category = $callbackData; // Return the full callback data as category
            error_log("CallbackHandlerFactory extracted project moderation category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle project management callbacks (pm_ prefix)
        if (strpos($callbackData, 'pm_') === 0) {
            $category = $callbackData; // Return the full callback data as category
            error_log("CallbackHandlerFactory extracted pm category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        // Handle project card callbacks (proj_card_ prefix)
        if (strpos($callbackData, 'proj_card_') === 0) {
            $category = $callbackData; // Return the full callback data as category
            error_log("CallbackHandlerFactory extracted proj_card category: '{$category}' from callback data: '{$callbackData}'");
            return $category;
        }
        
        error_log("CallbackHandlerFactory: No category found in callback data: '{$callbackData}'");
        return '';
    }

    /**
     * Get default response when no handler is found
     *
     * @param CallbackQuery $callbackQuery
     * @return ServerResponse
     */
    private function getDefaultResponse(CallbackQuery $callbackQuery): ServerResponse
    {
        return Request::sendMessage([
            'chat_id' => $callbackQuery->getMessage()->getChat()->getId(),
            'text' => 'Не выбран пункт меню',
        ]);
    }
    
    /**
     * Fix known Cyrillic pattern corruptions
     */
    private function fixKnownCyrillicPatterns($str)
    {
        // Log the original string
        error_log("CallbackHandlerFactory: fixKnownCyrillicPatterns input: '{$str}'");
        error_log("CallbackHandlerFactory: fixKnownCyrillicPatterns input hex: " . bin2hex($str));
        
        // Special case: if string is already the correct electrical equipment callback, return as is
        if ($str === 'category_Табель_Электрооборудование' || $str === 'Табель_Электрооборудование') {
            error_log("CallbackHandlerFactory: fixKnownCyrillicPatterns returning correct electrical equipment string as is: '{$str}'");
            return $str;
        }
        
        // Handle the specific corrupted electrical equipment callback data
        if ($str === 'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') {
            error_log("CallbackHandlerFactory: fixKnownCyrillicPatterns fixing corrupted electrical equipment callback");
            return 'category_Табель_Электрооборудование';
        }
        
        // Replace the known corrupted patterns
        $replacements = [
            'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
            'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
            'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
            'Р”Р°С‚Р°' => 'Дата',
            'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
            'Р’С‡РµСЂР°' => 'Вчера',
            'РЎРѕСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
            'Р¤РРћ' => 'ФИО',
            'Р¤РЊ' => 'ФН',
            'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
            'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
            'РЎСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
            'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
            'РўР°Р±РµР»СЊ' => 'Табель',
            'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
            'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
            'РџРѕРЅРёР·РёССЊ' => 'Понизить',
            // Add missing patterns for commissioning works
            'РџРќР›' => 'ПНЛ',
            'РЎСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
            'Р Р°Р±Р±РѕС‚С‹' => 'Работы',
            // Add missing patterns for the main buttons (order matters - more specific first)
            'РЁРµС„_РјРѕРЅС‚Р°Р¶' => 'Шеф_монтаж',
            'РўРµРєСѓС‰Р°СЏ' => 'Текущая',
            // Add individual word patterns
            'РјРѕРЅС‚Р°Р¶' => 'монтаж',
            // Add pattern for commissioning works abbreviation
            'РџРќР ' => 'ПНР',
            // Add pattern for preparation
            'Р РѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
            // Add pattern for electrical equipment - this is the key fix
            'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
            // Add pattern for full electrical equipment callback - this is the key fix
            'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'category_Табель_Электрооборудование',
            // Add additional patterns for double-encoded Cyrillic that might be causing issues
            'Табель_Электрооборудование' => 'Табель_Электрооборудование',
            'category_Табель_Электрооборудование' => 'category_Табель_Электрооборудование',
            // Handle mixed encoding cases
            'РўР°Р±РµР»СЊ_Электрооборудование' => 'Табель_Электрооборудование',
            // Handle the specific partially corrupted case
            'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРинРµ' => 'Электрооборудование',
            // Handle the exact double-encoded pattern we're seeing in the test
            'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
            // Add pattern for commissioning works short format
            'cat_ТабПНРаботы' => 'cat_ТабПНРаботы',
            // Add critical patterns for ШМР (Supervision Works) corruption fix
            'РЁРњР ' => 'ШМР',
            'cat_РЁРњР ' => 'cat_ШМР',
            'cat_РЁРњР _' => 'cat_ШМР_',
            // Add patterns for Подготовительные corruption fix
            'РџРѕРґРіР Р°Р±' => 'ПодгРаб',
            'РџРѕРґРіРіР Р°Р±' => 'ПодгРаб',
            'РџРѕРґРїРђРєС‚' => 'ПодгРаб',
            'РџРѕРґРїРђРєСЂ' => 'ПодгРаб',
            // Add full callback corruption pattern for "Подготовительные работы"
            'cat_РЁРњР _РџРѕРґРіР Р°Р±' => 'cat_ШМР_ПодгРаб',
            'cat_РЁРњР _РџРѕРґРіРіР Р°Р±' => 'cat_ШМР_ПодгРаб',
            'cat_РЁРњР _РџРѕРґРїРђРєС‚' => 'cat_ШМР_ПодгРаб',
            'cat_РЁРњР _РџРѕРґРїРђРєСЂ' => 'cat_ШМР_ПодгРаб'
        ];
        
        foreach ($replacements as $corrupted => $fixed) {
            if (strpos($str, $corrupted) !== false) {
                error_log("CallbackHandlerFactory: found corrupted pattern '{$corrupted}' in string, replacing with '{$fixed}'");
                $str = str_replace($corrupted, $fixed, $str);
            }
        }
        
        // Log the result
        error_log("CallbackHandlerFactory: fixKnownCyrillicPatterns output: '{$str}'");
        error_log("CallbackHandlerFactory: fixKnownCyrillicPatterns output hex: " . bin2hex($str));
        
        return $str;
    }
}
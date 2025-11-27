<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for main menu callback queries
 */
class MainMenuHandler implements CallbackHandlerInterface
{
    private MessageService $messageService;
    private AdminService $adminService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        $this->adminService = new AdminService($messageService);
    }

    public function canHandle(string $callbackData): bool
    {
        // Add detailed logging
        error_log("MainMenuHandler: canHandle called with callbackData: '{$callbackData}'");
        
        // Handle main menu callback but exclude access request callback
        // Access requests will now be handled by AdminHandler
        $result = $callbackData === 'category_Главное';
        error_log("MainMenuHandler: canHandle result for '{$callbackData}': " . ($result ? 'true' : 'false'));
        return $result;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("MainMenuHandler: handle called with category: '{$category}' for user: {$userId}");
        
        // Apply comprehensive encoding fixes
        $category = $this->comprehensiveEncodingFix($category);
        error_log("MainMenuHandler: encoded category: '{$category}'");

        // Always show main menu when this callback is triggered
        if ($category === 'Главное') {
            error_log("MainMenuHandler: showing main menu for user: {$userId}");
            return $this->showMainMenu($chatId, $userId);
        }
        
        // Handle access request
        if ($category === 'Админка_ЗапросДоступ') {
            error_log("MainMenuHandler: handling access request for user: {$userId}");
            return $this->handleAccessRequest($callbackQuery);
        }

        $text = 'Неизвестная команда главного меню';
        error_log("MainMenuHandler: unknown command for category: '{$category}' for user: {$userId}");
        $text = $this->comprehensiveEncodingFix($text);
        return $this->messageService->sendMessage($chatId, $text);
    }

    public function getPriority(): int
    {
        return 50; // Same as before, highest priority to handle main menu
    }

    /**
     * Show main menu based on user access level
     */
    private function showMainMenu(string $chatId, int $userId): ServerResponse
    {
        // Get user access level
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        // For guests (level 0), show limited menu
        if ($userAccessLevel == 0) {
            $keyboard = [
                [
                    ['text' => '👤 Профиль', 'callback_data' => 'category_Профиль']
                ],
                [
                    ['text' => '❓ Помощь', 'callback_data' => 'category_Помощь']
                ],
                [
                    ['text' => '🔐 Запросить доступ', 'callback_data' => 'category_Админка_ЗапросДоступ']
                ]
            ];
            
            $text = "<b>🏠 Главное меню</b>\n\n(для вызова этого меню в любой момент отправьте /start)\n\n🔐 Нажмите \"Запросить доступ\" для получения расширенных прав.";
            
            // Also update the reply keyboard (persistent menu) for guests
            $replyKeyboard = $this->messageService->createGuestMainMenuReplyKeyboard();
            $text = $this->comprehensiveEncodingFix($text);
            return $this->messageService->sendMessageWithReplyKeyboard($chatId, $text, $replyKeyboard);
        } else {
            // For other users, show full menu
            $keyboard = [
                [
                    ['text' => '📊 Табель', 'callback_data' => 'category_Табель']
                ],
                [
                    ['text' => '👤 Профиль', 'callback_data' => 'category_Профиль']
                ]
            ];
            
            // Add projects menu for all registered users (level 1+)
            if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_USER) {
                $keyboard[] = [
                    ['text' => '📁 Проекты', 'callback_data' => 'category_Проекты']
                ];
            }
            
            // Add supervisor menu for supervisors and above
            if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                $keyboard[] = [
                    ['text' => '📋 Супервайзер', 'callback_data' => 'category_Супервайзер']
                ];
            }
            
            // Add admin menu for supervisors and above (level 2+)
            if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                $keyboard[] = [
                    ['text' => '⚙️ Админка', 'callback_data' => 'category_Админка']
                ];
            }
            
            // Add project management button for supervisors and above (level 2+)
            if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                $keyboard[] = [
                    ['text' => '🛠️ Управление проектом', 'callback_data' => 'category_УправлениеПроектом']
                ];
            }
            
            $keyboard[] = [
                ['text' => '❓ Помощь', 'callback_data' => 'category_Помощь']
            ];
            
            $text = "<b>🏠 Главное меню</b>\n\n(для вызова этого меню в любой момент отправьте /start)";
            
            // Also update the reply keyboard (persistent menu) for registered users
            $replyKeyboard = $this->messageService->createMainMenuReplyKeyboard($userAccessLevel);
            $text = $this->comprehensiveEncodingFix($text);
            return $this->messageService->sendMessageWithReplyKeyboard($chatId, $text, $replyKeyboard);
        }
    }
    
    /**
     * Handle access request from guest user
     */
    private function handleAccessRequest(CallbackQuery $callbackQuery): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $userName = $callbackQuery->getFrom()->getFirstName() . ' ' . $callbackQuery->getFrom()->getLastName();
        
        error_log("MainMenuHandler: handleAccessRequest called for user: {$userId}");
        
        // Get user access level
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($userAccessLevel);
        
        error_log("MainMenuHandler: user access level: {$userAccessLevel} ({$levelName}) for user: {$userId}");
        
        // Check if user already has a pending access request
        if ($this->adminService->hasPendingAccessRequest($userId)) {
            error_log("MainMenuHandler: user {$userId} already has a pending access request");
            $text = "📝 <b>Запрос доступа</b>\n\n";
            $text .= "У вас уже есть активный запрос на получение доступа.\n";
            $text .= "Пожалуйста, дождитесь рассмотрения вашего запроса администратором.";
            $text = $this->comprehensiveEncodingFix($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // For guests (level 0), check if they have required profile information
        if ($userAccessLevel == 0) {
            error_log("MainMenuHandler: user {$userId} is a guest, checking profile information");
            // Get user profile data
            $userData = $this->adminService->getUserData($userId);
            $userFio = $userData['fio'] ?? '';
            $userFunctionalDirection = $userData['functional_direction'] ?? '';
            
            error_log("MainMenuHandler: user profile data for {$userId} - FIO: '{$userFio}', Functional Direction: '{$userFunctionalDirection}'");
            
            // Check if required profile information is provided
            if (empty($userFio) || $userFio === 'Без ФИО' || empty($userFunctionalDirection) || $userFunctionalDirection === 'Не указано') {
                error_log("MainMenuHandler: user {$userId} missing required profile information");
                $text = "📝 <b>Запрос доступа</b>\n\n";
                $text .= "⚠️ Для подачи заявки на доступ необходимо заполнить профиль:\n";
                
                if (empty($userFio) || $userFio === 'Без ФИО') {
                    $text .= "• Указать ФИО\n";
                }
                
                if (empty($userFunctionalDirection) || $userFunctionalDirection === 'Не указано') {
                    $text .= "• Указать функциональное направление\n";
                }
                
                $text .= "\nПожалуйста, перейдите в профиль и заполните недостающую информацию.";
                $text = $this->comprehensiveEncodingFix($text);
                
                // Add a button to go to the profile
                $keyboard = [
                    [
                        ['text' => '👤 Перейти в профиль', 'callback_data' => 'category_Профиль']
                    ]
                ];
                
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
            
            error_log("MainMenuHandler: user {$userId} has required profile information, sending access request");
            // Send access request
            $success = $this->adminService->sendAccessRequest($userId, $userName, $chatId);
            
            if ($success) {
                error_log("MainMenuHandler: access request sent successfully for user: {$userId}");
                $text = "📝 <b>Запрос доступа отправлен</b>\n\n";
                $text .= "Ваш текущий уровень: {$levelName}\n";
                $text .= "Запрашиваемый уровень: Пользователь\n";
                $text .= "ФИО: {$userFio}\n";
                $text .= "Функциональное направление: {$userFunctionalDirection}\n\n";
                $text .= "Администраторы получили уведомление о вашем запросе.\n";
                $text .= "Ожидайте подтверждения.";
            } else {
                error_log("MainMenuHandler: failed to send access request for user: {$userId}");
                $text = "❌ <b>Ошибка при отправке запроса</b>\n\n";
                $text .= "Не удалось отправить запрос. Проверьте, что профиль заполнен корректно.";
            }
        } else {
            error_log("MainMenuHandler: user {$userId} already has access level: {$userAccessLevel}");
            $text = "🔐 <b>Запрос доступа</b> У вас уже есть уровень доступа выше гостя: {$levelName}";
        }
        
        $text = $this->comprehensiveEncodingFix($text);
        return $this->messageService->sendMessage($chatId, $text);
    }
    
    /**
     * Apply comprehensive encoding fixes
     */
    private function comprehensiveEncodingFix($str)
    {
        // Handle null or empty strings
        if ($str === null || $str === '') {
            return $str;
        }
        
        // First check if the string is already valid UTF-8
        if (mb_check_encoding($str, 'UTF-8')) {
            // If it's already valid UTF-8, check if it contains any corrupted patterns
            // Only apply fixes if we detect corruption
            $hasCorruption = false;
            $corruptedPatterns = [
                'Ð', 'Ð‘', 'Ð’', 'Ð“', 'Ð”', 'Ð•', 'Ð–', 'Ð—',
                'Ð˜', 'Ð™', 'Ðš', 'Ð›', 'Ðœ', 'Ð', 'Ðž', 'ÐŸ',
                'Ð ', 'Ð¡', 'Ð¢', 'Ð£', 'Ð¤', 'Ð¥', 'Ð¦', 'Ð§',
                'Ð¨', 'Ð©', 'Ðª', 'Ð«', 'Ð¬', 'Ð­', 'Ð®', 'Ð¯',
                'Ð°', 'Ð±', 'Ð²', 'Ð³', 'Ð´', 'Ðµ', 'Ñ‘', 'Ð¶',
                'Ð·', 'Ð¸', 'Ð¹', 'Ðº', 'Ð»', 'Ð¼', 'Ð½', 'Ð¾',
                'Ð¿', 'Ñ€', 'Ñ', 'Ñ‚', 'Ñƒ', 'Ñ„', 'Ñ…', 'Ñ†',
                'Ñ‡', 'Ñˆ', 'Ñ‰', 'ÑŠ', 'Ñ‹', 'ÑŒ', 'Ñ', 'ÑŽ', 'Ñ'
            ];
            
            foreach ($corruptedPatterns as $pattern) {
                if (strpos($str, $pattern) !== false) {
                    $hasCorruption = true;
                    break;
                }
            }
            
            // If no corruption detected, return as is
            if (!$hasCorruption) {
                return $str;
            }
        }
        
        // If we get here, we need to fix encoding issues
        // First ensure UTF-8 encoding
        $str = mb_convert_encoding($str, 'UTF-8', 'auto');
        
        // Special handling for the specific corruption pattern we're seeing
        // Replace sequences of question marks followed by corrupted characters
        $str = preg_replace('/\?{2,}[°±µ»_]+/u', '', $str);
        
        // Then apply direct replacement of known corrupted patterns (most reliable method)
        $replacements = [
            // Cyrillic letters that get corrupted
            'Рђ' => 'А', 'Р‘' => 'Б', 'Р’' => 'В', 'Р“' => 'Г', 'Р”' => 'Д', 'Р•' => 'Е', 'Р–' => 'Ж', 'Р—' => 'З',
            'Р˜' => 'И', 'Р™' => 'Й', 'Рљ' => 'К', 'Р›' => 'Л', 'Рњ' => 'М', 'Рќ' => 'Н', 'Рћ' => 'О', 'Рџ' => 'П',
            'Р ' => 'Р', 'РЎ' => 'С', 'Рў' => 'Т', 'РЈ' => 'У', 'Р¤' => 'Ф', 'РҐ' => 'Х', 'Р¦' => 'Ц', 'Р§' => 'Ч',
            'РЁ' => 'Ш', 'Р©' => 'Щ', 'РЄ' => 'Ъ', 'Р«' => 'Ы', 'Р¬' => 'Ь', 'Р­' => 'Э', 'Р®' => 'Ю', 'РЇ' => 'Я',
            'Р°' => 'а', 'Р±' => 'б', 'РІ' => 'в', 'Рі' => 'г', 'Рґ' => 'д', 'Рµ' => 'е', 'С‘' => 'ё', 'Р¶' => 'ж',
            'Р·' => 'з', 'Рё' => 'и', 'Р№' => 'й', 'Рє' => 'к', 'Р»' => 'л', 'Рј' => 'м', 'РЅ' => 'н', 'Рѕ' => 'о',
            'Рї' => 'п', 'СЂ' => 'р', 'СЃ' => 'с', 'С‚' => 'т', 'Сѓ' => 'у', 'С„' => 'ф', 'С…' => 'х', 'С†' => 'ц',
            'С‡' => 'ч', 'С€' => 'ш', 'С‰' => 'щ', 'СЉ' => 'ъ', 'С‹' => 'ы', 'СЊ' => 'ь', 'СЌ' => 'э', 'СЋ' => 'ю', 'СЏ' => 'я',
            
            // Common corrupted words
            'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
            'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
            'РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
            'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
            'РўР°Р±РµР»СЊ' => 'Табель',
            'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
            'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
            'РџРѕРЅРёР·РёС‚СЊ' => 'Понизить',
            'РђРґРјРёРЅРєР°' => 'Админка',
            'Р—Р°СЏРІРєРё' => 'Заявки',
            'РџСЂРѕРµРєС‚С‹' => 'Проекты',
            'РЈРґР°Р»РёС‚СЊ' => 'Удалить',
            'РџРѕРґС‚РІРµСЂРґРёС‚СЊ' => 'Подтвердить',
            'РћС‡РёСЃС‚РёССЊ' => 'Очистить',
            'РЈСЂРѕРІРµРЅСЊ' => 'Уровень',
            'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
            'РЎСѓРїРµРІР°Р№Р·РµСЂ' => 'Супервайзер',
            'РћС‚С‡РµС‚С‹' => 'Отчеты',
            'Р’СЃРµРћС‚С‡РµС‚С‹' => 'ВсеОтчеты',
            'РђРєС‚РёРІРЅРѕСЃС‚СЊ' => 'Активность',
            'РќР°Р·Р°Рґ' => 'Назад',
            'Р“Р»Р°РІРЅРѕРµ' => 'Главное',
            'РџРѕР»СЊР·РѕРІР°С‚РµРё' => 'Пользователи',
            'РџСЂРѕР¤РёР»СЊ' => 'Профиль',
            'РџСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
            'Р¤РРћ' => 'ФИО',
            'Р¤Рќ' => 'ФН',
            'РЎРІР°РЅРѕС‡РЅС‹Рµ' => 'Сварочные',
            'Р”Р°С‚Р°' => 'Дата',
            'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
            'Р’С‡РµСЂР°' => 'Вчера'
        ];
        
        foreach ($replacements as $corrupted => $fixed) {
            $str = str_replace($corrupted, $fixed, $str);
        }
        
        // Additional conversion methods as fallback - only if string still has corruption
        $hasCorruption = false;
        $corruptedPatterns = [
            'Ð', 'Ð‘', 'Ð’', 'Ð“', 'Ð”', 'Ð•', 'Ð–', 'Ð—',
            'Ð˜', 'Ð™', 'Ðš', 'Ð›', 'Ðœ', 'Ð', 'Ðž', 'ÐŸ',
            'Ð ', 'Ð¡', 'Ð¢', 'Ð£', 'Ð¤', 'Ð¥', 'Ð¦', 'Ð§',
            'Ð¨', 'Ð©', 'Ðª', 'Ð«', 'Ð¬', 'Ð­', 'Ð®', 'Ð¯',
            'Ð°', 'Ð±', 'Ð²', 'Ð³', 'Ð´', 'Ðµ', 'Ñ‘', 'Ð¶',
            'Ð·', 'Ð¸', 'Ð¹', 'Ðº', 'Ð»', 'Ð¼', 'Ð½', 'Ð¾',
            'Ð¿', 'Ñ€', 'Ñ', 'Ñ‚', 'Ñƒ', 'Ñ„', 'Ñ…', 'Ñ†',
            'Ñ‡', 'Ñˆ', 'Ñ‰', 'ÑŠ', 'Ñ‹', 'ÑŒ', 'Ñ', 'ÑŽ', 'Ñ'
        ];
        
        foreach ($corruptedPatterns as $pattern) {
            if (strpos($str, $pattern) !== false) {
                $hasCorruption = true;
                break;
            }
        }
        
        if ($hasCorruption) {
            // Method 1: Convert from UTF-8 to Windows-1251 and back to UTF-8
            $fixed1 = @mb_convert_encoding($str, 'Windows-1251', 'UTF-8');
            if ($fixed1 !== false) {
                $fixed1 = @mb_convert_encoding($fixed1, 'UTF-8', 'Windows-1251');
                // Check if this looks like a reasonable fix
                if (strlen($fixed1) < strlen($str)) {
                    $str = $fixed1;
                }
            }
            
            // Method 2: Try to decode as UTF-8 and then re-encode
            $fixed2 = @mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            if ($fixed2 !== false && strlen($fixed2) <= strlen($str)) {
                $str = $fixed2;
            }
            
            // Method 3: Try ISO-8859-1 to UTF-8 conversion
            $fixed3 = @mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
            if ($fixed3 !== false) {
                $str = $fixed3;
            }
        }
        
        // Final cleanup - remove any remaining corrupted sequences
        $str = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $str);
        
        return $str;
    }
}
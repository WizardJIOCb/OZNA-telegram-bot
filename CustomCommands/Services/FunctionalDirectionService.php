<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Service for managing functional directions (справочник функциональных направлений)
 */
class FunctionalDirectionService
{
    /**
     * Available functional directions with their codes and display names
     */
    private const FUNCTIONAL_DIRECTIONS = [
        'mobile_brigade' => [
            'code' => 'mobile_brigade',
            'name' => 'Мобильная бригада',
            'emoji' => '👷‍♂️',
            'description' => 'Строительно-монтажные работы (СМР)'
        ],
        'commissioning' => [
            'code' => 'commissioning',
            'name' => 'Пусконаладка',
            'emoji' => '⚙️',
            'description' => 'Пусконаладочные работы (ПНР)'
        ],
        'supervision' => [
            'code' => 'supervision',
            'name' => 'Шеф-монтаж',
            'emoji' => '🔧',
            'description' => 'Шеф-монтажные работы (ШМР)'
        ],
        'manager' => [
            'code' => 'manager',
            'name' => 'Менеджер',
            'emoji' => '📊',
            'description' => 'Управление проектами'
        ],
        'fired' => [
            'code' => 'fired',
            'name' => 'Уволен',
            'emoji' => '🚫',
            'description' => 'Уволенный сотрудник',
            'admin_only' => true
        ],
        'tester' => [
            'code' => 'tester',
            'name' => 'Тестировщик',
            'emoji' => '🧪',
            'description' => 'Тестирование системы (только для администраторов)',
            'admin_only' => true
        ]
    ];

    /**
     * Mapping from display name to code
     */
    private const NAME_TO_CODE = [
        'Мобильная бригада' => 'mobile_brigade',
        'Пусконаладка' => 'commissioning',
        'Шеф-монтаж' => 'supervision',
        'Менеджер' => 'manager',
        'Уволен' => 'fired',
        'Тестировщик' => 'tester'
    ];

    /**
     * Get all functional directions
     * 
     * @param bool $includeAdminOnly Include directions that require admin access
     * @return array
     */
    public static function getAllDirections(bool $includeAdminOnly = false): array
    {
        $directions = [];
        foreach (self::FUNCTIONAL_DIRECTIONS as $code => $direction) {
            if (!$includeAdminOnly && isset($direction['admin_only']) && $direction['admin_only']) {
                continue;
            }
            $directions[$code] = $direction;
        }
        return $directions;
    }

    /**
     * Get direction by code
     * 
     * @param string $code Direction code
     * @return array|null
     */
    public static function getDirectionByCode(string $code): ?array
    {
        return self::FUNCTIONAL_DIRECTIONS[$code] ?? null;
    }

    /**
     * Get direction by name (display name)
     * 
     * @param string $name Direction display name
     * @return array|null
     */
    public static function getDirectionByName(string $name): ?array
    {
        $code = self::NAME_TO_CODE[$name] ?? null;
        if ($code === null) {
            return null;
        }
        return self::FUNCTIONAL_DIRECTIONS[$code] ?? null;
    }

    /**
     * Get code from display name
     * 
     * @param string $name Direction display name
     * @return string|null
     */
    public static function getCodeFromName(string $name): ?string
    {
        return self::NAME_TO_CODE[$name] ?? null;
    }

    /**
     * Get display name from code
     * 
     * @param string $code Direction code
     * @return string|null
     */
    public static function getNameFromCode(string $code): ?string
    {
        return self::FUNCTIONAL_DIRECTIONS[$code]['name'] ?? null;
    }

    /**
     * Validate if direction name is valid
     * 
     * @param string $name Direction name to validate
     * @param bool $includeAdminOnly Include admin-only directions
     * @return bool
     */
    public static function isValidDirection(string $name, bool $includeAdminOnly = false): bool
    {
        if (!isset(self::NAME_TO_CODE[$name])) {
            return false;
        }
        
        $code = self::NAME_TO_CODE[$name];
        $direction = self::FUNCTIONAL_DIRECTIONS[$code];
        
        if (!$includeAdminOnly && isset($direction['admin_only']) && $direction['admin_only']) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if direction requires admin access
     * 
     * @param string $name Direction name
     * @return bool
     */
    public static function isAdminOnlyDirection(string $name): bool
    {
        $code = self::NAME_TO_CODE[$name] ?? null;
        if ($code === null) {
            return false;
        }
        return isset(self::FUNCTIONAL_DIRECTIONS[$code]['admin_only']) && 
               self::FUNCTIONAL_DIRECTIONS[$code]['admin_only'];
    }

    /**
     * Get list of direction names for display
     * 
     * @param bool $includeAdminOnly Include admin-only directions
     * @return array List of direction names
     */
    public static function getDirectionNames(bool $includeAdminOnly = false): array
    {
        $names = [];
        foreach (self::FUNCTIONAL_DIRECTIONS as $direction) {
            if (!$includeAdminOnly && isset($direction['admin_only']) && $direction['admin_only']) {
                continue;
            }
            $names[] = $direction['name'];
        }
        return $names;
    }

    /**
     * Get formatted list of directions with emojis
     * 
     * @param bool $includeAdminOnly Include admin-only directions
     * @return string Formatted list
     */
    public static function getFormattedList(bool $includeAdminOnly = false): string
    {
        $lines = [];
        foreach (self::FUNCTIONAL_DIRECTIONS as $direction) {
            if (!$includeAdminOnly && isset($direction['admin_only']) && $direction['admin_only']) {
                continue;
            }
            $lines[] = "{$direction['emoji']} {$direction['name']}";
        }
        return implode("\n", $lines);
    }

    /**
     * Create inline keyboard buttons for functional direction selection
     * 
     * @param string $callbackPrefix Prefix for callback data (e.g., 'acc_lvl_set_fn_')
     * @param int $targetUserId Target user ID to include in callback
     * @param bool $includeAdminOnly Include admin-only directions
     * @return array Keyboard rows
     */
    public static function createSelectionKeyboard(string $callbackPrefix, int $targetUserId, bool $includeAdminOnly = false): array
    {
        $keyboard = [];
        
        foreach (self::FUNCTIONAL_DIRECTIONS as $code => $direction) {
            if (!$includeAdminOnly && isset($direction['admin_only']) && $direction['admin_only']) {
                continue;
            }
            
            $buttonText = "{$direction['emoji']} {$direction['name']}";
            // Use short callback format to avoid Telegram's 64-byte limit
            // Format: acc_lvl_set_fn_{userId}_{shortCode}
            $shortCode = self::getShortCode($code);
            $callbackData = "{$callbackPrefix}{$targetUserId}_{$shortCode}";
            
            $keyboard[] = [
                ['text' => $buttonText, 'callback_data' => $callbackData]
            ];
        }
        
        return $keyboard;
    }

    /**
     * Get short code for callback data (to fit within Telegram's 64-byte limit)
     * 
     * @param string $code Full code
     * @return string Short code
     */
    public static function getShortCode(string $code): string
    {
        $shortCodes = [
            'mobile_brigade' => 'mb',
            'commissioning' => 'pnr',
            'supervision' => 'shm',
            'manager' => 'mgr',
            'fired' => 'frd',
            'tester' => 'tst'
        ];
        return $shortCodes[$code] ?? substr($code, 0, 3);
    }

    /**
     * Get full code from short code
     * 
     * @param string $shortCode Short code
     * @return string|null Full code
     */
    public static function getFullCodeFromShort(string $shortCode): ?string
    {
        $shortToFull = [
            'mb' => 'mobile_brigade',
            'pnr' => 'commissioning',
            'shm' => 'supervision',
            'mgr' => 'manager',
            'frd' => 'fired',
            'tst' => 'tester'
        ];
        return $shortToFull[$shortCode] ?? null;
    }

    /**
     * Get display name from short code
     * 
     * @param string $shortCode Short code
     * @return string|null Display name
     */
    public static function getNameFromShortCode(string $shortCode): ?string
    {
        $fullCode = self::getFullCodeFromShort($shortCode);
        if ($fullCode === null) {
            return null;
        }
        return self::getNameFromCode($fullCode);
    }
}

# Guest Access Restrictions Implementation

## Overview
This document describes the implementation of access restrictions for guest users (access level 0) in the Telegram bot.

## Problem
Previously, guest users with access level 0 were able to see all main menu buttons and navigate to restricted functionality, even though the actual operations were blocked with access restriction messages. This behavior was not compliant with the specification which requires guests to only see Profile and Help functionality.

Additionally, users could request access without completing their profile (FIO and functional direction), which is not ideal for administrative purposes.

## Solution
The implementation now properly restricts guest access at multiple levels:

### 1. Main Menu Restrictions (StartCommand)
- Guests only see Profile and Help buttons in the main menu
- Tabel and Admin buttons are hidden from guests
- Implementation in `StartCommand.php`

### 2. Text Menu Restrictions (TextmenuCommand)
- Guests cannot access Tabel or Admin functionality through text menu commands
- Attempting to access restricted functionality shows an access restriction message
- Implementation in `TextmenuCommand.php`

### 3. Profile Access Request (ProfileHandler)
- Guests see a "Запросить доступ" button in their profile
- This allows guests to request access upgrade
- Implementation in `ProfileHandler.php`

### 4. Access Request Validation (AdminHandler)
- Users must complete their profile (FIO and functional direction) before requesting access
- Clear error messages guide users to complete their profile
- Implementation in `AdminHandler.php`

### 5. Inline Menu Restrictions (Callback Handlers)
- All callback handlers already implement proper access level validation
- Guests receive access restriction messages when trying to access restricted functionality
- Implementation in various handler files

## Implementation Details

### StartCommand Changes
Modified to check user access level and create appropriate menu:
```php
// Check user access level
$userAccessLevel = $adminService->getUserAccessLevel($user_id);

// Create main menu reply keyboard based on user access level
if ($userAccessLevel == 0) { // Guest level
    // Limited menu for guests
    $keyboard = [
        [
            ['text' => '👤 Профиль'], 
            ['text' => '❓ Помощь']
        ]
    ];
} else {
    // Full menu for other users
    $keyboard = $messageService->createMainMenuReplyKeyboard();
}
```

### TextmenuCommand Changes
Modified to check user access level and restrict menu access:
```php
// Получаем уровень доступа пользователя
$userAccessLevel = $this->adminService->getUserAccessLevel($userId);

// Для гостей (уровень 0) ограничиваем доступ к меню
if ($userAccessLevel == 0) { // Guest level
    switch ($text) {
        case '👤 Профиль':
        case 'Профиль':
            return $this->handleProfileMenu($chatId);
            
        case '❓ Помощь':
        case 'Помощь':
            return $this->handleHelpMenu($chatId);
            
        case '📊 Табель':
        case 'Табель':
        case '⚙️ Админка':
        case 'Админка':
            // Ограничиваем доступ для гостей
            return $this->messageService->sendMessage(
                $chatId,
                "⚠️ <b>Доступ ограничен</b>\n\nУ вас уровень доступа \"Гость\". Обратитесь к администратору для получения расширенного доступа.",
                null
            );
            
        default:
            // Если текст не соответствует меню - игнорируем
            return Request::emptyResponse();
    }
}
```

### ProfileHandler Changes
Modified to show access request button for guests:
```php
// Для гостевых пользователей добавляем кнопку "Заявка на доступ"
if ($accessLevel == 0) { // Guest level
    $text = '<b>👤 Ваш профиль</b>' . PHP_EOL . PHP_EOL .
        '<b>ФИО:</b> ' . $currentFio . PHP_EOL .
        '<b>Функциональное направление:</b> ' . $currentFn . PHP_EOL .
        '<b>Уровень доступа:</b> ' . $levelName . PHP_EOL . PHP_EOL .
        '⚠️ Уровень доступа "Гость" - Обратитесь к администратору для получения расширенного доступа.';
        
    $keyboard = [
        [
            ['text' => '📝 Запросить доступ', 'callback_data' => 'category_Админка_ЗапросДоступ']
        ],
        [
            ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
        ]
    ];
    
    $text = mb_convert_encoding($text, 'UTF-8', 'auto');
    
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
} else {
    // Для пользователей с уровнем доступа выше гостя показываем полную информацию
    $text = '<b>👤 Ваш профиль</b>' . PHP_EOL . PHP_EOL .
        '<b>ФИО:</b> ' . $currentFio . PHP_EOL .
        '<b>Функциональное направление:</b> ' . $currentFn . PHP_EOL .
        '<b>Уровень доступа:</b> ' . $levelName . PHP_EOL .
        '<b>ID уровня:</b> ' . $accessLevel;
        
    $text = mb_convert_encoding($text, 'UTF-8', 'auto');
    
    return $this->messageService->sendMessage($chatId, $text);
}
```

### AdminHandler Changes
Modified to validate profile completion before access request:
```php
// Проверяем, заполнил ли пользователь ФИО и функциональное направление
$userData = \pDB::getUserData($userId);
$userFio = $userData['fio'] ?? '';
$userFunctionalDirection = $userData['functional_direction'] ?? '';

// Проверяем, заполнены ли обязательные поля
if (empty($userFio) || $userFio === 'Без ФИО') {
    $text = "⚠️ <b>Заполните профиль</b>\n\nДля подачи заявки на доступ к админке необходимо заполнить ФИО.\n\nВведите команду /fio Фамилия Имя Отчество";
    $text = $this->ensureUtf8Encoding($text);
    return $this->messageService->sendMessage($chatId, $text);
}

if (empty($userFunctionalDirection) || $userFunctionalDirection === 'Не указано') {
    $text = "⚠️ <b>Заполните профиль</b>\n\nДля подачи заявки на доступ к админке необходимо выбрать функциональное направление.\n\nВведите команду /profile и выберите функциональное направление";
    $text = $this->ensureUtf8Encoding($text);
    return $this->messageService->sendMessage($chatId, $text);
}
```

## Testing
The implementation has been verified with the following test scenarios:

1. Guest users see only Profile and Help in main menu
2. Guest users cannot access Tabel functionality
3. Guest users cannot access Admin functionality
4. Guest users can access Profile functionality
5. Guest users can access Help functionality
6. Guest users see "Запросить доступ" button in their profile
7. Users without completed profile cannot request access
8. Users with completed profile can request access

## Files Modified
- `CustomCommands/StartCommand.php` - Main menu restriction
- `CustomCommands/TextmenuCommand.php` - Text menu access restriction
- `CustomCommands/Handlers/ProfileHandler.php` - Profile access request button
- `CustomCommands/Handlers/AdminHandler.php` - Access request validation

## Compliance
This implementation now complies with the specification that requires guest users (access level 0) to only have access to profile and help functionality, with the addition of being able to request access upgrade only after completing their profile.
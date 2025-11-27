# Guest Menu Fix Summary

## Problem
When guests pressed the "Вернуться в главное меню" (Return to main menu) button, they were seeing all main menu buttons including "Табель" and "Админка", even though they should only see limited options based on their access level.

## Root Cause
The issue was in the MainMenuHandler.php file. While the inline keyboard (shown in messages) was correctly limited for guests, the reply keyboard (persistent menu at the bottom of the chat) was not being updated based on the user's access level.

## Solution
Modified the MainMenuHandler.php to:
1. Check the user's access level when showing the main menu
2. Update the reply keyboard (persistent menu) based on the user's access level
3. Use the existing `createGuestMainMenuReplyKeyboard()` method for guests
4. Use the existing `createMainMenuReplyKeyboard()` method for registered users
5. Send messages with the appropriate reply keyboard using `sendMessageWithReplyKeyboard()`

## Changes Made

### File: CustomCommands/Handlers/MainMenuHandler.php

Updated the `showMainMenu()` method to:

1. For guests (access level 0):
   - Show limited inline keyboard with only "Профиль" and "Помощь"
   - Update reply keyboard to show only "Профиль" and "Помощь" buttons
   - Include warning message about guest access level

2. For registered users (access level 1+):
   - Show full inline keyboard with all menu options
   - Update reply keyboard to show all menu buttons
   - Include standard main menu message

## Verification
- All required methods exist in MessageService.php
- MainMenuHandler correctly implements access level checking
- Both guest and registered user logic paths are implemented
- No syntax errors in modified files

## Result
Now when guests press "Вернуться в главное меню", they will see and have access only to the appropriate menu options:
- 👤 Профиль
- ❓ Помощь

Registered users will continue to see the full menu:
- 📊 Табель
- 👤 Профиль
- ⚙️ Админка
- ❓ Помощь
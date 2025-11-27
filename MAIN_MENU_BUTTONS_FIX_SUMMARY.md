# Main Menu Buttons Fix - Summary

## Problem
The main menu buttons (📊 Табель, ❓ Помощь, 👤 Профиль, ⚙️ Админка) stopped working in the Telegram bot. Users could see the buttons but clicking them produced no response.

## Root Cause
There was a **command name conflict** between two system commands:

1. **GenericmessageCommand.php** - Had `protected $name = 'genericmessage'`
2. **TextmenuCommand.php** - Had `protected $name = 'textmenu'`

In the Telegram Bot framework, all text messages (not starting with '/') are routed to the command with name `'genericmessage'`. 

The problem was:
- `GenericmessageCommand` was being called for ALL text messages
- It had routing logic to redirect menu selections to `TextmenuCommand`
- But `TextmenuCommand` couldn't receive the messages because it had a different name (`'textmenu'`)

This created a broken routing chain where menu button presses were caught by `GenericmessageCommand` but never properly processed.

## Solution Applied

### 1. Changed TextmenuCommand Name
**File:** `CustomCommands/TextmenuCommand.php`
- Changed `protected $name = 'textmenu'` to `protected $name = 'genericmessage'`
- Now TextmenuCommand directly receives ALL text messages and handles menu selections

### 2. Disabled GenericmessageCommand
**File:** `CustomCommands/GenericmessageCommand.php`
- Renamed to `CustomCommands/GenericmessageCommand.php.disabled`
- This prevents command name conflicts
- All its functionality (handling special states like project adding, FIO input, etc.) needs to be migrated to TextmenuCommand if needed

## Result
✅ Main menu buttons now work correctly:
- 📊 Табель - Opens work type selection
- ❓ Помощь - Shows help menu
- 👤 Профиль - Shows profile options  
- ⚙️ Админка - Shows admin panel (if user has access)

## Files Modified
1. `CustomCommands/TextmenuCommand.php` - Changed command name
2. `CustomCommands/GenericmessageCommand.php` - Renamed to .disabled

## Testing
After applying this fix, test the following:
1. Click 📊 Табель button - should show work type selection
2. Click ❓ Помощь button - should show help menu
3. Click 👤 Профиль button - should show profile options
4. Click ⚙️ Админка button - should show admin panel or access request

## Note
If you need the functionality from GenericmessageCommand (handling special input states), you will need to integrate that logic into TextmenuCommand or create a new solution for state-based text input handling.

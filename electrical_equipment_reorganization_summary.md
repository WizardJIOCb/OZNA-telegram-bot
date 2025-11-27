# Electrical Equipment Buttons Reorganization

## Task
Move the buttons "изменение схем подключения", "монтаж кабельных линий" and "расключение кабельных линий" from the "электрооборудование" activity to the "дополнительные работы" button.

## Changes Made

### 1. Modified `createElectricalEquipmentKeyboard()` method
Removed the three buttons from the main electrical equipment keyboard:
- 🔀 Изменение схем подключения
- 🔌 Монтаж кабельных линий
- 📊 Расключение кабельных линий

The main electrical equipment keyboard now contains:
1. 📅 Работы по графику
2. 📄 Формирование ИД
3. 🛠️ Дополнительные работы
4. ✅ Подтвердить достижение вехи
5. 📝 Прочее
6. ⬅️ Назад

### 2. Kept buttons in `createAdditionalWorksElectricalKeyboard()` method
The three buttons remain in the additional works electrical keyboard:
1. 🔀 Изменение схем подключения
2. 🔌 Монтаж кабельных линий
3. 📊 Расключение кабельных линий
4. 📝 Прочее
5. ⬅️ Назад

## Verification
Created and ran tests to confirm:
- ✅ The three buttons are no longer in the main electrical equipment menu
- ✅ The three buttons are still present in the additional works menu
- ✅ All other buttons remain in their correct positions
- ✅ Navigation between menus works correctly

## Files Modified
- `c:\ozna_bot\CustomCommands\Services\MessageService.php` - Modified the `createElectricalEquipmentKeyboard()` method

## Result
The buttons have been successfully moved as requested. Users will now need to click the "Дополнительные работы" button to access "Изменение схем подключения", "Монтаж кабельных линий", and "Расключение кабельных линий" options.
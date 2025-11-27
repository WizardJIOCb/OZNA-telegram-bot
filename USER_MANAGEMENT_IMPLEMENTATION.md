# User Management Implementation for Access Levels

## Features Implemented

### 1. User Management Menu
- Added "Управление пользователями" button in the access level management menu
- When clicked, displays a list of all users with access level above "Гость"

### 2. User List Display
- Shows all users with access level > 0 (above Guest)
- Displays each user as a button with their FIO and current access level
- Buttons are arranged 2 per row for better UI
- Includes "Вернуться в меню" button

### 3. User Details View
- When a user button is clicked, shows detailed profile information:
  - FIO
  - Functional direction
  - Current access level with name and ID
  - User ID
- Implements access restrictions:
  - Administrators cannot modify other administrators or super administrators
  - Appropriate error message is shown when this restriction is encountered

### 4. Access Level Selection
- Shows buttons for all available access levels:
  - For Administrators: Levels 0-2 (Guest, User, Supervisor)
  - For Super Administrators: Levels 0-3 (Guest, User, Supervisor, Administrator)
- Each button sets the user's access level to the corresponding value

### 5. Access Level Update
- Updates user's access level in the database
- Sends notification to the user about the change
- Shows updated user details after successful update
- Implements proper access restrictions:
  - Only Super Administrators can set Administrator level (3)
  - Administrators cannot modify other administrators

## Technical Implementation

### New Methods Added

1. **AdminService::getAllUsersWithAccess()**
   - Retrieves all users with access level > 0
   - Used to populate the user management list

2. **AccessLevelHandler::showUsersWithAccess()**
   - Displays list of users with access as buttons
   - Handles empty user list case

3. **AccessLevelHandler::showUserDetails()**
   - Shows detailed user profile information
   - Implements access restrictions for administrators
   - Creates access level selection buttons based on current user's level

4. **AccessLevelHandler::setUserAccessLevel()**
   - Updates user's access level in database
   - Implements access restrictions
   - Sends notification to user
   - Shows updated user details

### Updated Methods

1. **AccessLevelHandler::showAccessLevelManagementMenu()**
   - Changed callback data for "Управление пользователями" button from 'acc_lvl_users' to 'acc_lvl_manage_users'

### New Callback Handlers

1. **acc_lvl_manage_users** - Shows list of users with access
2. **acc_lvl_user_details_{userId}** - Shows details for specific user
3. **acc_lvl_set_level_{userId}_{level}** - Sets user's access level

## Access Restrictions

1. **Administrator vs Administrator**
   - Administrators cannot modify other administrators or super administrators
   - Error message is shown when attempting to do so

2. **Administrator Level Assignment**
   - Only Super Administrators can assign Administrator level (3)
   - Error message is shown when regular administrators attempt to do so

## User Experience

1. **Navigation**
   - Clear navigation path: Main Menu → Access Level Management → User Management → User List → User Details
   - Back buttons at each level for easy navigation

2. **Information Display**
   - Clear, formatted display of user information
   - Visual indication of current access level
   - Appropriate error messages for restricted actions

3. **Feedback**
   - Users receive notifications when their access level is changed
   - Immediate visual feedback through UI updates

## Testing

The implementation has been designed to:
- Handle edge cases (empty user lists, invalid user IDs)
- Maintain proper access control
- Provide clear user feedback
- Follow existing code patterns and conventions
# CallbackqueryCommand Refactoring Documentation

## Overview

The original `CallbackqueryCommand.php` was a monolithic class with over 1600 lines of code containing a massive switch statement. This created several maintainability issues:

- **Spaghetti code**: All logic was intertwined in one massive switch statement
- **Code duplication**: Similar patterns repeated throughout the file  
- **Poor separation of concerns**: UI, business logic, and data access mixed together
- **Hard to extend**: Adding new features required modifying the main class
- **Database connections scattered**: Direct database calls throughout the code
- **Hard-coded values**: Database credentials and project codes embedded in logic

## Refactored Architecture

The new architecture follows SOLID principles and clean architecture patterns:

### 📁 Directory Structure

```
CustomCommands/
├── CallbackqueryCommand.php           # Main command (100 lines vs 1612)
├── CallbackqueryCommand_Original_Backup.php  # Original backup
├── Services/                          # Core services
│   ├── CallbackHandlerInterface.php   # Handler contract
│   ├── CallbackHandlerFactory.php     # Handler factory/router
│   ├── DatabaseService.php           # Centralized database operations
│   ├── MessageService.php            # Message sending & keyboard creation
│   ├── ProjectSelector.php           # Project code management
│   └── TabelEntry.php               # Timesheet entry logic
└── Handlers/                         # Callback handlers
    ├── ProfileHandler.php           # Profile-related callbacks
    ├── TabelHandler.php            # Timesheet-related callbacks
    ├── HelpHandler.php             # Help-related callbacks
    ├── AdminHandler.php            # Admin-related callbacks
    └── AccessLevelHandler.php      # Access level management callbacks
```

### 🏗️ Architecture Components

#### 1. **Services Layer**
- **DatabaseService**: Centralizes all database operations with prepared statements
- **MessageService**: Handles message sending and keyboard creation 
- **ProjectSelector**: Manages project code extraction and validation
- **TabelEntry**: Encapsulates timesheet entry business logic

#### 2. **Handlers Layer** 
- **CallbackHandlerInterface**: Contract for all callback handlers
- **ProfileHandler**: Handles profile-related operations
- **TabelHandler**: Manages complex timesheet workflows
- **HelpHandler**: Simple help functionality
- **AdminHandler**: Handles admin panel operations
- **AccessLevelHandler**: Manages user access levels

#### 3. **Factory Pattern**
- **CallbackHandlerFactory**: Routes callbacks to appropriate handlers based on priority

### 🔄 Request Flow

```
1. CallbackqueryCommand.execute()
2. → CallbackHandlerFactory.handleCallback()
3. → Find appropriate handler (ProfileHandler, TabelHandler, etc.)
4. → Handler uses services (DatabaseService, MessageService, etc.)
5. → Return ServerResponse
```

## Key Improvements

### ✅ **Code Quality**
- **93% line reduction**: 1612 lines → ~100 lines in main class
- **Eliminated spaghetti code**: Clean handler pattern
- **Single Responsibility**: Each class has one clear purpose
- **Open/Closed Principle**: Easy to add new handlers without modifying existing code

### 🔒 **Security & Reliability**  
- **Prepared statements**: Prevents SQL injection
- **Error handling**: Proper exception handling
- **Connection management**: Centralized database connections

### 🚀 **Maintainability**
- **Easy to extend**: Add new handlers for new callback types
- **Testable**: Each component can be unit tested independently
- **Clear separation**: UI, business logic, and data access separated
- **Configuration**: Hard-coded values moved to service constructors

### 📊 **Performance**
- **Connection reuse**: Database connections managed efficiently
- **Lazy loading**: Services initialized only when needed
- **Priority-based routing**: Handlers processed by priority

## Usage Examples

### Adding a New Handler

```php
class NewFeatureHandler implements CallbackHandlerInterface 
{
    public function canHandle(string $callbackData): bool 
    {
        return str_starts_with($callbackData, 'category_NewFeature');
    }
    
    public function handle(CallbackQuery $query, string $category, Conversation $conv): ServerResponse 
    {
        // Handle the callback
    }
    
    public function getPriority(): int 
    {
        return 5;
    }
}

// Register in CallbackqueryCommand::initializeServices()
$this->handlerFactory->registerHandler(new NewFeatureHandler());
```

### Using Services

```php
// Database operations
$dbService = new DatabaseService();
$entries = $dbService->getUserLastTabelEntries($userId, 10);

// Message sending
$msgService = new MessageService();
$keyboard = $msgService->createProjectSelectionKeyboard();
$msgService->sendMessage($chatId, "Hello", $keyboard);

// Project management  
$projSelector = new ProjectSelector();
$projectCode = $projSelector->extractProjectCode($category);
```

## Migration Notes

### Backward Compatibility
The refactored class maintains backward compatibility through legacy methods:
- `sendToHistoryChat()` - delegates to MessageService
- `addUserTabel()` - delegates to DatabaseService

### Database Integration
The refactored code works with the existing `pDB` class and database schema without changes.

### Configuration
Database credentials can be easily modified in the `DatabaseService` constructor or moved to a configuration file.

## Testing

Run the architecture test:
```bash
php test_refactored_architecture.php
```

This verifies all classes can be instantiated and work together correctly.

## Benefits Achieved

1. **📉 Code Complexity**: Massive reduction in cyclomatic complexity
2. **🔧 Maintainability**: Much easier to modify and extend
3. **🧪 Testability**: Each component can be unit tested  
4. **🔒 Security**: SQL injection protection with prepared statements
5. **⚡ Performance**: Better resource management
6. **📚 Readability**: Clean, self-documenting code
7. **🎯 Extensibility**: Easy to add new callback types

## Future Enhancements

1. **Configuration Management**: Move database credentials to config files
2. **Dependency Injection**: Implement a proper DI container
3. **Unit Tests**: Add comprehensive test coverage
4. **Caching**: Add caching layer for frequently accessed data
5. **Logging**: Implement structured logging
6. **Validation**: Add input validation decorators

## Access Level Management System

### Overview
A new 4-level access control system has been implemented:

1. **Guest (Level 0)** - Basic profile access
2. **User (Level 1)** - Full bot functionality
3. **Admin (Level 2)** - Administrative functions
4. **Super Admin (Level 3)** - Full system control

### Key Features
- **Role-based access control**: Users can only access features appropriate to their level
- **Request system**: Guests can request access upgrades
- **Admin management**: Admins can promote/demote users
- **Super admin control**: Only super admins can create new admins

### Implementation Details
- **DatabaseService**: Extended with access level management methods
- **AdminService**: Enhanced with access level checking and management
- **AccessLevelHandler**: New handler for access level operations
- **RequestAccessCommand**: Command for users to request access upgrades
- **PromoteCommand/DemoteCommand**: Admin commands for managing user levels

### Security Features
- Users cannot promote others to equal or higher levels
- Only super admins can create new admins
- All access level changes are logged and notified

### Testing the Access Level System
Several test scripts have been created to verify the functionality:

1. `check_user_access_levels.php` - Check current access levels
2. `test_approve_access_request.php` - Test access request approval
3. `add_first_admins.php` - Add initial administrators
4. `reset_users_to_guest_level.php` - Reset all users to guest level
5. `verify_access_levels_system.php` - Verify system functionality

### Usage Instructions
Detailed usage instructions are provided in `USAGE_ACCESS_LEVELS.md`:

1. **Getting First Admins**: Approve access requests or manually add admins
2. **Resetting Users**: Reset all users to guest level for controlled access
3. **User Access Requests**: Users can request access through the profile menu
4. **Admin Management**: Admins can promote/demote users through commands

## Conclusion

The refactored architecture transforms a monolithic, unmaintainable class into a clean, extensible system following modern PHP practices. The code is now much easier to understand, modify, and extend while maintaining full backward compatibility. The addition of the access level management system provides fine-grained control over user permissions while maintaining security and ease of use.
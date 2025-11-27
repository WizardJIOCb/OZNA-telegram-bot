<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Thread-Safe Database Service with concurrent access protection
 * Исправленная версия с защитой от одновременного доступа
 */
class ThreadSafeDatabaseService extends DatabaseService
{
    private bool $debugMode = true;

    /**
     * Thread-safe method to update user data with proper locking
     * Безопасный метод обновления данных пользователя с блокировками
     */
    public function updateUserDataSafe(int $userId, array $userData): bool
    {
        $connection = $this->getConnection();
        
        try {
            // Начинаем транзакцию
            $connection->autocommit(false);
            
            if ($this->debugMode) {
                error_log("ThreadSafeDatabaseService: Starting transaction for user {$userId}");
            }
            
            // Блокируем запись для обновления (SELECT ... FOR UPDATE)
            $lockQuery = "SELECT data FROM ozna_data WHERE user_id = ? FOR UPDATE";
            $stmt = $connection->prepare($lockQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentData = $result->fetch_assoc();
            
            if ($this->debugMode) {
                error_log("ThreadSafeDatabaseService: Locked record for user {$userId}");
            }
            
            // Если записи нет - создаём её
            if (!$currentData) {
                $insertQuery = "INSERT INTO ozna_data (user_id, data) VALUES (?, ?)";
                $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
                $insertStmt = $connection->prepare($insertQuery);
                $insertStmt->bind_param("is", $userId, $jsonData);
                $result = $insertStmt->execute();
            } else {
                // Обновляем существующую запись
                $updateQuery = "UPDATE ozna_data SET data = ? WHERE user_id = ?";
                $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
                $updateStmt = $connection->prepare($updateQuery);
                $updateStmt->bind_param("si", $jsonData, $userId);
                $result = $updateStmt->execute();
            }
            
            if ($result) {
                // Фиксируем транзакцию
                $connection->commit();
                if ($this->debugMode) {
                    error_log("ThreadSafeDatabaseService: Transaction committed for user {$userId}");
                }
                return true;
            } else {
                // Откатываем транзакцию при ошибке
                $connection->rollback();
                if ($this->debugMode) {
                    error_log("ThreadSafeDatabaseService: Transaction rolled back for user {$userId}");
                }
                return false;
            }
            
        } catch (\Exception $e) {
            // Откатываем транзакцию при исключении
            $connection->rollback();
            error_log("ThreadSafeDatabaseService: Exception in updateUserDataSafe: " . $e->getMessage());
            return false;
        } finally {
            // Возвращаем автокоммит
            $connection->autocommit(true);
        }
    }

    /**
     * Safe method to add project to control list with conflict prevention
     * Безопасный метод добавления проекта на контроль с предотвращением конфликтов
     */
    public function addProjectToControlListSafe(int $userId, string $projectCode, array $projectSettings): bool
    {
        $connection = $this->getConnection();
        
        try {
            $connection->autocommit(false);
            
            if ($this->debugMode) {
                error_log("ThreadSafeDatabaseService: Adding project {$projectCode} for user {$userId}");
            }
            
            // Блокируем запись
            $lockQuery = "SELECT data FROM ozna_data WHERE user_id = ? FOR UPDATE";
            $stmt = $connection->prepare($lockQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentRow = $result->fetch_assoc();
            
            // Получаем текущие данные
            $userData = $currentRow ? json_decode($currentRow['data'], true) : [];
            if (!isset($userData['control_projects'])) {
                $userData['control_projects'] = [];
            }
            
            // Проверяем, что проект ещё не добавлен
            if (isset($userData['control_projects'][$projectCode])) {
                $connection->rollback();
                if ($this->debugMode) {
                    error_log("ThreadSafeDatabaseService: Project {$projectCode} already exists for user {$userId}");
                }
                return false; // Проект уже существует
            }
            
            // Добавляем новый проект
            $userData['control_projects'][$projectCode] = $projectSettings;
            
            // Обновляем запись
            if (!$currentRow) {
                $insertQuery = "INSERT INTO ozna_data (user_id, data) VALUES (?, ?)";
                $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
                $insertStmt = $connection->prepare($insertQuery);
                $insertStmt->bind_param("is", $userId, $jsonData);
                $result = $insertStmt->execute();
            } else {
                $updateQuery = "UPDATE ozna_data SET data = ? WHERE user_id = ?";
                $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
                $updateStmt = $connection->prepare($updateQuery);
                $updateStmt->bind_param("si", $jsonData, $userId);
                $result = $updateStmt->execute();
            }
            
            $connection->commit();
            if ($this->debugMode) {
                error_log("ThreadSafeDatabaseService: Successfully added project {$projectCode} for user {$userId}");
            }
            return true;
            
        } catch (\Exception $e) {
            $connection->rollback();
            error_log("ThreadSafeDatabaseService: Exception in addProjectToControlListSafe: " . $e->getMessage());
            return false;
        } finally {
            $connection->autocommit(true);
        }
    }

    /**
     * Safe method to update project settings with conflict prevention
     * Безопасный метод обновления настроек проекта с предотвращением конфликтов
     */
    public function updateProjectSettingsSafe(int $userId, string $projectCode, array $newSettings): bool
    {
        $connection = $this->getConnection();
        
        try {
            $connection->autocommit(false);
            
            if ($this->debugMode) {
                error_log("ThreadSafeDatabaseService: Updating settings for project {$projectCode}, user {$userId}");
            }
            
            // Блокируем запись
            $lockQuery = "SELECT data FROM ozna_data WHERE user_id = ? FOR UPDATE";
            $stmt = $connection->prepare($lockQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentRow = $result->fetch_assoc();
            
            if (!$currentRow) {
                $connection->rollback();
                if ($this->debugMode) {
                    error_log("ThreadSafeDatabaseService: User {$userId} not found");
                }
                return false;
            }
            
            // Получаем текущие данные
            $userData = json_decode($currentRow['data'], true);
            if (!isset($userData['control_projects'][$projectCode])) {
                $connection->rollback();
                if ($this->debugMode) {
                    error_log("ThreadSafeDatabaseService: Project {$projectCode} not found for user {$userId}");
                }
                return false;
            }
            
            // Обновляем настройки проекта
            $userData['control_projects'][$projectCode] = array_merge(
                $userData['control_projects'][$projectCode],
                $newSettings
            );
            $userData['control_projects'][$projectCode]['updated_at'] = date('Y-m-d H:i:s');
            
            // Сохраняем обновлённые данные
            $updateQuery = "UPDATE ozna_data SET data = ? WHERE user_id = ?";
            $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
            $updateStmt = $connection->prepare($updateQuery);
            $updateStmt->bind_param("si", $jsonData, $userId);
            $result = $updateStmt->execute();
            
            $connection->commit();
            if ($this->debugMode) {
                error_log("ThreadSafeDatabaseService: Successfully updated project {$projectCode} for user {$userId}");
            }
            return true;
            
        } catch (\Exception $e) {
            $connection->rollback();
            error_log("ThreadSafeDatabaseService: Exception in updateProjectSettingsSafe: " . $e->getMessage());
            return false;
        } finally {
            $connection->autocommit(true);
        }
    }

    /**
     * Safe method to remove project from control list
     * Безопасный метод удаления проекта с контроля
     */
    public function removeProjectFromControlListSafe(int $userId, string $projectCode): bool
    {
        $connection = $this->getConnection();
        
        try {
            $connection->autocommit(false);
            
            if ($this->debugMode) {
                error_log("ThreadSafeDatabaseService: Removing project {$projectCode} for user {$userId}");
            }
            
            // Блокируем запись
            $lockQuery = "SELECT data FROM ozna_data WHERE user_id = ? FOR UPDATE";
            $stmt = $connection->prepare($lockQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentRow = $result->fetch_assoc();
            
            if (!$currentRow) {
                $connection->rollback();
                return false;
            }
            
            // Получаем текущие данные
            $userData = json_decode($currentRow['data'], true);
            if (!isset($userData['control_projects'][$projectCode])) {
                $connection->rollback();
                return false; // Проект не найден
            }
            
            // Удаляем проект
            unset($userData['control_projects'][$projectCode]);
            
            // Сохраняем обновлённые данные
            $updateQuery = "UPDATE ozna_data SET data = ? WHERE user_id = ?";
            $jsonData = json_encode($userData, JSON_UNESCAPED_UNICODE);
            $updateStmt = $connection->prepare($updateQuery);
            $updateStmt->bind_param("si", $jsonData, $userId);
            $result = $updateStmt->execute();
            
            $connection->commit();
            if ($this->debugMode) {
                error_log("ThreadSafeDatabaseService: Successfully removed project {$projectCode} for user {$userId}");
            }
            return true;
            
        } catch (\Exception $e) {
            $connection->rollback();
            error_log("ThreadSafeDatabaseService: Exception in removeProjectFromControlListSafe: " . $e->getMessage());
            return false;
        } finally {
            $connection->autocommit(true);
        }
    }

    /**
     * Enable/disable debug mode
     */
    public function setDebugMode(bool $enabled): void
    {
        $this->debugMode = $enabled;
    }
}
?>
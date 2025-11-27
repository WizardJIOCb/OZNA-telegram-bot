<?php
/**
 * Command to run project cards migration
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class MigrateprojectcardsCommand extends SystemCommand
{
    protected $name = 'migrateprojectcards';
    protected $description = 'Create project cards tables';
    protected $usage = '/migrateprojectcards';
    protected $version = '1.0.0';
    protected $private_only = true;

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();

        // Only superadmin can run migrations
        if ($user_id != 230379668 && $user_id != 5261465966) {
            return $this->replyToChat('⛔ Доступ запрещён');
        }

        try {
            // Database configuration
            $databaseHost = '82.146.42.213';
            $databaseUsername = 'WizardJIOCb';
            $databasePassword = '7qQ#28051961';
            $databaseName = 'tg_bot';

            $pdo = new \PDO(
                "mysql:host={$databaseHost};dbname={$databaseName};charset=utf8mb4",
                $databaseUsername,
                $databasePassword,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            $text = "🔧 <b>Создание таблиц для карточек проектов...</b>\n\n";

            // Create customers table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS customers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $text .= "✅ Таблица customers создана\n";

            // Create business_units table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS business_units (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $text .= "✅ Таблица business_units создана\n";

            // Create project_cards table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS project_cards (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    project_code VARCHAR(50) NOT NULL UNIQUE,
                    project_name VARCHAR(255) NOT NULL,
                    customer_id INT NULL,
                    business_unit_id INT NULL,
                    smr_planned_hours DECIMAL(10,2) NULL,
                    pnr_planned_hours DECIMAL(10,2) NULL,
                    created_by BIGINT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
                    FOREIGN KEY (business_unit_id) REFERENCES business_units(id) ON DELETE SET NULL,
                    INDEX idx_project_code (project_code),
                    INDEX idx_customer (customer_id),
                    INDEX idx_business_unit (business_unit_id),
                    INDEX idx_created_by (created_by)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $text .= "✅ Таблица project_cards создана\n";

            // Create project_milestones table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS project_milestones (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    project_code VARCHAR(50) NOT NULL,
                    work_type ENUM('ШМР', 'ПНР') NOT NULL,
                    milestone_name VARCHAR(255) NOT NULL,
                    planned_hours DECIMAL(10,2) NOT NULL,
                    order_index INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (project_code) REFERENCES project_cards(project_code) ON DELETE CASCADE,
                    INDEX idx_project (project_code),
                    INDEX idx_work_type (work_type),
                    INDEX idx_order (order_index)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $text .= "✅ Таблица project_milestones создана\n\n";

            $text .= "🎉 <b>Все таблицы успешно созданы!</b>\n\n";
            $text .= "Теперь можно включить ProjectCardHandler в коде.";

            return $this->replyToChat($text, ['parse_mode' => 'HTML']);

        } catch (\Exception $e) {
            return $this->replyToChat(
                "❌ <b>Ошибка создания таблиц:</b>\n\n" . $e->getMessage(),
                ['parse_mode' => 'HTML']
            );
        }
    }
}

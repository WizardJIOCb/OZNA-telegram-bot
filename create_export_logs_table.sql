-- Create export_logs table for audit trail
CREATE TABLE IF NOT EXISTS `export_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT NOT NULL,
  `user_fio` VARCHAR(255) NOT NULL,
  `export_type` VARCHAR(50) NOT NULL COMMENT 'report or notes',
  `export_scope` VARCHAR(255) NOT NULL COMMENT 'Description of export scope',
  `project_code` VARCHAR(100) DEFAULT NULL,
  `period_start` DATETIME DEFAULT NULL,
  `period_end` DATETIME DEFAULT NULL,
  `status_filter` VARCHAR(50) DEFAULT NULL,
  `records_count` INT NOT NULL DEFAULT 0,
  `file_name` VARCHAR(255) NOT NULL,
  `exported_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_exported_at` (`exported_at`),
  INDEX `idx_export_type` (`export_type`),
  INDEX `idx_project_code` (`project_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

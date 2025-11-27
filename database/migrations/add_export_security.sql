-- Export Security System Migration
-- Adds export audit log and user export key management
-- Run: mysql -h 82.146.42.213 -u WizardJIOCb -p tg_bot < add_export_security.sql

USE tg_bot;

-- Table for logging all export operations (audit trail)
CREATE TABLE IF NOT EXISTS export_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL COMMENT 'User who exported data',
    user_fio VARCHAR(255) NOT NULL COMMENT 'User full name',
    export_type VARCHAR(50) NOT NULL COMMENT 'Type: report|notes',
    export_scope VARCHAR(100) COMMENT 'Scope: project_code|general|simple',
    project_code VARCHAR(50) COMMENT 'Project code if applicable',
    period_start DATE COMMENT 'Period start date',
    period_end DATE COMMENT 'Period end date',
    status_filter VARCHAR(20) COMMENT 'For notes: active|closed|all',
    records_count INT NOT NULL COMMENT 'Number of records exported',
    file_name VARCHAR(255) NOT NULL COMMENT 'Generated file name',
    exported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Export timestamp',
    ip_address VARCHAR(45) COMMENT 'User IP if available',
    
    INDEX idx_user_id (user_id),
    INDEX idx_export_type (export_type),
    INDEX idx_exported_at (exported_at),
    INDEX idx_project_code (project_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Audit log for all data exports';

-- Note: export_key will be stored in ozna_data.data JSON field
-- Example JSON structure in ozna_data.data:
-- {
--   "fio": "User Full Name",
--   "functional_direction": "User Functional Direction", 
--   "access_level": 1,
--   "export_key": "user_password_123",
--   "export_key_set_at": "2025-10-28 10:30:00"
-- }

-- Add comment to ozna_data table documenting the new field
ALTER TABLE ozna_data COMMENT = 'User data including profile, access level, and export_key in JSON';

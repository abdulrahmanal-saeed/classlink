-- Phase 8: Lesson Credits, Packages, Attendance, and Balance
-- Run after Phase 7 migration.

-- Upgrade credit transaction enum safely from old Phase 2 values to Phase 8 values.
-- Legacy values stay allowed for compatibility with older approval code and historic rows.
ALTER TABLE lesson_credit_transactions
  MODIFY COLUMN transaction_type ENUM('add','deduct','refund','adjust','purchase_grant','session_deducted','cancellation_return','manual_adjustment','refund_adjustment') NOT NULL,
  ADD COLUMN IF NOT EXISTS session_id INT UNSIGNED NULL AFTER student_user_id,
  ADD COLUMN IF NOT EXISTS balance_after DECIMAL(8,2) NULL AFTER credits,
  ADD COLUMN IF NOT EXISTS metadata JSON NULL AFTER reason;

UPDATE lesson_credit_transactions
SET balance_after = (
  SELECT remaining_credits FROM lesson_packages WHERE lesson_packages.id = lesson_credit_transactions.package_id
)
WHERE balance_after IS NULL;

CREATE INDEX IF NOT EXISTS idx_credit_tx_session ON lesson_credit_transactions (session_id);
CREATE INDEX IF NOT EXISTS idx_credit_tx_type_created ON lesson_credit_transactions (transaction_type, created_at);

-- Upgrade lesson session status enum safely from old values to Phase 8 values.
ALTER TABLE lesson_sessions
  MODIFY COLUMN status ENUM('scheduled','cancelled','completed','no_show','planned','confirmed','canceled_on_time','canceled_late','rescheduled') NOT NULL DEFAULT 'scheduled';

UPDATE lesson_sessions SET status = 'planned' WHERE status = 'scheduled';
UPDATE lesson_sessions SET status = 'canceled_on_time' WHERE status = 'cancelled';

ALTER TABLE lesson_sessions
  MODIFY COLUMN status ENUM('planned','confirmed','completed','canceled_on_time','canceled_late','rescheduled','no_show') NOT NULL DEFAULT 'planned',
  ADD COLUMN IF NOT EXISTS package_id INT UNSIGNED NULL AFTER teacher_user_id,
  ADD COLUMN IF NOT EXISTS credit_deducted TINYINT(1) NOT NULL DEFAULT 0 AFTER status,
  ADD COLUMN IF NOT EXISTS credit_transaction_id INT UNSIGNED NULL AFTER credit_deducted,
  ADD COLUMN IF NOT EXISTS attendance_marked_by_user_id INT UNSIGNED NULL AFTER credit_transaction_id,
  ADD COLUMN IF NOT EXISTS attendance_marked_at DATETIME NULL AFTER attendance_marked_by_user_id,
  ADD COLUMN IF NOT EXISTS cancellation_reason TEXT NULL AFTER notes;

CREATE INDEX IF NOT EXISTS idx_sessions_package ON lesson_sessions (package_id);
CREATE INDEX IF NOT EXISTS idx_sessions_status_start ON lesson_sessions (status, start_at);

CREATE TABLE IF NOT EXISTS attendance_records (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id INT UNSIGNED NOT NULL,
  student_user_id INT UNSIGNED NOT NULL,
  package_id INT UNSIGNED NULL,
  status ENUM('planned','confirmed','completed','canceled_on_time','canceled_late','rescheduled','no_show') NOT NULL,
  credit_action ENUM('none','deducted','returned','kept') NOT NULL DEFAULT 'none',
  credit_transaction_id INT UNSIGNED NULL,
  marked_by_user_id INT UNSIGNED NULL,
  notes TEXT NULL,
  marked_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_attendance_session FOREIGN KEY (session_id) REFERENCES lesson_sessions(id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_package FOREIGN KEY (package_id) REFERENCES lesson_packages(id) ON DELETE SET NULL,
  CONSTRAINT fk_attendance_marked_by FOREIGN KEY (marked_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_attendance_session (session_id),
  INDEX idx_attendance_student (student_user_id),
  INDEX idx_attendance_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('credits.no_show_deducts_credit', '1', 'lesson_credits', 'boolean', 0),
('credits.late_cancellation_deducts_credit', '1', 'lesson_credits', 'boolean', 0),
('credits.on_time_cancellation_keeps_credit', '1', 'lesson_credits', 'boolean', 0),
('credits.default_single_session_credits', '1', 'lesson_credits', 'number', 0),
('credits.default_monthly_plan_credits', '8', 'lesson_credits', 'number', 0),
('credits.default_bundle_credits', '20', 'lesson_credits', 'number', 0),
('credits.late_cancellation_hours', '24', 'lesson_credits', 'number', 0)
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

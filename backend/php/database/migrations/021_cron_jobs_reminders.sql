-- Phase 21: Automated Cron Reminders and Scheduled Jobs
-- Run after Phase 20.
-- Default timezone: Asia/Dubai.

CREATE TABLE IF NOT EXISTS scheduled_job_runs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_key VARCHAR(120) NOT NULL,
  trigger_source ENUM('cron','manual','api') NOT NULL DEFAULT 'cron',
  status ENUM('running','success','failed','partial') NOT NULL DEFAULT 'running',
  started_at DATETIME NOT NULL,
  finished_at DATETIME NULL,
  duration_ms INT UNSIGNED NULL,
  processed_count INT UNSIGNED NOT NULL DEFAULT 0,
  sent_count INT UNSIGNED NOT NULL DEFAULT 0,
  skipped_count INT UNSIGNED NOT NULL DEFAULT 0,
  failed_count INT UNSIGNED NOT NULL DEFAULT 0,
  error_message TEXT NULL,
  metadata JSON NULL,
  created_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_job_runs_key_date (job_key, started_at),
  INDEX idx_job_runs_status (status, started_at),
  CONSTRAINT fk_job_runs_user FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS scheduled_reminder_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_key VARCHAR(120) NOT NULL,
  reminder_key VARCHAR(120) NOT NULL,
  target_user_id INT UNSIGNED NULL,
  target_role ENUM('student','parent','owner','academy_partner') NULL,
  related_entity_type VARCHAR(100) NULL,
  related_entity_id VARCHAR(100) NULL,
  scheduled_for DATETIME NULL,
  delivery_channel ENUM('in_app','email','whatsapp','push','multi') NOT NULL DEFAULT 'multi',
  title VARCHAR(190) NOT NULL,
  message TEXT NULL,
  action_url VARCHAR(255) NULL,
  status ENUM('sent','skipped','failed') NOT NULL DEFAULT 'sent',
  job_run_id BIGINT UNSIGNED NULL,
  notification_id BIGINT UNSIGNED NULL,
  email_log_id BIGINT UNSIGNED NULL,
  push_log_ids TEXT NULL,
  error_message TEXT NULL,
  metadata JSON NULL,
  sent_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_reminder_dedupe (reminder_key, target_user_id, related_entity_type, related_entity_id, scheduled_for),
  INDEX idx_reminder_logs_job (job_key, sent_at),
  INDEX idx_reminder_logs_target (target_user_id, sent_at),
  CONSTRAINT fk_reminder_target_user FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_reminder_job_run FOREIGN KEY (job_run_id) REFERENCES scheduled_job_runs(id) ON DELETE SET NULL,
  CONSTRAINT fk_reminder_email_log FOREIGN KEY (email_log_id) REFERENCES email_logs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS weekly_ai_summary_queue (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_user_id INT UNSIGNED NOT NULL,
  week_start DATE NOT NULL,
  week_end DATE NOT NULL,
  status ENUM('queued','generated','failed','skipped') NOT NULL DEFAULT 'queued',
  ai_draft_id BIGINT UNSIGNED NULL,
  error_message TEXT NULL,
  queued_at DATETIME NOT NULL,
  processed_at DATETIME NULL,
  UNIQUE KEY uniq_weekly_summary_queue (student_user_id, week_start, week_end),
  INDEX idx_weekly_summary_status (status, queued_at),
  CONSTRAINT fk_weekly_queue_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_weekly_queue_ai_draft FOREIGN KEY (ai_draft_id) REFERENCES ai_drafts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('cron_enabled','1','cron','boolean',0),
('cron_secret_token','change-this-cron-token','cron','string',0),
('cron_timezone','Asia/Dubai','cron','string',0),
('cron_lesson_reminders_enabled','1','cron','boolean',0),
('cron_homework_reminders_enabled','1','cron','boolean',0),
('cron_level_check_reminders_enabled','1','cron','boolean',0),
('cron_student_form_reminders_enabled','1','cron','boolean',0),
('cron_low_credits_reminders_enabled','1','cron','boolean',0),
('cron_weekly_summaries_enabled','1','cron','boolean',0),
('cron_badge_checks_enabled','1','cron','boolean',0),
('cron_referral_checks_enabled','1','cron','boolean',0),
('cron_low_credit_threshold','2','cron','number',0),
('cron_homework_due_hours_before','24','cron','number',0),
('cron_level_check_reminder_days_after','1','cron','number',0),
('cron_student_form_reminder_days_after','1','cron','number',0)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type);

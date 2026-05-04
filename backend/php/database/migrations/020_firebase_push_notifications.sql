-- Phase 20: Firebase Push Notifications
-- Run after Phase 19.
-- Firebase secrets must stay in .env / server environment.

CREATE TABLE IF NOT EXISTS push_device_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  device_token TEXT NOT NULL,
  platform ENUM('web','android','ios','unknown') NOT NULL DEFAULT 'unknown',
  device_label VARCHAR(190) NULL,
  app_version VARCHAR(80) NULL,
  user_agent VARCHAR(255) NULL,
  status ENUM('active','disabled','invalid') NOT NULL DEFAULT 'active',
  last_seen_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_push_user_token (user_id, token_hash),
  INDEX idx_push_user_status (user_id, status),
  CONSTRAINT fk_push_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS push_notification_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  device_token_id BIGINT UNSIGNED NULL,
  target_role ENUM('owner','student','parent','academy_partner') NULL,
  event_key VARCHAR(120) NOT NULL,
  title VARCHAR(190) NOT NULL,
  body TEXT NULL,
  action_url VARCHAR(255) NULL,
  payload_json JSON NULL,
  provider VARCHAR(80) NOT NULL DEFAULT 'firebase_fcm',
  provider_message_id VARCHAR(255) NULL,
  status ENUM('queued','sent','failed','skipped') NOT NULL DEFAULT 'queued',
  error_message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sent_at DATETIME NULL,
  INDEX idx_push_logs_user (user_id, created_at),
  INDEX idx_push_logs_event (event_key, status, created_at),
  CONSTRAINT fk_push_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_push_logs_token FOREIGN KEY (device_token_id) REFERENCES push_device_tokens(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS push_notification_preferences (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  event_key VARCHAR(120) NOT NULL,
  is_enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_push_pref_user_event (user_id, event_key),
  CONSTRAINT fk_push_prefs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('push_enabled','1','push','boolean',0),
('push_provider','firebase_fcm','push','string',0),
('firebase_project_id','','push','string',0),
('firebase_service_account_json_env','FIREBASE_SERVICE_ACCOUNT_JSON','push','string',0),
('firebase_service_account_path_env','GOOGLE_APPLICATION_CREDENTIALS','push','string',0),
('push_owner_default_enabled','1','push','boolean',0),
('push_student_parent_default_enabled','0','push','boolean',0)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type);

INSERT INTO push_notification_preferences (user_id, event_key, is_enabled)
SELECT users.id, events.event_key, 1
FROM users
JOIN (
  SELECT 'payment_pending_verification' AS event_key UNION ALL
  SELECT 'student_form_submitted' UNION ALL
  SELECT 'level_check_submitted' UNION ALL
  SELECT 'homework_submitted' UNION ALL
  SELECT 'scenario_submitted' UNION ALL
  SELECT 'review_submitted' UNION ALL
  SELECT 'testimonial_submitted' UNION ALL
  SELECT 'academy_brief_submitted' UNION ALL
  SELECT 'booking_requested'
) events
WHERE users.role = 'owner_teacher'
ON DUPLICATE KEY UPDATE is_enabled = is_enabled;

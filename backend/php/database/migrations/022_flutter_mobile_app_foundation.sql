-- Phase 22: Flutter Mobile App Foundation
-- Run after Phase 21.
-- The web platform remains the source of truth.

CREATE TABLE IF NOT EXISTS mobile_auth_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  device_label VARCHAR(190) NULL,
  platform ENUM('flutter_android','flutter_ios','flutter_web','unknown') NOT NULL DEFAULT 'unknown',
  status ENUM('active','revoked','expired') NOT NULL DEFAULT 'active',
  expires_at DATETIME NULL,
  last_used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  revoked_at DATETIME NULL,
  INDEX idx_mobile_tokens_user_status (user_id, status),
  CONSTRAINT fk_mobile_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('mobile_api_enabled','1','mobile','boolean',0),
('mobile_token_ttl_days','30','mobile','number',0),
('mobile_app_min_supported_version','1.0.0','mobile','string',0),
('mobile_app_backend_base_url','https://staging.mshabibanabil.com','mobile','string',0)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type);

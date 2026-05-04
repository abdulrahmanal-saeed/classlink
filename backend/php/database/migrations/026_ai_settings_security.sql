-- Phase 26: AI Settings and Security
-- AI must not work unless Owner configures provider + API key.
-- API keys are not exposed to frontend.

CREATE TABLE IF NOT EXISTS ai_provider_secrets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider ENUM('openai','anthropic','gemini','other') NOT NULL,
  encrypted_api_key MEDIUMTEXT NULL,
  key_last4 VARCHAR(12) NULL,
  key_masked VARCHAR(80) NULL,
  status ENUM('not_configured','configured','connection_failed') NOT NULL DEFAULT 'not_configured',
  last_tested_at DATETIME NULL,
  last_error TEXT NULL,
  updated_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_ai_provider_secret (provider),
  CONSTRAINT fk_ai_provider_secret_user FOREIGN KEY (updated_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_usage_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tool_name VARCHAR(120) NOT NULL,
  provider ENUM('openai','anthropic','gemini','other') NULL,
  model_name VARCHAR(190) NULL,
  related_entity_type VARCHAR(80) NULL,
  related_entity_id VARCHAR(80) NULL,
  student_user_id INT UNSIGNED NULL,
  prompt LONGTEXT NULL,
  response LONGTEXT NULL,
  estimated_input_tokens INT UNSIGNED NULL,
  estimated_output_tokens INT UNSIGNED NULL,
  estimated_cost DECIMAL(12,6) NULL,
  status ENUM('success','failed','blocked_not_configured','draft_saved') NOT NULL DEFAULT 'success',
  error_message TEXT NULL,
  created_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ai_usage_created (created_at),
  INDEX idx_ai_usage_tool_status (tool_name, status),
  INDEX idx_ai_usage_related (related_entity_type, related_entity_id),
  CONSTRAINT fk_ai_usage_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_ai_usage_creator FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public) VALUES
('ai_features_enabled','0','ai','boolean',0),
('ai_provider','anthropic','ai','string',0),
('ai_model_name','claude-3-5-sonnet-latest','ai','string',0),
('ai_regenerate_limit_per_tool','3','ai','number',0),
('ai_monthly_usage_limit','0','ai','number',0),
('ai_output_mode','preview_draft_only','ai','string',0),
('ai_connection_status','not_configured','ai','string',0)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type), is_public=VALUES(is_public);

INSERT INTO ai_provider_secrets (provider, status) VALUES
('openai','not_configured'),
('anthropic','not_configured'),
('gemini','not_configured'),
('other','not_configured')
ON DUPLICATE KEY UPDATE provider=VALUES(provider);

-- Phase 16: AI Teacher and Marketing Tools
-- Run after Phase 15.
-- Anthropic API key must stay in .env / server environment as ANTHROPIC_API_KEY.

ALTER TABLE ai_usage_logs
  ADD COLUMN IF NOT EXISTS related_type VARCHAR(100) NULL AFTER tool_name,
  ADD COLUMN IF NOT EXISTS related_id VARCHAR(100) NULL AFTER related_type,
  ADD COLUMN IF NOT EXISTS model_name VARCHAR(120) NULL AFTER related_id,
  ADD COLUMN IF NOT EXISTS prompt_text LONGTEXT NULL AFTER model_name,
  ADD COLUMN IF NOT EXISTS response_text LONGTEXT NULL AFTER prompt_text,
  ADD COLUMN IF NOT EXISTS response_json JSON NULL AFTER response_text,
  ADD COLUMN IF NOT EXISTS estimated_tokens INT UNSIGNED NULL AFTER completion_tokens,
  ADD COLUMN IF NOT EXISTS error_message TEXT NULL AFTER status;

CREATE INDEX IF NOT EXISTS idx_ai_usage_tool_related ON ai_usage_logs (tool_name, related_type, related_id);
CREATE INDEX IF NOT EXISTS idx_ai_usage_user_date ON ai_usage_logs (user_id, created_at);

CREATE TABLE IF NOT EXISTS ai_drafts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  tool_name VARCHAR(120) NOT NULL,
  related_type VARCHAR(100) NULL,
  related_id VARCHAR(100) NULL,
  title VARCHAR(190) NULL,
  prompt_text LONGTEXT NULL,
  response_text LONGTEXT NULL,
  response_json JSON NULL,
  status ENUM('draft','applied','discarded') NOT NULL DEFAULT 'draft',
  applied_to_type VARCHAR(100) NULL,
  applied_to_id VARCHAR(100) NULL,
  usage_log_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  applied_at DATETIME NULL,
  INDEX idx_ai_drafts_user_tool (user_id, tool_name, status),
  INDEX idx_ai_drafts_related (related_type, related_id),
  CONSTRAINT fk_ai_drafts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_ai_drafts_usage FOREIGN KEY (usage_log_id) REFERENCES ai_usage_logs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS weekly_student_summaries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_user_id INT UNSIGNED NOT NULL,
  generated_by_user_id INT UNSIGNED NULL,
  week_start DATE NULL,
  week_end DATE NULL,
  summary_text TEXT NULL,
  went_well TEXT NULL,
  focus_areas TEXT NULL,
  next_week_focus TEXT NULL,
  engagement_level ENUM('High','Medium','Low') NOT NULL DEFAULT 'Medium',
  source_ai_draft_id BIGINT UNSIGNED NULL,
  status ENUM('draft','saved','sent') NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_weekly_summaries_student (student_user_id, week_start),
  CONSTRAINT fk_weekly_summary_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_weekly_summary_generated_by FOREIGN KEY (generated_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_weekly_summary_ai_draft FOREIGN KEY (source_ai_draft_id) REFERENCES ai_drafts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE articles
  ADD COLUMN IF NOT EXISTS seo_meta_title VARCHAR(190) NULL AFTER slug,
  ADD COLUMN IF NOT EXISTS seo_meta_description TEXT NULL AFTER seo_meta_title,
  ADD COLUMN IF NOT EXISTS keywords TEXT NULL AFTER seo_meta_description,
  ADD COLUMN IF NOT EXISTS cta TEXT NULL AFTER keywords,
  ADD COLUMN IF NOT EXISTS target_audience VARCHAR(190) NULL AFTER cta,
  ADD COLUMN IF NOT EXISTS cover_image_prompt TEXT NULL AFTER target_audience,
  ADD COLUMN IF NOT EXISTS source_ai_draft_id BIGINT UNSIGNED NULL AFTER cover_image_prompt;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('ai_enabled','1','ai','boolean',0),
('ai_provider','anthropic','ai','string',0),
('ai_default_model','claude-3-5-sonnet-latest','ai','string',0),
('ai_regenerate_limit_per_tool_per_day','3','ai','number',0),
('ai_estimated_cost_per_1k_tokens','0.0000','ai','number',0),
('ai_max_preview_tokens','2500','ai','number',0)
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_group = VALUES(setting_group), value_type = VALUES(value_type);

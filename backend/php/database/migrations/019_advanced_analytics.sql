-- Phase 19: Advanced Analytics
-- Run after Phase 18.

ALTER TABLE analytics_events
  ADD COLUMN IF NOT EXISTS event_category ENUM('public','learning','marketing','revenue','content','referral','system') NOT NULL DEFAULT 'public' AFTER event_name,
  ADD COLUMN IF NOT EXISTS session_id VARCHAR(120) NULL AFTER user_id,
  ADD COLUMN IF NOT EXISTS visitor_id VARCHAR(120) NULL AFTER session_id,
  ADD COLUMN IF NOT EXISTS role VARCHAR(50) NULL AFTER visitor_id,
  ADD COLUMN IF NOT EXISTS entity_type VARCHAR(100) NULL AFTER page_url,
  ADD COLUMN IF NOT EXISTS entity_id VARCHAR(100) NULL AFTER entity_type,
  ADD COLUMN IF NOT EXISTS referrer_url VARCHAR(255) NULL AFTER entity_id,
  ADD COLUMN IF NOT EXISTS utm_source VARCHAR(120) NULL AFTER referrer_url,
  ADD COLUMN IF NOT EXISTS utm_medium VARCHAR(120) NULL AFTER utm_source,
  ADD COLUMN IF NOT EXISTS utm_campaign VARCHAR(120) NULL AFTER utm_medium,
  ADD COLUMN IF NOT EXISTS device_type VARCHAR(50) NULL AFTER utm_campaign,
  ADD COLUMN IF NOT EXISTS ip_hash CHAR(64) NULL AFTER device_type;

CREATE INDEX IF NOT EXISTS idx_analytics_category_date ON analytics_events (event_category, created_at);
CREATE INDEX IF NOT EXISTS idx_analytics_visitor_date ON analytics_events (visitor_id, created_at);
CREATE INDEX IF NOT EXISTS idx_analytics_entity ON analytics_events (entity_type, entity_id);
CREATE INDEX IF NOT EXISTS idx_analytics_event_date ON analytics_events (event_name, created_at);

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('analytics_enabled','1','analytics','boolean',0),
('analytics_privacy_mode','privacy_first','analytics','string',0),
('analytics_track_public_pages','1','analytics','boolean',0),
('analytics_track_learning_events','1','analytics','boolean',0),
('analytics_ip_hash_salt','change-this-salt-in-production','analytics','string',0),
('analytics_retention_days','365','analytics','number',0)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type);

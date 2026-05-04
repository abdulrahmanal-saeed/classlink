-- Phase 23: Bilingual Arabic/English localization foundation
-- Run after Phase 22.

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS preferred_language ENUM('ar','en') NULL AFTER display_name;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('platform_default_language','en','localization','string',1),
('platform_supported_languages','ar,en','localization','string',1),
('platform_language_cookie_days','365','localization','number',1),
('localization_rtl_enabled','1','localization','boolean',1),
('localization_copy_tone_ar','neutral_professional','localization','string',0),
('localization_copy_tone_en','natural_saas','localization','string',0)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type), is_public=VALUES(is_public);

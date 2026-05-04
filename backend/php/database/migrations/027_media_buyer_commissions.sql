-- Phase 27: Media Buyer / Marketing Partner Dashboard, Tracking, Analytics, and Commission System
-- MySQL implementation using existing PHP auth, audit log, notifications, and payment flow.

ALTER TABLE users
  MODIFY role ENUM('owner_teacher','student','parent','academy_partner','media_buyer') NOT NULL;

CREATE TABLE IF NOT EXISTS media_buyer_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  display_name VARCHAR(190) NOT NULL,
  partner_code VARCHAR(80) NOT NULL UNIQUE,
  commission_type ENUM('percentage','fixed','none') NOT NULL DEFAULT 'percentage',
  commission_rate DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
  fixed_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('active','disabled','pending') NOT NULL DEFAULT 'active',
  payout_method VARCHAR(120) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_media_buyer_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketing_campaigns (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  media_buyer_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(190) NOT NULL,
  utm_source VARCHAR(120) NULL,
  utm_medium VARCHAR(120) NULL,
  utm_campaign VARCHAR(190) NULL,
  utm_content VARCHAR(190) NULL,
  landing_page VARCHAR(255) NULL,
  coupon_code VARCHAR(80) NULL,
  status ENUM('active','paused','archived') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_campaign_buyer_status (media_buyer_id, status),
  CONSTRAINT fk_campaign_media_buyer FOREIGN KEY (media_buyer_id) REFERENCES media_buyer_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attribution_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  media_buyer_id BIGINT UNSIGNED NULL,
  campaign_id BIGINT UNSIGNED NULL,
  event_type VARCHAR(80) NOT NULL,
  session_id VARCHAR(128) NULL,
  visitor_id_hash CHAR(64) NULL,
  checkout_order_id VARCHAR(80) NULL,
  utm_data JSON NULL,
  landing_page VARCHAR(255) NULL,
  referrer VARCHAR(255) NULL,
  first_touch_at DATETIME NULL,
  last_touch_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_attr_events_buyer_type (media_buyer_id, event_type, created_at),
  INDEX idx_attr_events_order (checkout_order_id),
  CONSTRAINT fk_attr_event_buyer FOREIGN KEY (media_buyer_id) REFERENCES media_buyer_profiles(id) ON DELETE SET NULL,
  CONSTRAINT fk_attr_event_campaign FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_attributions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  checkout_order_id VARCHAR(80) NOT NULL UNIQUE,
  media_buyer_id BIGINT UNSIGNED NULL,
  campaign_id BIGINT UNSIGNED NULL,
  partner_code VARCHAR(80) NULL,
  attribution_model ENUM('first_touch','last_touch','manual') NOT NULL DEFAULT 'last_touch',
  utm_data JSON NULL,
  landing_page VARCHAR(255) NULL,
  selected_plan VARCHAR(80) NULL,
  order_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  payment_status VARCHAR(80) NOT NULL DEFAULT 'pending',
  customer_name_masked VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_order_attr_buyer_status (media_buyer_id, payment_status),
  CONSTRAINT fk_order_attr_buyer FOREIGN KEY (media_buyer_id) REFERENCES media_buyer_profiles(id) ON DELETE SET NULL,
  CONSTRAINT fk_order_attr_campaign FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commission_records (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  media_buyer_id BIGINT UNSIGNED NOT NULL,
  checkout_order_id VARCHAR(80) NOT NULL,
  package_name VARCHAR(120) NULL,
  order_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  commission_type ENUM('percentage','fixed','none') NOT NULL DEFAULT 'percentage',
  commission_rate DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
  commission_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('pending','approved','rejected','paid','reversed') NOT NULL DEFAULT 'pending',
  reason TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  approved_at DATETIME NULL,
  paid_at DATETIME NULL,
  reversed_at DATETIME NULL,
  UNIQUE KEY uniq_commission_order_buyer (media_buyer_id, checkout_order_id),
  INDEX idx_commissions_buyer_status (media_buyer_id, status),
  CONSTRAINT fk_commission_buyer FOREIGN KEY (media_buyer_id) REFERENCES media_buyer_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payout_records (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  media_buyer_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('pending','approved','paid','cancelled') NOT NULL DEFAULT 'pending',
  payout_method VARCHAR(120) NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  CONSTRAINT fk_payout_buyer FOREIGN KEY (media_buyer_id) REFERENCES media_buyer_profiles(id) ON DELETE CASCADE,
  INDEX idx_payout_buyer_status (media_buyer_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public) VALUES
('media_buyers_enabled','1','media_buyers','boolean',0),
('media_global_commission_type','percentage','media_buyers','string',0),
('media_global_commission_rate','10','media_buyers','number',0),
('media_fixed_commission_amount','0','media_buyers','number',0),
('media_commission_approval_required','1','media_buyers','boolean',0),
('media_payout_cycle','manual','media_buyers','string',0),
('media_cookie_days','30','media_buyers','number',1),
('media_default_attribution_model','last_touch','media_buyers','string',0),
('media_export_enabled','1','media_buyers','boolean',0)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type), is_public=VALUES(is_public);

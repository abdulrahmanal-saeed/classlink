-- Phase 18: Referral System
-- Run after Phase 17.

CREATE TABLE IF NOT EXISTS referral_codes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT UNSIGNED NOT NULL,
  code VARCHAR(40) NOT NULL UNIQUE,
  status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  landing_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_referral_code_owner (owner_user_id),
  CONSTRAINT fk_referral_codes_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE referrals
  MODIFY COLUMN status ENUM('pending','qualified','reward_pending','reward_applied','rejected','new','converted','rewarded','cancelled') NOT NULL DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS referral_code_id INT UNSIGNED NULL AFTER referrer_user_id,
  ADD COLUMN IF NOT EXISTS source_referral_code VARCHAR(40) NULL AFTER referral_code_id,
  ADD COLUMN IF NOT EXISTS referred_user_id INT UNSIGNED NULL AFTER referred_email,
  ADD COLUMN IF NOT EXISTS referred_name VARCHAR(190) NULL AFTER referred_user_id,
  ADD COLUMN IF NOT EXISTS purchase_id INT UNSIGNED NULL AFTER referred_name,
  ADD COLUMN IF NOT EXISTS payment_record_id INT UNSIGNED NULL AFTER purchase_id,
  ADD COLUMN IF NOT EXISTS reward_type ENUM('free_session','aed_discount','both') NULL AFTER reward_amount,
  ADD COLUMN IF NOT EXISTS reward_value DECIMAL(10,2) NULL AFTER reward_type,
  ADD COLUMN IF NOT EXISTS reward_credit_amount DECIMAL(8,2) NULL AFTER reward_value,
  ADD COLUMN IF NOT EXISTS reward_discount_amount DECIMAL(10,2) NULL AFTER reward_credit_amount,
  ADD COLUMN IF NOT EXISTS applied_by_user_id INT UNSIGNED NULL AFTER reward_discount_amount,
  ADD COLUMN IF NOT EXISTS qualified_at DATETIME NULL AFTER applied_by_user_id,
  ADD COLUMN IF NOT EXISTS reward_applied_at DATETIME NULL AFTER qualified_at,
  ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER reward_applied_at,
  ADD CONSTRAINT fk_referrals_code FOREIGN KEY (referral_code_id) REFERENCES referral_codes(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_referrals_referred_user FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_referrals_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_referrals_payment_record FOREIGN KEY (payment_record_id) REFERENCES payment_records(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_referrals_applied_by FOREIGN KEY (applied_by_user_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE purchases
  ADD COLUMN IF NOT EXISTS referral_code VARCHAR(40) NULL AFTER source,
  ADD COLUMN IF NOT EXISTS referral_code_id INT UNSIGNED NULL AFTER referral_code,
  ADD COLUMN IF NOT EXISTS referral_id INT UNSIGNED NULL AFTER referral_code_id;

ALTER TABLE payment_records
  ADD COLUMN IF NOT EXISTS referral_code VARCHAR(40) NULL AFTER provider_reference,
  ADD COLUMN IF NOT EXISTS referral_id INT UNSIGNED NULL AFTER referral_code;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('referral_program_enabled','1','referrals','boolean',0),
('referral_reward_type','free_session','referrals','string',0),
('referral_reward_value','1','referrals','number',0),
('referral_terms_text','Share your referral link. Rewards are applied manually after the referred student payment is verified and approved.','referrals','text',0),
('referral_public_base_url','https://mshabibanabil.com/?ref=','referrals','string',0)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), setting_group=VALUES(setting_group), value_type=VALUES(value_type);

INSERT INTO referral_codes (owner_user_id, code)
SELECT users.id, CONCAT('HN', users.id, UPPER(SUBSTRING(MD5(CONCAT(users.id, users.email)), 1, 5)))
FROM users
WHERE users.role IN ('student','parent')
  AND NOT EXISTS (SELECT 1 FROM referral_codes rc WHERE rc.owner_user_id = users.id);

CREATE INDEX IF NOT EXISTS idx_referrals_status ON referrals (status, created_at);
CREATE INDEX IF NOT EXISTS idx_referrals_referrer ON referrals (referrer_user_id, status);
CREATE INDEX IF NOT EXISTS idx_referrals_purchase ON referrals (purchase_id, payment_record_id);

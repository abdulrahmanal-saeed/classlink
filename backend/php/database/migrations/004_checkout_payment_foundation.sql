-- Phase 4: Checkout, Policy Agreement, Payment Status, and Thank You Page
-- Run after Phase 3 migration.

ALTER TABLE purchases
  MODIFY COLUMN status ENUM('pending', 'pending_verification', 'paid', 'failed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending';

ALTER TABLE payment_records
  MODIFY COLUMN status ENUM('pending', 'pending_verification', 'verified', 'failed', 'refunded', 'manual_approved') NOT NULL DEFAULT 'pending';

ALTER TABLE purchases
  ADD COLUMN IF NOT EXISTS checkout_reference VARCHAR(64) NULL AFTER id,
  ADD COLUMN IF NOT EXISTS full_name VARCHAR(190) NULL AFTER checkout_reference,
  ADD COLUMN IF NOT EXISTS email VARCHAR(190) NULL AFTER full_name,
  ADD COLUMN IF NOT EXISTS whatsapp VARCHAR(80) NULL AFTER email,
  ADD COLUMN IF NOT EXISTS student_age INT UNSIGNED NULL AFTER whatsapp,
  ADD COLUMN IF NOT EXISTS learner_type ENUM('adult', 'child', 'someone_else') NULL AFTER student_age,
  ADD COLUMN IF NOT EXISTS main_goal VARCHAR(120) NULL AFTER learner_type,
  ADD COLUMN IF NOT EXISTS preferred_contact_method ENUM('whatsapp', 'email') NULL AFTER main_goal,
  ADD COLUMN IF NOT EXISTS policy_agreed_at DATETIME NULL AFTER preferred_contact_method,
  ADD COLUMN IF NOT EXISTS payment_redirect_url VARCHAR(500) NULL AFTER policy_agreed_at;

CREATE UNIQUE INDEX IF NOT EXISTS uniq_purchases_checkout_reference ON purchases (checkout_reference);
CREATE INDEX IF NOT EXISTS idx_purchases_email ON purchases (email);
CREATE INDEX IF NOT EXISTS idx_purchases_status ON purchases (status);

ALTER TABLE payment_records
  ADD COLUMN IF NOT EXISTS checkout_reference VARCHAR(64) NULL AFTER id,
  ADD COLUMN IF NOT EXISTS manual_status_note TEXT NULL AFTER notes;

CREATE INDEX IF NOT EXISTS idx_payment_records_reference ON payment_records (checkout_reference);

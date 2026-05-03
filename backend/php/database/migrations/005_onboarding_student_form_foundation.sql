-- Phase 5: Post-Payment Student Form and Onboarding Pipeline
-- Run after Phase 4B migration.

ALTER TABLE purchases
  ADD COLUMN IF NOT EXISTS student_form_status ENUM('not_started', 'submitted') NOT NULL DEFAULT 'not_started' AFTER payment_redirect_url,
  ADD COLUMN IF NOT EXISTS level_check_status ENUM('not_started', 'submitted', 'reviewed') NOT NULL DEFAULT 'not_started' AFTER student_form_status,
  ADD COLUMN IF NOT EXISTS schedule_status ENUM('not_selected', 'requested', 'confirmed') NOT NULL DEFAULT 'not_selected' AFTER level_check_status,
  ADD COLUMN IF NOT EXISTS owner_review_status ENUM('pending_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending_review' AFTER schedule_status;

CREATE INDEX IF NOT EXISTS idx_purchases_onboarding_statuses ON purchases (student_form_status, level_check_status, schedule_status, owner_review_status);

ALTER TABLE student_intake_forms
  ADD COLUMN IF NOT EXISTS purchase_id INT UNSIGNED NULL AFTER id,
  ADD COLUMN IF NOT EXISTS checkout_reference VARCHAR(64) NULL AFTER purchase_id,
  ADD COLUMN IF NOT EXISTS learner_type ENUM('adult', 'child', 'someone_else_adult', 'someone_else_child') NULL AFTER checkout_reference,
  ADD COLUMN IF NOT EXISTS submitted_at DATETIME NULL AFTER raw_payload,
  ADD COLUMN IF NOT EXISTS owner_review_status ENUM('pending_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending_review' AFTER submitted_at,
  ADD COLUMN IF NOT EXISTS owner_review_note TEXT NULL AFTER owner_review_status;

CREATE INDEX IF NOT EXISTS idx_student_intake_purchase ON student_intake_forms (purchase_id);
CREATE INDEX IF NOT EXISTS idx_student_intake_reference ON student_intake_forms (checkout_reference);
CREATE INDEX IF NOT EXISTS idx_student_intake_review_status ON student_intake_forms (owner_review_status);

CREATE TABLE IF NOT EXISTS email_fallback_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT UNSIGNED NULL,
  checkout_reference VARCHAR(64) NULL,
  recipient_email VARCHAR(190) NOT NULL,
  subject VARCHAR(190) NOT NULL,
  body TEXT NULL,
  reason VARCHAR(190) NOT NULL DEFAULT 'email_provider_not_configured',
  status ENUM('logged', 'sent', 'failed') NOT NULL DEFAULT 'logged',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email_fallback_purchase (purchase_id),
  INDEX idx_email_fallback_reference (checkout_reference),
  CONSTRAINT fk_email_fallback_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

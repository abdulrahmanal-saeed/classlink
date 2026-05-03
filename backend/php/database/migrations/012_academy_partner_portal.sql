-- Phase 12: Academy Partner Portal
-- Run after Phase 11. This migration expands the old Phase 2 academy_briefs table.

ALTER TABLE academy_briefs
  MODIFY COLUMN status ENUM('new','reviewed','approved','submitted','under_review','converted_to_student','rejected') NOT NULL DEFAULT 'submitted',
  ADD COLUMN IF NOT EXISTS student_name VARCHAR(190) NULL AFTER academy_name,
  ADD COLUMN IF NOT EXISTS age INT UNSIGNED NULL AFTER student_name,
  ADD COLUMN IF NOT EXISTS nationality_country VARCHAR(120) NULL AFTER age,
  ADD COLUMN IF NOT EXISTS current_arabic_level VARCHAR(100) NULL AFTER nationality_country,
  ADD COLUMN IF NOT EXISTS goal TEXT NULL AFTER current_arabic_level,
  ADD COLUMN IF NOT EXISTS speaking_ability TEXT NULL AFTER goal,
  ADD COLUMN IF NOT EXISTS reading_writing_ability TEXT NULL AFTER speaking_ability,
  ADD COLUMN IF NOT EXISTS notes_from_academy TEXT NULL AFTER reading_writing_ability,
  ADD COLUMN IF NOT EXISTS parent_contact_info TEXT NULL AFTER notes_from_academy,
  ADD COLUMN IF NOT EXISTS preferred_schedule TEXT NULL AFTER parent_contact_info,
  ADD COLUMN IF NOT EXISTS internal_notes TEXT NULL AFTER preferred_schedule,
  ADD COLUMN IF NOT EXISTS converted_student_user_id INT UNSIGNED NULL AFTER internal_notes,
  ADD COLUMN IF NOT EXISTS converted_intake_form_id INT UNSIGNED NULL AFTER converted_student_user_id,
  ADD COLUMN IF NOT EXISTS reviewed_by_user_id INT UNSIGNED NULL AFTER converted_intake_form_id,
  ADD COLUMN IF NOT EXISTS reviewed_at DATETIME NULL AFTER reviewed_by_user_id,
  ADD COLUMN IF NOT EXISTS converted_by_user_id INT UNSIGNED NULL AFTER reviewed_at,
  ADD COLUMN IF NOT EXISTS converted_at DATETIME NULL AFTER converted_by_user_id;

UPDATE academy_briefs SET status = 'submitted' WHERE status = 'new';
UPDATE academy_briefs SET status = 'under_review' WHERE status = 'reviewed';
UPDATE academy_briefs SET status = 'converted_to_student' WHERE status = 'approved';

CREATE INDEX IF NOT EXISTS idx_academy_briefs_partner_status ON academy_briefs (academy_partner_user_id, status);
CREATE INDEX IF NOT EXISTS idx_academy_briefs_status_created ON academy_briefs (status, created_at);
CREATE INDEX IF NOT EXISTS idx_academy_briefs_converted_student ON academy_briefs (converted_student_user_id);

-- Phase 6: Adult Level Check and Child Literacy Check
-- Run after Phase 5 migration.

ALTER TABLE level_check_attempts
  ADD COLUMN IF NOT EXISTS intake_form_id INT UNSIGNED NULL AFTER id,
  ADD COLUMN IF NOT EXISTS checkout_reference VARCHAR(64) NULL AFTER intake_form_id,
  ADD COLUMN IF NOT EXISTS vocabulary_score DECIMAL(5,2) NULL AFTER auto_score,
  ADD COLUMN IF NOT EXISTS sentence_score DECIMAL(5,2) NULL AFTER vocabulary_score,
  ADD COLUMN IF NOT EXISTS reading_score DECIMAL(5,2) NULL AFTER sentence_score,
  ADD COLUMN IF NOT EXISTS letter_score DECIMAL(5,2) NULL AFTER reading_score,
  ADD COLUMN IF NOT EXISTS suggested_level VARCHAR(100) NULL AFTER final_level,
  ADD COLUMN IF NOT EXISTS recommended_first_lesson TEXT NULL AFTER suggested_level,
  ADD COLUMN IF NOT EXISTS manual_score DECIMAL(5,2) NULL AFTER recommended_first_lesson,
  ADD COLUMN IF NOT EXISTS teacher_notes TEXT NULL AFTER manual_score;

CREATE INDEX IF NOT EXISTS idx_level_attempt_intake ON level_check_attempts (intake_form_id);
CREATE INDEX IF NOT EXISTS idx_level_attempt_reference ON level_check_attempts (checkout_reference);
CREATE INDEX IF NOT EXISTS idx_level_attempt_status ON level_check_attempts (status);

ALTER TABLE level_check_answers
  ADD COLUMN IF NOT EXISTS section_key VARCHAR(100) NULL AFTER attempt_id,
  ADD COLUMN IF NOT EXISTS question_text TEXT NULL AFTER question_key,
  ADD COLUMN IF NOT EXISTS correct_answer TEXT NULL AFTER answer_text,
  ADD COLUMN IF NOT EXISTS metadata JSON NULL AFTER score;

ALTER TABLE level_check_uploads
  ADD COLUMN IF NOT EXISTS original_filename VARCHAR(255) NULL AFTER file_path,
  ADD COLUMN IF NOT EXISTS mime_type VARCHAR(120) NULL AFTER file_type,
  ADD COLUMN IF NOT EXISTS size_bytes INT UNSIGNED NULL AFTER mime_type;

ALTER TABLE student_intake_forms
  ADD COLUMN IF NOT EXISTS recommended_first_lesson TEXT NULL AFTER owner_review_note;

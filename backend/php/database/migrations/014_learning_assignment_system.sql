-- Phase 14: Homework, Speaking Scenarios, Reviews/Tests, Materials
-- Run after Phase 13. This migration expands assignment/review/material tables without breaking old data.

ALTER TABLE homework_questions
  ADD COLUMN IF NOT EXISTS points DECIMAL(6,2) NOT NULL DEFAULT 1.00 AFTER answer_key,
  ADD COLUMN IF NOT EXISTS media_url VARCHAR(255) NULL AFTER points,
  ADD COLUMN IF NOT EXISTS explanation TEXT NULL AFTER media_url;

ALTER TABLE homework_submissions
  ADD COLUMN IF NOT EXISTS score DECIMAL(6,2) NULL AFTER submitted_payload,
  ADD COLUMN IF NOT EXISTS max_score DECIMAL(6,2) NULL AFTER score,
  ADD COLUMN IF NOT EXISTS manual_override_payload JSON NULL AFTER max_score,
  ADD COLUMN IF NOT EXISTS reviewed_by_user_id INT UNSIGNED NULL AFTER feedback;

ALTER TABLE scenario_submissions
  ADD COLUMN IF NOT EXISTS owner_feedback TEXT NULL AFTER feedback,
  ADD COLUMN IF NOT EXISTS reviewed_by_user_id INT UNSIGNED NULL AFTER owner_feedback;

ALTER TABLE review_tests
  ADD COLUMN IF NOT EXISTS instructions TEXT NULL AFTER title,
  ADD COLUMN IF NOT EXISTS manual_override_note TEXT NULL AFTER status;

ALTER TABLE review_questions
  ADD COLUMN IF NOT EXISTS points DECIMAL(6,2) NOT NULL DEFAULT 1.00 AFTER answer_key,
  ADD COLUMN IF NOT EXISTS media_url VARCHAR(255) NULL AFTER points,
  ADD COLUMN IF NOT EXISTS explanation TEXT NULL AFTER media_url;

ALTER TABLE review_submissions
  ADD COLUMN IF NOT EXISTS max_score DECIMAL(6,2) NULL AFTER score,
  ADD COLUMN IF NOT EXISTS manual_override_payload JSON NULL AFTER max_score,
  ADD COLUMN IF NOT EXISTS reviewed_by_user_id INT UNSIGNED NULL AFTER feedback;

ALTER TABLE course_materials
  MODIFY COLUMN material_type ENUM('pdf','video','youtube','audio','link','text','html','powerpoint','file') NOT NULL DEFAULT 'text',
  ADD COLUMN IF NOT EXISTS assigned_student_user_id INT UNSIGNED NULL AFTER level,
  ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER content,
  ADD COLUMN IF NOT EXISTS created_by_user_id INT UNSIGNED NULL AFTER is_active;

CREATE INDEX IF NOT EXISTS idx_homework_questions_homework_order ON homework_questions (homework_id, sort_order);
CREATE INDEX IF NOT EXISTS idx_review_questions_test_order ON review_questions (review_test_id, sort_order);
CREATE INDEX IF NOT EXISTS idx_homework_submissions_student_homework ON homework_submissions (student_user_id, homework_id);
CREATE INDEX IF NOT EXISTS idx_review_submissions_student_test ON review_submissions (student_user_id, review_test_id);
CREATE INDEX IF NOT EXISTS idx_materials_student_level ON course_materials (assigned_student_user_id, level, is_active);

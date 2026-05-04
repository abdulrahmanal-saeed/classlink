-- Phase 15: Practice Words, Flashcards, Progress, Streaks, Badges, and Badge Settings
-- Run after Phase 14.

CREATE TABLE IF NOT EXISTS learning_activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_user_id INT UNSIGNED NOT NULL,
  activity_type ENUM('homework_submitted','scenario_submitted','review_taken','flashcards_reviewed','session_completed','level_check_completed','badge_awarded','practice_word_added') NOT NULL,
  source_type VARCHAR(100) NULL,
  source_id VARCHAR(100) NULL,
  points DECIMAL(8,2) NOT NULL DEFAULT 1.00,
  metadata JSON NULL,
  activity_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_student_date (student_user_id, activity_date),
  INDEX idx_activity_student_type (student_user_id, activity_type),
  CONSTRAINT fk_learning_activity_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE badge_definitions
  ADD COLUMN IF NOT EXISTS trigger_type ENUM('manual','activity_count','streak_days','sessions_completed','practice_words_mastered','scenarios_submitted','homework_submitted','level_check_completed') NOT NULL DEFAULT 'manual' AFTER icon,
  ADD COLUMN IF NOT EXISTS required_value INT UNSIGNED NOT NULL DEFAULT 1 AFTER trigger_type,
  ADD COLUMN IF NOT EXISTS display_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER required_value,
  ADD COLUMN IF NOT EXISTS visibility ENUM('public','student_parent','owner_only') NOT NULL DEFAULT 'student_parent' AFTER display_order,
  ADD COLUMN IF NOT EXISTS color_style VARCHAR(120) NULL AFTER visibility;

ALTER TABLE student_badges
  ADD COLUMN IF NOT EXISTS source_type VARCHAR(100) NULL AFTER awarded_by_user_id,
  ADD COLUMN IF NOT EXISTS source_id VARCHAR(100) NULL AFTER source_type;

ALTER TABLE practice_words
  ADD COLUMN IF NOT EXISTS next_review_at DATETIME NULL AFTER mastery_level,
  ADD COLUMN IF NOT EXISTS last_reviewed_at DATETIME NULL AFTER next_review_at,
  ADD COLUMN IF NOT EXISTS due_status ENUM('new','due','scheduled','mastered') NOT NULL DEFAULT 'new' AFTER last_reviewed_at;

ALTER TABLE flashcard_reviews
  MODIFY COLUMN rating ENUM('missed','almost','got_it','again','hard','good','easy') NOT NULL,
  ADD COLUMN IF NOT EXISTS previous_review_at DATETIME NULL AFTER reviewed_at;

CREATE INDEX IF NOT EXISTS idx_practice_words_student_due ON practice_words (student_user_id, next_review_at, due_status);
CREATE INDEX IF NOT EXISTS idx_badges_trigger ON badge_definitions (trigger_type, is_active, display_order);

INSERT INTO badge_definitions (badge_key, name_en, name_ar, description_en, description_ar, icon, trigger_type, required_value, display_order, visibility, color_style, is_active)
VALUES
('first_step','First Step','الخطوة الأولى','Complete your first learning activity.','أكمل أول نشاط تعليمي لك.','🎯','activity_count',1,10,'student_parent','primary',1),
('streak_5','5-Day Streak','استمرار 5 أيام','Stay active for 5 days in a row.','استمر في النشاط لمدة 5 أيام متتالية.','🔥','streak_days',5,20,'student_parent','warning',1),
('streak_10','10-Day Streak','استمرار 10 أيام','Stay active for 10 days in a row.','استمر في النشاط لمدة 10 أيام متتالية.','🔥','streak_days',10,30,'student_parent','warning',1),
('streak_30','30-Day Streak','استمرار 30 يوم','Stay active for 30 days in a row.','استمر في النشاط لمدة 30 يومًا متتالية.','🏆','streak_days',30,40,'student_parent','success',1),
('sessions_10','10 Sessions','10 حصص','Complete 10 sessions.','أكمل 10 حصص.','📚','sessions_completed',10,50,'student_parent','info',1),
('sessions_25','25 Sessions','25 حصة','Complete 25 sessions.','أكمل 25 حصة.','🎓','sessions_completed',25,60,'student_parent','success',1),
('vocab_builder','Vocab Builder','باني الكلمات','Master 20 practice words.','أتقن 20 كلمة تدريبية.','🧠','practice_words_mastered',20,70,'student_parent','primary',1),
('speaking_star','Speaking Star','نجم التحدث','Submit 5 speaking scenarios.','سلّم 5 مواقف كلامية.','🎤','scenarios_submitted',5,80,'student_parent','purple',1),
('perfect_week','Perfect Week','أسبوع مثالي','Complete 7 activities in a week.','أكمل 7 أنشطة خلال أسبوع.','⭐','activity_count',7,90,'student_parent','gold',1),
('first_level_check','First Level Check Completed','أول اختبار مستوى','Complete your first level check.','أكمل أول اختبار مستوى.','✅','level_check_completed',1,100,'student_parent','success',1)
ON DUPLICATE KEY UPDATE
  name_en = VALUES(name_en),
  name_ar = VALUES(name_ar),
  description_en = VALUES(description_en),
  description_ar = VALUES(description_ar),
  icon = VALUES(icon),
  trigger_type = VALUES(trigger_type),
  required_value = VALUES(required_value),
  display_order = VALUES(display_order),
  visibility = VALUES(visibility),
  color_style = VALUES(color_style);

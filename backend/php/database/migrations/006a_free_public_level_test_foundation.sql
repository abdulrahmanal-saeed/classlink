-- Phase 6A V2: Free Public Level Tests, Random Question Bank, Audio Bank, and Owner Control
-- MySQL/MariaDB foundation. This is separate from the paid post-payment level check.

CREATE TABLE IF NOT EXISTS free_level_test_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(150) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  value_type ENUM('string','number','boolean','json','text') NOT NULL DEFAULT 'string',
  updated_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_free_level_settings_user FOREIGN KEY (updated_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_applicants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  applicant_type ENUM('new_applicant','existing_student') NOT NULL DEFAULT 'new_applicant',
  existing_student_code VARCHAR(80) NULL,
  full_name VARCHAR(190) NOT NULL,
  whatsapp VARCHAR(80) NOT NULL,
  email VARCHAR(190) NULL,
  age INT UNSIGNED NULL,
  country VARCHAR(100) NULL,
  device_id VARCHAR(120) NULL,
  ip_hash CHAR(64) NULL,
  user_agent_hash CHAR(64) NULL,
  status ENUM('registered','started','submitted','reviewed','converted') NOT NULL DEFAULT 'registered',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_free_applicant_whatsapp (whatsapp),
  INDEX idx_free_applicant_device (device_id),
  INDEX idx_free_applicant_student_code (existing_student_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_listening_scripts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  level ENUM('A2','B1','B2','C1','C2') NOT NULL,
  script_number INT UNSIGNED NOT NULL,
  audio_url VARCHAR(255) NOT NULL,
  title VARCHAR(190) NOT NULL,
  topic VARCHAR(190) NULL,
  dialect_style VARCHAR(100) NULL,
  script_text TEXT NULL,
  notes TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_free_listening_level_script (level, script_number),
  INDEX idx_free_listening_active_level (is_active, level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_listening_questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  script_id INT UNSIGNED NOT NULL,
  question_text TEXT NOT NULL,
  option_a VARCHAR(255) NOT NULL,
  option_b VARCHAR(255) NOT NULL,
  option_c VARCHAR(255) NOT NULL,
  option_d VARCHAR(255) NOT NULL,
  correct_option ENUM('A','B','C','D') NOT NULL,
  points DECIMAL(6,2) NOT NULL DEFAULT 1.00,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT fk_free_listening_questions_script FOREIGN KEY (script_id) REFERENCES free_level_test_listening_scripts(id) ON DELETE CASCADE,
  INDEX idx_free_listening_questions_script (script_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_reading_texts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bank_type ENUM('full','quick','shared') NOT NULL DEFAULT 'full',
  level ENUM('A1','A2','B1','B2','C1','C2') NOT NULL,
  text_number INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  passage_text LONGTEXT NOT NULL,
  topic VARCHAR(190) NULL,
  dialect_style VARCHAR(100) NULL,
  notes TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_free_reading_active_level (bank_type, is_active, level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_reading_questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reading_text_id INT UNSIGNED NOT NULL,
  question_text TEXT NOT NULL,
  option_a VARCHAR(255) NOT NULL,
  option_b VARCHAR(255) NOT NULL,
  option_c VARCHAR(255) NOT NULL,
  option_d VARCHAR(255) NOT NULL,
  correct_option ENUM('A','B','C','D') NOT NULL,
  points DECIMAL(6,2) NOT NULL DEFAULT 1.00,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT fk_free_reading_questions_text FOREIGN KEY (reading_text_id) REFERENCES free_level_test_reading_texts(id) ON DELETE CASCADE,
  INDEX idx_free_reading_questions_text (reading_text_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_writing_prompts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  task_type ENUM('task1','task2') NOT NULL,
  level ENUM('all','B1','B2','C1','C2') NOT NULL DEFAULT 'all',
  title VARCHAR(190) NOT NULL,
  prompt_text TEXT NOT NULL,
  word_min INT UNSIGNED NULL,
  word_max INT UNSIGNED NULL,
  instructions TEXT NULL,
  diagnostic_criteria TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_free_writing_active (task_type, level, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_speaking_prompts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  phase ENUM('warm_up','description','discussion','abstract_argument') NOT NULL,
  target_level VARCHAR(50) NOT NULL,
  title VARCHAR(190) NOT NULL,
  prompt_text TEXT NOT NULL,
  image_url VARCHAR(255) NULL,
  evaluation_notes TEXT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_free_speaking_active (phase, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_token CHAR(64) NOT NULL UNIQUE,
  test_type ENUM('quick','full') NOT NULL,
  applicant_id INT UNSIGNED NULL,
  device_id VARCHAR(120) NULL,
  whatsapp VARCHAR(80) NULL,
  ip_hash CHAR(64) NULL,
  user_agent_hash CHAR(64) NULL,
  current_step ENUM('quick','listening','reading','writing_speaking','submitted','reviewed') NOT NULL DEFAULT 'listening',
  status ENUM('started','submitted','reviewed','abandoned') NOT NULL DEFAULT 'started',
  snapshot_json JSON NOT NULL,
  generated_with_warnings TINYINT(1) NOT NULL DEFAULT 0,
  warnings_json JSON NULL,
  listening_score DECIMAL(6,2) NULL,
  reading_score DECIMAL(6,2) NULL,
  listening_estimated_level VARCHAR(50) NULL,
  reading_estimated_level VARCHAR(50) NULL,
  auto_estimated_level VARCHAR(50) NULL,
  writing_target_level VARCHAR(50) NULL,
  preliminary_level VARCHAR(50) NULL,
  final_level VARCHAR(50) NULL,
  teacher_notes TEXT NULL,
  reviewed_by_user_id INT UNSIGNED NULL,
  submitted_at DATETIME NULL,
  reviewed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_free_attempt_applicant FOREIGN KEY (applicant_id) REFERENCES free_level_test_applicants(id) ON DELETE SET NULL,
  CONSTRAINT fk_free_attempt_reviewer FOREIGN KEY (reviewed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_free_attempt_applicant (applicant_id),
  INDEX idx_free_attempt_device (device_id),
  INDEX idx_free_attempt_status (test_type, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_answers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT UNSIGNED NOT NULL,
  section_key VARCHAR(80) NOT NULL,
  source_type ENUM('listening','reading','writing','speaking','quick') NOT NULL,
  source_id INT UNSIGNED NULL,
  question_id INT UNSIGNED NULL,
  question_text TEXT NULL,
  answer_text LONGTEXT NULL,
  selected_option VARCHAR(10) NULL,
  correct_option VARCHAR(10) NULL,
  is_correct TINYINT(1) NULL,
  points DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  score DECIMAL(6,2) NULL,
  metadata JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_free_answers_attempt FOREIGN KEY (attempt_id) REFERENCES free_level_test_attempts(id) ON DELETE CASCADE,
  INDEX idx_free_answers_attempt (attempt_id, section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_uploads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT UNSIGNED NOT NULL,
  purpose VARCHAR(80) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  original_filename VARCHAR(255) NULL,
  mime_type VARCHAR(120) NULL,
  size_bytes INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_free_uploads_attempt FOREIGN KEY (attempt_id) REFERENCES free_level_test_attempts(id) ON DELETE CASCADE,
  INDEX idx_free_uploads_attempt (attempt_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS free_level_test_manual_reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT UNSIGNED NOT NULL UNIQUE,
  writing_score DECIMAL(6,2) NULL,
  writing_level VARCHAR(50) NULL,
  writing_feedback TEXT NULL,
  speaking_fluency DECIMAL(6,2) NULL,
  speaking_grammar DECIMAL(6,2) NULL,
  speaking_vocabulary DECIMAL(6,2) NULL,
  speaking_pronunciation DECIMAL(6,2) NULL,
  speaking_depth DECIMAL(6,2) NULL,
  speaking_total DECIMAL(6,2) NULL,
  speaking_level VARCHAR(50) NULL,
  speaking_feedback TEXT NULL,
  final_level VARCHAR(50) NULL,
  next_step_notes TEXT NULL,
  reviewed_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_free_manual_attempt FOREIGN KEY (attempt_id) REFERENCES free_level_test_attempts(id) ON DELETE CASCADE,
  CONSTRAINT fk_free_manual_reviewer FOREIGN KEY (reviewed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

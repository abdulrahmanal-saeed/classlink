-- Phase 7: Owner Approval, Student/Parent Account Creation, and Login Details
-- Run after Phase 6 migrations.

ALTER TABLE student_intake_forms
  ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER recommended_first_lesson,
  ADD COLUMN IF NOT EXISTS approved_by_user_id INT UNSIGNED NULL AFTER approved_at,
  ADD COLUMN IF NOT EXISTS created_student_user_id INT UNSIGNED NULL AFTER approved_by_user_id,
  ADD COLUMN IF NOT EXISTS created_parent_user_id INT UNSIGNED NULL AFTER created_student_user_id,
  ADD COLUMN IF NOT EXISTS approval_note TEXT NULL AFTER created_parent_user_id;

ALTER TABLE purchases
  ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER owner_review_status,
  ADD COLUMN IF NOT EXISTS approved_by_user_id INT UNSIGNED NULL AFTER approved_at,
  ADD COLUMN IF NOT EXISTS created_student_user_id INT UNSIGNED NULL AFTER approved_by_user_id,
  ADD COLUMN IF NOT EXISTS created_parent_user_id INT UNSIGNED NULL AFTER created_student_user_id;

CREATE TABLE IF NOT EXISTS login_detail_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  related_student_user_id INT UNSIGNED NULL,
  related_parent_user_id INT UNSIGNED NULL,
  intake_form_id INT UNSIGNED NULL,
  purchase_id INT UNSIGNED NULL,
  delivery_channel ENUM('email', 'whatsapp', 'manual_log') NOT NULL DEFAULT 'manual_log',
  recipient VARCHAR(190) NOT NULL,
  subject VARCHAR(190) NOT NULL,
  message_body TEXT NOT NULL,
  temporary_password VARCHAR(120) NULL,
  status ENUM('logged', 'sent', 'failed') NOT NULL DEFAULT 'logged',
  created_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_login_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_login_logs_student FOREIGN KEY (related_student_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_login_logs_parent FOREIGN KEY (related_parent_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_login_logs_intake FOREIGN KEY (intake_form_id) REFERENCES student_intake_forms(id) ON DELETE SET NULL,
  CONSTRAINT fk_login_logs_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL,
  CONSTRAINT fk_login_logs_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_login_logs_user (user_id),
  INDEX idx_login_logs_intake (intake_form_id),
  INDEX idx_login_logs_purchase (purchase_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS onboarding_account_links (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intake_form_id INT UNSIGNED NOT NULL,
  purchase_id INT UNSIGNED NULL,
  learner_type ENUM('adult', 'child') NOT NULL,
  student_user_id INT UNSIGNED NOT NULL,
  parent_user_id INT UNSIGNED NULL,
  lesson_package_id INT UNSIGNED NULL,
  status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  created_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_onboarding_links_intake FOREIGN KEY (intake_form_id) REFERENCES student_intake_forms(id) ON DELETE CASCADE,
  CONSTRAINT fk_onboarding_links_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL,
  CONSTRAINT fk_onboarding_links_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_onboarding_links_parent FOREIGN KEY (parent_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_onboarding_links_package FOREIGN KEY (lesson_package_id) REFERENCES lesson_packages(id) ON DELETE SET NULL,
  CONSTRAINT fk_onboarding_links_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE KEY uniq_onboarding_intake_link (intake_form_id),
  INDEX idx_onboarding_links_student (student_user_id),
  INDEX idx_onboarding_links_parent (parent_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO email_templates (template_key, subject_en, subject_ar, body_en, body_ar, is_active)
VALUES
('student_welcome_login', 'Welcome to Habiba Nabil Arabic Academy', 'مرحبًا بك في أكاديمية حبيبة نبيل', 'Welcome {{name}}. Your login email is {{email}} and your temporary password is {{temporary_password}}. Please log in and change your password.', 'مرحبًا {{name}}. بريد الدخول هو {{email}} وكلمة المرور المؤقتة هي {{temporary_password}}. من فضلك سجّل الدخول وغيّر كلمة المرور.', 1),
('parent_welcome_login', 'Welcome to Habiba Nabil Arabic Academy - Parent Access', 'مرحبًا بك في أكاديمية حبيبة نبيل - حساب ولي الأمر', 'Welcome {{name}}. Your parent login email is {{email}} and your temporary password is {{temporary_password}}. You can view your child profile after login.', 'مرحبًا {{name}}. بريد دخول ولي الأمر هو {{email}} وكلمة المرور المؤقتة هي {{temporary_password}}. يمكنك متابعة ملف الطفل بعد تسجيل الدخول.', 1)
ON DUPLICATE KEY UPDATE template_key = VALUES(template_key);

INSERT INTO whatsapp_templates (template_key, body_en, body_ar, is_active)
VALUES
('student_welcome_login', 'Welcome {{name}}. Your login email: {{email}}. Temporary password: {{temporary_password}}.', 'مرحبًا {{name}}. بريد الدخول: {{email}}. كلمة المرور المؤقتة: {{temporary_password}}.', 1),
('parent_welcome_login', 'Welcome {{name}}. Parent login email: {{email}}. Temporary password: {{temporary_password}}.', 'مرحبًا {{name}}. بريد دخول ولي الأمر: {{email}}. كلمة المرور المؤقتة: {{temporary_password}}.', 1)
ON DUPLICATE KEY UPDATE template_key = VALUES(template_key);

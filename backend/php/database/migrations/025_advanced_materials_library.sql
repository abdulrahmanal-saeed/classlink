-- Phase 25: Advanced Materials Library, Uploads, Viewers, and Student Access
-- MySQL implementation. Supabase/PostgreSQL is intentionally not used.
-- Files are stored on Hostinger/local storage; DB stores metadata only.

CREATE TABLE IF NOT EXISTS material_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  description TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_material_category_name (name),
  INDEX idx_material_categories_active_sort (active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_materials (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  description TEXT NULL,
  material_type ENUM('video_upload','external_video','audio_upload','pdf','powerpoint','document','image','external_link','text_article','html_file','mixed_page') NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  level ENUM('A0','A1','A2','B1','B2','C1','C2','mixed','not_set') NOT NULL DEFAULT 'not_set',
  tags TEXT NULL,
  material_language ENUM('ar','en','both') NOT NULL DEFAULT 'both',
  estimated_study_minutes INT UNSIGNED NULL,
  status ENUM('draft','published','hidden','archived') NOT NULL DEFAULT 'draft',
  file_url VARCHAR(255) NULL,
  file_path VARCHAR(255) NULL,
  external_url VARCHAR(255) NULL,
  html_sandbox_mode ENUM('sandboxed_iframe','download_only') NULL,
  thumbnail_url VARCHAR(255) NULL,
  allow_download TINYINT(1) NOT NULL DEFAULT 1,
  text_content LONGTEXT NULL,
  teacher_notes TEXT NULL,
  related_entity_type VARCHAR(80) NULL,
  related_entity_id VARCHAR(80) NULL,
  created_by_user_id INT UNSIGNED NULL,
  archived_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_materials_status_type (status, material_type),
  INDEX idx_materials_category (category_id, status),
  INDEX idx_materials_level_language (level, material_language),
  CONSTRAINT fk_materials_category FOREIGN KEY (category_id) REFERENCES material_categories(id) ON DELETE SET NULL,
  CONSTRAINT fk_materials_creator FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS material_files (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  material_id BIGINT UNSIGNED NOT NULL,
  file_url VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NULL,
  original_filename VARCHAR(255) NULL,
  saved_filename VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NOT NULL,
  file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
  storage_driver VARCHAR(40) NOT NULL DEFAULT 'local',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_material_files_material (material_id),
  CONSTRAINT fk_material_files_material FOREIGN KEY (material_id) REFERENCES course_materials(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS material_assignments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  material_id BIGINT UNSIGNED NOT NULL,
  student_user_id INT UNSIGNED NOT NULL,
  assigned_by_user_id INT UNSIGNED NULL,
  assigned_at DATETIME NOT NULL,
  visible TINYINT(1) NOT NULL DEFAULT 1,
  notes TEXT NULL,
  due_date DATE NULL,
  required TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_material_student (material_id, student_user_id),
  INDEX idx_material_assignments_student_visible (student_user_id, visible, assigned_at),
  CONSTRAINT fk_material_assignments_material FOREIGN KEY (material_id) REFERENCES course_materials(id) ON DELETE CASCADE,
  CONSTRAINT fk_material_assignments_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_material_assignments_owner FOREIGN KEY (assigned_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS material_progress (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  material_id BIGINT UNSIGNED NOT NULL,
  student_user_id INT UNSIGNED NOT NULL,
  viewed_at DATETIME NULL,
  last_opened_at DATETIME NULL,
  completed_at DATETIME NULL,
  download_count INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('assigned','viewed','completed') NOT NULL DEFAULT 'assigned',
  completion_source ENUM('student_marked','owner_marked','auto') NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_material_progress (material_id, student_user_id),
  INDEX idx_material_progress_student_status (student_user_id, status),
  CONSTRAINT fk_material_progress_material FOREIGN KEY (material_id) REFERENCES course_materials(id) ON DELETE CASCADE,
  CONSTRAINT fk_material_progress_student FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO material_categories (name, description, sort_order, active) VALUES
('Lesson slides','Slides used during or after lessons',10,1),
('Reading practice','Reading practice materials',20,1),
('Listening practice','Listening and audio practice',30,1),
('Speaking support','Speaking prompts and support',40,1),
('Writing practice','Writing exercises and support',50,1),
('Vocabulary','Vocabulary sheets and flashcard support',60,1),
('Grammar support','Practical grammar support',70,1),
('Child literacy','Worksheets and literacy support for children',80,1),
('Work Arabic','Arabic for workplace use',90,1),
('Emirati dialect','Emirati Arabic materials',100,1),
('Egyptian dialect','Egyptian Arabic materials',110,1),
('Homework support','Materials linked to homework',120,1),
('Review material','Review and test preparation',130,1)
ON DUPLICATE KEY UPDATE description=VALUES(description), sort_order=VALUES(sort_order), active=VALUES(active);

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public) VALUES
('materials_enabled','1','materials','boolean',0),
('materials_upload_storage_driver','local','materials','string',0),
('materials_upload_base_path','web/public/uploads/materials','materials','string',0),
('materials_public_file_base_url','','materials','string',0),
('materials_allow_html_upload','1','materials','boolean',0),
('materials_html_default_mode','download_only','materials','string',0),
('materials_allow_student_complete','1','materials','boolean',0),
('materials_allow_download_default','1','materials','boolean',0),
('materials_max_video_mb','250','materials','number',0),
('materials_max_audio_mb','50','materials','number',0),
('materials_max_document_mb','80','materials','number',0),
('materials_max_image_mb','20','materials','number',0),
('materials_owner_assign_all_enabled','0','materials','boolean',0)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type), is_public=VALUES(is_public);

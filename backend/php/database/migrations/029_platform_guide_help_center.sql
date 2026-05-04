-- Phase 29: Platform Guide, Help Center, and Role-Based Onboarding
-- Adds role-based help articles, onboarding checklist state, and product tour state.

CREATE TABLE IF NOT EXISTS help_articles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  role ENUM('public','owner_teacher','student','parent','academy_partner','media_buyer') NOT NULL DEFAULT 'public',
  category VARCHAR(120) NOT NULL DEFAULT 'General',
  content LONGTEXT NOT NULL,
  video_url VARCHAR(255) NULL,
  language ENUM('ar','en','both') NOT NULL DEFAULT 'both',
  status ENUM('draft','published') NOT NULL DEFAULT 'published',
  sort_order INT NOT NULL DEFAULT 0,
  created_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_help_slug_role_lang (slug, role, language),
  INDEX idx_help_role_status_sort (role, status, sort_order),
  INDEX idx_help_category (category),
  CONSTRAINT fk_help_article_owner FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_onboarding_progress (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  role VARCHAR(80) NOT NULL,
  checklist_key VARCHAR(120) NOT NULL,
  completed TINYINT(1) NOT NULL DEFAULT 0,
  completed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_checklist (user_id, checklist_key),
  INDEX idx_user_onboarding_role (role, completed),
  CONSTRAINT fk_user_onboarding_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_tour_progress (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  role VARCHAR(80) NOT NULL,
  tour_key VARCHAR(120) NOT NULL,
  completed TINYINT(1) NOT NULL DEFAULT 0,
  skipped TINYINT(1) NOT NULL DEFAULT 0,
  completed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_tour (user_id, tour_key),
  INDEX idx_user_tour_role (role, completed, skipped),
  CONSTRAINT fk_user_tour_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public) VALUES
('help_center_enabled','1','help_center','boolean',1),
('guided_tours_enabled','1','help_center','boolean',0),
('onboarding_checklists_enabled','1','help_center','boolean',0),
('help_whatsapp_url','https://wa.me/','help_center','string',1)
ON DUPLICATE KEY UPDATE setting_group=VALUES(setting_group), value_type=VALUES(value_type), is_public=VALUES(is_public);

INSERT INTO help_articles (title, slug, role, category, content, language, status, sort_order) VALUES
('Platform Overview','platform-overview','public','Quick Start','This platform helps learners, parents, academy partners, media buyers, and the Owner manage the full Arabic learning journey in one place. Use the role-specific guides to understand your dashboard and next steps.','en','published',1),
('Owner Quick Start','owner-quick-start','owner_teacher','Quick Start','Start by reviewing payments, onboarding submissions, level checks, bookings, notifications, and settings. Use the dashboard to see what needs attention today, then use quick actions to approve students, create homework, review submissions, and manage materials.','en','published',1),
('Student Quick Start','student-quick-start','student','Quick Start','Your student dashboard shows upcoming lessons, homework, scenarios, materials, feedback, flashcards, progress, badges, and notifications. Start by completing your profile, checking your next lesson, and opening assigned homework or materials.','en','published',1),
('Parent Quick Start','parent-quick-start','parent','Quick Start','The parent portal helps you follow your child’s Arabic learning journey. You can view upcoming lessons, homework status, teacher notes, package balance, progress, materials, and contact shortcuts.','en','published',1),
('Academy Partner Quick Start','academy-quick-start','academy_partner','Quick Start','Use the academy partner portal to submit student briefs and track their status. The Owner reviews each brief and may convert it into onboarding or a student profile.','en','published',1),
('Media Buyer Quick Start','media-buyer-quick-start','media_buyer','Quick Start','Your media buyer dashboard shows tracking links, campaign performance, attributed orders, revenue, commissions, and payouts. Use approved tracking links and UTM parameters. Only paid orders count for commission.','en','published',1),
('How Homework Works','how-homework-works','student','Homework','When your teacher assigns homework, it appears in your homework page and notifications. Complete each section, submit your answers, then return later to view correction and teacher feedback.','en','published',10),
('How Parent Progress Works','how-parent-progress-works','parent','Progress','Progress appears after lessons, homework, reviews, materials, and teacher notes. If there is no progress yet, it usually means the learning activity has not started or has not been reviewed yet.','en','published',10),
('How Tracking Links Work','how-tracking-links-work','media_buyer','Tracking','Tracking links include your partner code and UTM data. When visitors use your link and later purchase, the platform can attribute the order to your media buyer profile.','en','published',10),
('دليل سريع للمنصة','platform-overview-ar','public','البداية السريعة','هذه المنصة تساعد الطالب وولي الأمر وشريك الأكاديمية وشريك التسويق وصاحب المنصة على متابعة رحلة تعلم العربية من مكان واحد. استخدم الدليل المناسب لدورك لمعرفة الخطوات التالية.','ar','published',1),
('دليل المالك السريع','owner-quick-start-ar','owner_teacher','البداية السريعة','ابدأ بمراجعة المدفوعات وطلبات الانضمام واختبارات المستوى والحجوزات والإشعارات والإعدادات. استخدم لوحة التحكم لمعرفة ما يحتاج انتباهك اليوم.','ar','published',1),
('دليل الطالب السريع','student-quick-start-ar','student','البداية السريعة','لوحة الطالب تعرض الحصص القادمة والواجبات والمواقف الكلامية والمواد التعليمية والنتائج والكلمات والشارات والإشعارات. ابدأ بإكمال ملفك ومراجعة الحصة القادمة.','ar','published',1),
('دليل ولي الأمر السريع','parent-quick-start-ar','parent','البداية السريعة','بوابة ولي الأمر تساعدك على متابعة تقدم الطفل والحصص القادمة وحالة الواجبات وملاحظات المعلم ورصيد الباقة وطرق التواصل.','ar','published',1),
('دليل شريك الأكاديمية','academy-quick-start-ar','academy_partner','البداية السريعة','استخدم بوابة شريك الأكاديمية لإرسال بيانات الطالب ومتابعة حالتها حتى يراجعها صاحب المنصة.','ar','published',1),
('دليل شريك التسويق','media-buyer-quick-start-ar','media_buyer','البداية السريعة','لوحة شريك التسويق تعرض روابط التتبع والطلبات المنسوبة والإيرادات والعمولات والمدفوعات. العمولة تُحسب على الطلبات المدفوعة فقط.','ar','published',1)
ON DUPLICATE KEY UPDATE title=VALUES(title), content=VALUES(content), status=VALUES(status), sort_order=VALUES(sort_order);

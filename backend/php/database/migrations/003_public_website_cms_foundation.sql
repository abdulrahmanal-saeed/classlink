-- Phase 3: Public Website, Pricing, Legal Pages, and CMS Basics
-- Run after Phase 2 migration.

ALTER TABLE articles
  ADD COLUMN IF NOT EXISTS seo_title VARCHAR(190) NULL AFTER slug,
  ADD COLUMN IF NOT EXISTS seo_description VARCHAR(255) NULL AFTER seo_title,
  ADD COLUMN IF NOT EXISTS cover_image_url VARCHAR(255) NULL AFTER seo_description;

ALTER TABLE videos
  ADD COLUMN IF NOT EXISTS slug VARCHAR(190) NULL AFTER title_ar,
  ADD COLUMN IF NOT EXISTS description_en TEXT NULL AFTER slug,
  ADD COLUMN IF NOT EXISTS description_ar TEXT NULL AFTER description_en,
  ADD COLUMN IF NOT EXISTS seo_title VARCHAR(190) NULL AFTER description_ar,
  ADD COLUMN IF NOT EXISTS seo_description VARCHAR(255) NULL AFTER seo_title,
  ADD COLUMN IF NOT EXISTS thumbnail_url VARCHAR(255) NULL AFTER seo_description;

-- MariaDB/MySQL note: if your database does not allow multiple NULL values in UNIQUE indexes as expected,
-- keep video slugs filled for all published videos.
CREATE UNIQUE INDEX IF NOT EXISTS uniq_videos_slug ON videos (slug);

ALTER TABLE testimonials
  ADD COLUMN IF NOT EXISTS source VARCHAR(100) NULL AFTER rating,
  ADD COLUMN IF NOT EXISTS consent_to_publish TINYINT(1) NOT NULL DEFAULT 0 AFTER source;

CREATE TABLE IF NOT EXISTS public_page_contents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_key VARCHAR(120) NOT NULL UNIQUE,
  title_en VARCHAR(190) NOT NULL,
  title_ar VARCHAR(190) NULL,
  body_en LONGTEXT NULL,
  body_ar LONGTEXT NULL,
  seo_title VARCHAR(190) NULL,
  seo_description VARCHAR(255) NULL,
  is_indexable TINYINT(1) NOT NULL DEFAULT 1,
  updated_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_public_page_updated_by FOREIGN KEY (updated_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO public_page_contents (page_key, title_en, title_ar, body_en, body_ar, seo_title, seo_description, is_indexable)
VALUES
('terms', 'Terms and Conditions', 'الشروط والأحكام', 'These terms are a placeholder and should be reviewed by a lawyer before real use.', 'هذه الشروط نص مبدئي ويجب مراجعتها قانونيًا قبل الاستخدام الحقيقي.', 'Terms and Conditions | Habiba Nabil Arabic Academy', 'Read the terms and conditions for Habiba Nabil Arabic Academy.', 1),
('privacy', 'Privacy Policy', 'سياسة الخصوصية', 'This privacy policy is a placeholder and should be reviewed by a lawyer before real use.', 'سياسة الخصوصية نص مبدئي ويجب مراجعتها قانونيًا قبل الاستخدام الحقيقي.', 'Privacy Policy | Habiba Nabil Arabic Academy', 'Read the privacy policy for Habiba Nabil Arabic Academy.', 1),
('refund', 'Refund Policy', 'سياسة الاسترداد', 'This refund policy is a placeholder and should be reviewed by a lawyer before real use.', 'سياسة الاسترداد نص مبدئي ويجب مراجعتها قانونيًا قبل الاستخدام الحقيقي.', 'Refund Policy | Habiba Nabil Arabic Academy', 'Read the refund policy for Habiba Nabil Arabic Academy.', 1)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

INSERT INTO plans (name_en, name_ar, description_en, description_ar, price_amount, currency, included_sessions, session_minutes, is_active, sort_order)
VALUES
('Single Session', 'حصة واحدة', 'Launch price: one 90-minute personalized Arabic lesson. Regular price AED 120.', 'سعر الإطلاق: حصة عربية شخصية لمدة 90 دقيقة. السعر العادي 120 درهم.', 80.00, 'AED', 1, 90, 1, 10),
('Monthly Plan', 'الخطة الشهرية', 'Launch price: 8 sessions / 12 hours. Regular price AED 960.', 'سعر الإطلاق: 8 حصص / 12 ساعة. السعر العادي 960 درهم.', 640.00, 'AED', 8, 90, 1, 20),
('30-Hour Bundle', 'باقة 30 ساعة', 'Launch price: 20 sessions / 30 hours. Never expires. Regular price AED 2400.', 'سعر الإطلاق: 20 حصة / 30 ساعة. لا تنتهي. السعر العادي 2400 درهم.', 1600.00, 'AED', 20, 90, 1, 30);

-- Phase 17: Email, WhatsApp Templates, Notifications, and Communication Center
-- Run after Phase 16.

ALTER TABLE notifications
  ADD COLUMN IF NOT EXISTS notification_type VARCHAR(100) NULL AFTER body,
  ADD COLUMN IF NOT EXISTS target_role ENUM('student','parent','owner','academy_partner') NULL AFTER notification_type,
  ADD COLUMN IF NOT EXISTS related_entity_type ENUM('homework','scenario','review','material','booking','payment','badge','level_check','weekly_summary','general') NULL AFTER target_role,
  ADD COLUMN IF NOT EXISTS related_entity_id VARCHAR(100) NULL AFTER related_entity_type,
  ADD COLUMN IF NOT EXISTS action_label VARCHAR(120) NULL AFTER related_entity_id,
  ADD COLUMN IF NOT EXISTS action_url VARCHAR(255) NULL AFTER action_label;

CREATE INDEX IF NOT EXISTS idx_notifications_role_status ON notifications (target_role, status, created_at);
CREATE INDEX IF NOT EXISTS idx_notifications_user_related ON notifications (user_id, related_entity_type, related_entity_id);

CREATE TABLE IF NOT EXISTS email_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  recipient_email VARCHAR(190) NOT NULL,
  recipient_name VARCHAR(190) NULL,
  template_key VARCHAR(120) NULL,
  subject VARCHAR(255) NOT NULL,
  body LONGTEXT NULL,
  related_entity_type VARCHAR(100) NULL,
  related_entity_id VARCHAR(100) NULL,
  provider VARCHAR(100) NULL,
  provider_message_id VARCHAR(190) NULL,
  status ENUM('logged','queued','sent','failed') NOT NULL DEFAULT 'logged',
  error_message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sent_at DATETIME NULL,
  INDEX idx_email_logs_recipient (recipient_email),
  INDEX idx_email_logs_related (related_entity_type, related_entity_id),
  CONSTRAINT fk_email_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE email_templates
  ADD COLUMN IF NOT EXISTS name VARCHAR(190) NULL AFTER template_key,
  ADD COLUMN IF NOT EXISTS variables TEXT NULL AFTER body_ar,
  ADD COLUMN IF NOT EXISTS sort_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER variables;

ALTER TABLE whatsapp_templates
  ADD COLUMN IF NOT EXISTS name VARCHAR(190) NULL AFTER template_key,
  ADD COLUMN IF NOT EXISTS variables TEXT NULL AFTER body_ar,
  ADD COLUMN IF NOT EXISTS sort_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER variables;

INSERT INTO email_templates (template_key, name, subject_en, subject_ar, body_en, body_ar, variables, sort_order, is_active)
VALUES
('payment_confirmation','Payment Confirmation','Payment received — Habiba Nabil Arabic Academy','تم استلام الدفع — أكاديمية حبيبة نبيل','Hi [Name],\n\nThank you. Your payment for [Plan Name] has been received.\n\nNext step: [Student Form Link]\n\nHabiba Nabil Arabic Academy','مرحباً [Name]،\n\nشكراً لك. تم استلام الدفع الخاص بـ [Plan Name].\n\nالخطوة التالية: [Student Form Link]\n\nأكاديمية حبيبة نبيل','[Name], [Plan Name], [Student Form Link]',10,1),
('student_form_received','Student Form Received','Student form received','تم استلام نموذج الطالب','Hi [Name],\n\nWe received your student form. The teacher will review it and guide you to the next step.\n\n[Booking Link]','مرحباً [Name]،\n\nتم استلام نموذج الطالب. سيقوم المعلم بمراجعته وتوجيهك للخطوة التالية.\n\n[Booking Link]','[Name], [Booking Link]',20,1),
('lesson_time_confirmation','Lesson Time Confirmation','Your Arabic lesson is confirmed','تم تأكيد موعد حصة العربي','Hi [Name],\n\nYour lesson is confirmed on [Lesson Date] at [Lesson Time].\nMeeting link: [Meeting Link]\n\nSee you soon!','مرحباً [Name]،\n\nتم تأكيد الحصة يوم [Lesson Date] الساعة [Lesson Time].\nرابط الحصة: [Meeting Link]\n\nنراك قريباً!','[Name], [Lesson Date], [Lesson Time], [Meeting Link]',30,1),
('level_check_reminder','Level Check Reminder','Reminder: complete your Arabic level check','تذكير: أكمل اختبار تحديد المستوى','Hi [Name],\n\nPlease complete your level check here: [Level Check Link].\nThis helps us prepare your first lesson properly.','مرحباً [Name]،\n\nبرجاء إكمال اختبار تحديد المستوى من هنا: [Level Check Link].\nهذا يساعدنا على تجهيز أول حصة بشكل مناسب.','[Name], [Level Check Link]',40,1),
('after_first_lesson','After First Lesson','Great start in your first Arabic lesson','بداية رائعة في أول حصة عربي','Hi [Name],\n\nGreat work in your first lesson. Your next focus is: [Next Focus].\nHomework: [Homework Link]','مرحباً [Name]،\n\nأداء رائع في أول حصة. التركيز القادم: [Next Focus].\nالواجب: [Homework Link]','[Name], [Next Focus], [Homework Link]',50,1),
('homework_corrected','Homework Corrected','Your homework has been corrected','تم تصحيح الواجب','Hi [Name],\n\nYour homework has been corrected. View your result here: [Result Link].','مرحباً [Name]،\n\nتم تصحيح الواجب. يمكنك مشاهدة النتيجة من هنا: [Result Link].','[Name], [Result Link]',60,1),
('weekly_summary_ready','Weekly Summary Ready','Your weekly Arabic summary is ready','الملخص الأسبوعي جاهز','Hi [Name],\n\nYour weekly summary is ready.\nFocus for next week: [Next Focus].\n\nOpen: [Summary Link]','مرحباً [Name]،\n\nالملخص الأسبوعي جاهز.\nتركيز الأسبوع القادم: [Next Focus].\n\nافتح من هنا: [Summary Link]','[Name], [Next Focus], [Summary Link]',70,1)
ON DUPLICATE KEY UPDATE name=VALUES(name), subject_en=VALUES(subject_en), subject_ar=VALUES(subject_ar), body_en=VALUES(body_en), body_ar=VALUES(body_ar), variables=VALUES(variables), sort_order=VALUES(sort_order), is_active=VALUES(is_active);

INSERT INTO whatsapp_templates (template_key, name, body_en, body_ar, variables, sort_order, is_active)
VALUES
('after_payment','After Payment','Hi [Name], thank you for your payment for [Plan Name]. Please complete your student form here: [Student Form Link]','مرحباً [Name]، شكراً على الدفع الخاص بـ [Plan Name]. برجاء إكمال نموذج الطالب من هنا: [Student Form Link]','[Name], [Plan Name], [Student Form Link]',10,1),
('lesson_confirmation','Lesson Confirmation','Hi [Name], your Arabic lesson is confirmed on [Lesson Date] at [Lesson Time]. Link: [Meeting Link]','مرحباً [Name]، تم تأكيد حصة العربي يوم [Lesson Date] الساعة [Lesson Time]. الرابط: [Meeting Link]','[Name], [Lesson Date], [Lesson Time], [Meeting Link]',20,1),
('level_check_received','Level Check Received','Hi [Name], we received your level check. The teacher will review it and send the next step.','مرحباً [Name]، تم استلام اختبار تحديد المستوى. سيقوم المعلم بمراجعته وإرسال الخطوة التالية.','[Name]',30,1),
('homework_published','Homework Published','Hi [Name], new homework is ready: [Homework Link]','مرحباً [Name]، يوجد واجب جديد جاهز: [Homework Link]','[Name], [Homework Link]',40,1),
('scenario_published','Scenario Published','Hi [Name], new speaking scenario is ready: [Scenario Link]','مرحباً [Name]، يوجد موقف كلامي جديد: [Scenario Link]','[Name], [Scenario Link]',50,1),
('review_published','Review Published','Hi [Name], your review/test is ready: [Review Link]','مرحباً [Name]، المراجعة/الاختبار جاهز: [Review Link]','[Name], [Review Link]',60,1),
('reminder_24h','Reminder 24h','Hi [Name], reminder: your Arabic lesson is tomorrow at [Lesson Time]. Link: [Meeting Link]','مرحباً [Name]، تذكير: حصة العربي غداً الساعة [Lesson Time]. الرابط: [Meeting Link]','[Name], [Lesson Time], [Meeting Link]',70,1),
('reminder_1h','Reminder 1h','Hi [Name], your Arabic lesson starts in 1 hour. Link: [Meeting Link]','مرحباً [Name]، حصة العربي تبدأ بعد ساعة. الرابط: [Meeting Link]','[Name], [Meeting Link]',80,1),
('low_credits','Low Credits','Hi [Name], your lesson balance is low: [Remaining Credits] credits left. Renew here: [Payment Link]','مرحباً [Name]، رصيد الحصص منخفض: متبقي [Remaining Credits] حصة. التجديد من هنا: [Payment Link]','[Name], [Remaining Credits], [Payment Link]',90,1)
ON DUPLICATE KEY UPDATE name=VALUES(name), body_en=VALUES(body_en), body_ar=VALUES(body_ar), variables=VALUES(variables), sort_order=VALUES(sort_order), is_active=VALUES(is_active);

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('email_provider_configured','0','communication','boolean',0),
('email_from_name','Habiba Nabil Arabic Academy','communication','string',0),
('email_from_address','hello@mshabibanabil.com','communication','string',0),
('whatsapp_default_country_code','971','communication','string',0)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), setting_group=VALUES(setting_group), value_type=VALUES(value_type);

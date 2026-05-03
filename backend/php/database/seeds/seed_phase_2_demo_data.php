<?php
/**
 * Phase 2 demo data seeder.
 *
 * This creates safe demo records for development/testing only. It does not mark
 * payments as paid automatically; records stay pending/manual-friendly.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/AuditLogger.php';
require_once __DIR__ . '/../../shared/Settings.php';

$pdo = db();
$appEnv = getenv('APP_ENV') ?: 'local';

if (!in_array($appEnv, ['local', 'development'], true)) {
    throw new RuntimeException('Seed data can only run in local/development environment.');
}

$owner = $pdo->query("SELECT id FROM users WHERE role = 'owner_teacher' ORDER BY id ASC LIMIT 1")->fetch();
$student = $pdo->query("SELECT id FROM users WHERE role = 'student' ORDER BY id ASC LIMIT 1")->fetch();
$parent = $pdo->query("SELECT id FROM users WHERE role = 'parent' ORDER BY id ASC LIMIT 1")->fetch();
$academy = $pdo->query("SELECT id FROM users WHERE role = 'academy_partner' ORDER BY id ASC LIMIT 1")->fetch();

if (!$owner || !$student) {
    throw new RuntimeException('Please run Phase 1 demo account seeder first.');
}

$ownerId = (int) $owner['id'];
$studentId = (int) $student['id'];
$parentId = $parent ? (int) $parent['id'] : null;
$academyId = $academy ? (int) $academy['id'] : null;

$pdo->beginTransaction();

try {
    $pdo->prepare("INSERT IGNORE INTO student_profiles (user_id, learner_type, current_level, target_level, learning_goal, preferred_dialect, notes) VALUES (?, 'adult', 'A1', 'B1', 'Speaking confidence', 'MSA + Emirati', 'Demo student profile')")->execute([$studentId]);

    if ($parentId) {
        $pdo->prepare("INSERT IGNORE INTO parent_profiles (user_id, preferred_contact_method, notes) VALUES (?, 'whatsapp', 'Demo parent profile')")->execute([$parentId]);
    }

    if ($academyId) {
        $pdo->prepare("INSERT INTO academy_briefs (academy_partner_user_id, contact_name, contact_email, academy_name, student_age_group, goals, status) VALUES (?, 'Demo Partner', 'academy@demo.com', 'Demo Academy', 'Children and adults', 'Arabic learning partnership demo brief', 'new')")->execute([$academyId]);
    }

    $pdo->prepare("INSERT INTO plans (name_en, name_ar, description_en, description_ar, price_amount, currency, included_sessions, session_minutes, sort_order) VALUES ('Single Session', 'حصة واحدة', 'One 90-minute Arabic session', 'حصة عربية لمدة 90 دقيقة', 80.00, 'AED', 1, 90, 1), ('Monthly Plan', 'الخطة الشهرية', '8 sessions per month', '8 حصص شهريًا', 640.00, 'AED', 8, 90, 2), ('30-Hour Bundle', 'باقة 30 ساعة', '20 sessions / 30 hours', '20 حصة / 30 ساعة', 1600.00, 'AED', 20, 90, 3)")->execute();

    $planId = (int) $pdo->lastInsertId();
    if ($planId > 0) {
        $pdo->prepare("INSERT INTO purchases (user_id, plan_id, status, amount, currency, source) VALUES (?, ?, 'pending', 80.00, 'AED', 'demo')")->execute([$studentId, $planId]);
    }

    $pdo->prepare("INSERT INTO badge_definitions (badge_key, name_en, name_ar, description_en, description_ar, icon) VALUES ('first_login', 'First Login', 'أول دخول', 'Awarded after first login', 'شارة أول دخول للمنصة', 'star') ON DUPLICATE KEY UPDATE name_en = VALUES(name_en)")->execute();

    $pdo->prepare("INSERT INTO email_templates (template_key, subject_en, subject_ar, body_en, body_ar) VALUES ('welcome_student', 'Welcome to Habiba Nabil Arabic Academy', 'مرحبًا بك في أكاديمية حبيبة نبيل', 'Welcome {{name}}', 'مرحبًا {{name}}') ON DUPLICATE KEY UPDATE subject_en = VALUES(subject_en)")->execute();

    $pdo->prepare("INSERT INTO whatsapp_templates (template_key, body_en, body_ar) VALUES ('homework_published', 'New homework is available.', 'تم نشر واجب جديد.') ON DUPLICATE KEY UPDATE body_en = VALUES(body_en)")->execute();

    setting_set('pricing.single_session_price', '80 AED', 'pricing', $ownerId);
    setting_set('upload_limits.audio_mb', '25', 'upload_limits', $ownerId);
    setting_set('lesson_cancellation_policy.hours_before', '12', 'lesson_cancellation_policy', $ownerId);

    audit_log($ownerId, 'phase_2_demo_seeded', 'system', null, ['environment' => $appEnv]);

    $pdo->commit();
    echo "Phase 2 demo data seeded successfully." . PHP_EOL;
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}

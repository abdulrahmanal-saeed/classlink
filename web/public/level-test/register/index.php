<?php
/**
 * /level-test/register
 * New applicant registration for full free placement test.
 */

require_once __DIR__ . '/../../../../backend/php/shared/FreeLevelTest.php';
require_once __DIR__ . '/../../../../web/components/layout/public_layout.php';

flt_seed_defaults();
$errors = [];
$studentCode = trim($_GET['student_code'] ?? '');
$type = $studentCode !== '' ? 'existing_student' : 'new_applicant';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['applicant_type'] ?? 'new_applicant';
    $data = [
        'applicant_type' => $type === 'existing_student' ? 'existing_student' : 'new_applicant',
        'existing_student_code' => trim($_POST['existing_student_code'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'age' => (int) ($_POST['age'] ?? 0),
        'country' => trim($_POST['country'] ?? ''),
    ];

    if ($data['full_name'] === '') $errors[] = 'Full name is required.';
    if ($data['whatsapp'] === '') $errors[] = 'WhatsApp is required.';
    if (!preg_match('/^\+[1-9][0-9]{7,14}$/', $data['whatsapp'])) $errors[] = 'WhatsApp must include country code and must not start with 0. Example: +971501234567';
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($data['applicant_type'] === 'existing_student' && $data['existing_student_code'] === '') $errors[] = 'Student code is required for existing students.';

    if (!$errors) {
        $applicantId = flt_create_applicant($data);
        $snapshot = flt_generate_full_snapshot();
        $attempt = flt_create_attempt('full', $applicantId, $data['whatsapp'], $snapshot, 'listening');
        db()->prepare('UPDATE free_level_test_applicants SET status = "started" WHERE id = :id')->execute([':id' => $applicantId]);
        audit_log(null, 'free_full_level_test_registered', 'free_level_test_applicant', (string) $applicantId, ['attempt_id' => $attempt['id']]);
        header('Location: /level-test/start?token=' . urlencode($attempt['token']));
        exit;
    }
}

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card" style="max-width: 860px; margin:auto;">
      <h1 class="hero-title mb-3">Almost Ready!</h1>
      <p class="hero-subtitle">Just your name and WhatsApp — we’ll send your results and next step there.</p>
      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="status-box h-100">Auto sections checked instantly.</div></div>
        <div class="col-md-4"><div class="status-box h-100">Writing + Speaking reviewed by teacher.</div></div>
        <div class="col-md-4"><div class="status-box h-100">Result and next step within 48h.</div></div>
      </div>

      <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>

      <form method="post" class="row g-3">
        <input type="hidden" name="applicant_type" value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($type === 'existing_student'): ?>
          <div class="col-md-6"><label class="form-label">Student code</label><input class="form-control" name="existing_student_code" value="<?= htmlspecialchars($studentCode, ENT_QUOTES, 'UTF-8') ?>" required></div>
        <?php endif; ?>
        <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="full_name" required></div>
        <div class="col-md-6"><label class="form-label">WhatsApp with country code</label><input class="form-control" name="whatsapp" placeholder="+971501234567" required></div>
        <div class="col-md-6"><label class="form-label">Email optional</label><input class="form-control" type="email" name="email"></div>
        <div class="col-md-3"><label class="form-label">Age optional</label><input class="form-control" type="number" min="1" max="100" name="age"></div>
        <div class="col-md-3"><label class="form-label">Country optional</label><input class="form-control" name="country"></div>
        <div class="col-12"><div class="alert alert-light border">This is free. No paid student account, package, credits, or payment status will be created here.</div></div>
        <div class="col-12"><button class="btn btn-brand btn-lg" type="submit">Start Full Free Placement Test</button></div>
      </form>
    </div>
  </div>
</section>
<?php
render_public_layout('Register for Free Arabic Placement Test | Habiba Nabil Arabic Academy', 'Register for a free Arabic placement test reviewed by the teacher.', ob_get_clean(), true);

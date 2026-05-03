<?php
/**
 * /student-form?ref={checkoutReference}
 *
 * Post-payment onboarding form. It loads checkout data by secure reference,
 * shows conditional adult/child/someone_else fields, then redirects to level check intro.
 */

require_once __DIR__ . '/../../../backend/php/shared/Onboarding.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$reference = onboarding_clean_reference($_GET['ref'] ?? '');
$purchase = $reference ? onboarding_find_checkout($reference) : null;
$errors = [];

if (!$purchase) {
    http_response_code(404);
    $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Checkout reference not found</h1><p class="text-muted">Please use the student form link from your thank-you page.</p><a class="btn btn-brand" href="/pricing">Back to pricing</a></div></div></section>';
    render_public_layout('Student Form Not Found | Habiba Nabil Arabic Academy', 'Student form reference not found.', $content, false);
    exit;
}

$checkoutLearnerType = $purchase['learner_type'] ?: 'adult';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $someoneElseType = $_POST['someone_else_type'] ?? '';
    $learnerType = onboarding_normalize_learner_type($checkoutLearnerType, $someoneElseType);

    $payload = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
        'who_is_learning' => $checkoutLearnerType,
        'someone_else_type' => $someoneElseType,
        'purchased_plan' => $purchase['plan_name'] ?? '',
        'age' => trim($_POST['age'] ?? ''),
        'native_language' => trim($_POST['native_language'] ?? ''),
        'current_arabic_level' => trim($_POST['current_arabic_level'] ?? ''),
        'can_read_arabic' => trim($_POST['can_read_arabic'] ?? ''),
        'can_write_arabic' => trim($_POST['can_write_arabic'] ?? ''),
        'main_goal' => trim($_POST['main_goal'] ?? ''),
        'learning_reason' => trim($_POST['learning_reason'] ?? ''),
        'use_context' => trim($_POST['use_context'] ?? ''),
        'preferred_arabic_type' => trim($_POST['preferred_arabic_type'] ?? ''),
        'biggest_difficulty' => trim($_POST['biggest_difficulty'] ?? ''),
        'difficulty_details' => trim($_POST['difficulty_details'] ?? ''),
        'scheduling_preferences' => trim($_POST['scheduling_preferences'] ?? ''),
        'notes_for_tutor' => trim($_POST['notes_for_tutor'] ?? ''),
        'parent_name' => trim($_POST['parent_name'] ?? ''),
        'child_name' => trim($_POST['child_name'] ?? ''),
        'child_age' => trim($_POST['child_age'] ?? ''),
        'child_native_language' => trim($_POST['child_native_language'] ?? ''),
        'child_speaks_arabic' => trim($_POST['child_speaks_arabic'] ?? ''),
        'child_can_read_arabic' => trim($_POST['child_can_read_arabic'] ?? ''),
        'child_can_write_arabic' => trim($_POST['child_can_write_arabic'] ?? ''),
        'child_learning_goal' => trim($_POST['child_learning_goal'] ?? ''),
        'studied_arabic_before' => trim($_POST['studied_arabic_before'] ?? ''),
        'struggles' => trim($_POST['struggles'] ?? ''),
        'learning_style_notes' => trim($_POST['learning_style_notes'] ?? ''),
    ];

    $errors = onboarding_validate_payload($payload, $learnerType);

    if (!$errors) {
        onboarding_save_form($purchase, $learnerType, $payload);
        header('Location: /level-check-intro?ref=' . urlencode($reference));
        exit;
    }
}

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card">
      <div class="badge text-bg-light border mb-3">Reference: <?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?></div>
      <h1 class="hero-title mb-3">Complete your student form</h1>
      <p class="hero-subtitle">This helps the tutor prepare a personalized first Arabic lesson.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger"><strong>Please fix:</strong><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <form method="post" class="row g-3" id="studentForm">
        <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="full_name" value="<?= htmlspecialchars($purchase['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= htmlspecialchars($purchase['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
        <div class="col-md-6"><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp" value="<?= htmlspecialchars($purchase['whatsapp'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Purchased plan</label><input class="form-control" value="<?= htmlspecialchars($purchase['plan_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly></div>

        <div class="col-12"><div class="status-box"><strong>Who is learning Arabic?</strong><br><?= htmlspecialchars($checkoutLearnerType, ENT_QUOTES, 'UTF-8') ?></div></div>

        <?php if ($checkoutLearnerType === 'someone_else'): ?>
          <div class="col-12"><label class="form-label">Is the learner an adult or child?</label><select class="form-select" name="someone_else_type" id="someoneElseType" required><option value="adult">Adult</option><option value="child">Child</option></select></div>
        <?php else: ?>
          <input type="hidden" id="someoneElseType" value="<?= $checkoutLearnerType === 'child' ? 'child' : 'adult' ?>">
        <?php endif; ?>

        <div id="adultFields" class="row g-3 mt-1">
          <div class="col-12"><h2 class="h4 fw-bold">Adult learner details</h2></div>
          <div class="col-md-4"><label class="form-label">Age</label><input class="form-control adult-required" name="age" type="number" min="1" max="100" value="<?= htmlspecialchars((string) ($purchase['student_age'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
          <div class="col-md-4"><label class="form-label">Native language</label><input class="form-control adult-required" name="native_language"></div>
          <div class="col-md-4"><label class="form-label">Current Arabic level</label><select class="form-select adult-required" name="current_arabic_level"><option value="">Choose...</option><option>Complete beginner</option><option>I know some words</option><option>I understand but cannot speak well</option><option>I can speak simple sentences</option><option>Basic conversations</option></select></div>
          <div class="col-md-6"><label class="form-label">Can read Arabic?</label><select class="form-select adult-required" name="can_read_arabic"><option value="">Choose...</option><option>Yes</option><option>No</option><option>A little</option></select></div>
          <div class="col-md-6"><label class="form-label">Can write Arabic?</label><select class="form-select adult-required" name="can_write_arabic"><option value="">Choose...</option><option>Yes</option><option>No</option><option>A little</option></select></div>
          <div class="col-md-6"><label class="form-label">Main goal</label><input class="form-control adult-required" name="main_goal" value="<?= htmlspecialchars($purchase['main_goal'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
          <div class="col-md-6"><label class="form-label">Preferred Arabic type</label><select class="form-select adult-required" name="preferred_arabic_type"><option value="">Choose...</option><option>Modern Standard Arabic</option><option>Emirati dialect</option><option>Egyptian dialect</option><option>Mix of MSA + dialect</option><option>Not sure</option></select></div>
          <div class="col-md-6"><label class="form-label">Learning reason</label><textarea class="form-control adult-required" name="learning_reason" rows="3"></textarea></div>
          <div class="col-md-6"><label class="form-label">Use context</label><textarea class="form-control adult-required" name="use_context" rows="3" placeholder="Work, daily life, travel, family..."></textarea></div>
          <div class="col-md-6"><label class="form-label">Biggest difficulty</label><input class="form-control adult-required" name="biggest_difficulty"></div>
          <div class="col-md-6"><label class="form-label">Difficulty details</label><input class="form-control adult-required" name="difficulty_details"></div>
        </div>

        <div id="childFields" class="row g-3 mt-1 d-none">
          <div class="col-12"><h2 class="h4 fw-bold">Child learner details</h2></div>
          <div class="col-md-6"><label class="form-label">Parent name</label><input class="form-control child-required" name="parent_name" value="<?= htmlspecialchars($purchase['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
          <div class="col-md-6"><label class="form-label">Child name</label><input class="form-control child-required" name="child_name"></div>
          <div class="col-md-4"><label class="form-label">Child age</label><input class="form-control child-required" name="child_age" type="number" min="1" max="18" value="<?= htmlspecialchars((string) ($purchase['student_age'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
          <div class="col-md-4"><label class="form-label">Child native language</label><input class="form-control child-required" name="child_native_language"></div>
          <div class="col-md-4"><label class="form-label">Does child speak Arabic?</label><select class="form-select child-required" name="child_speaks_arabic"><option value="">Choose...</option><option>Yes</option><option>No</option><option>A little</option></select></div>
          <div class="col-md-6"><label class="form-label">Can child read Arabic?</label><select class="form-select child-required" name="child_can_read_arabic"><option value="">Choose...</option><option>Yes</option><option>No</option><option>A little</option></select></div>
          <div class="col-md-6"><label class="form-label">Can child write Arabic?</label><select class="form-select child-required" name="child_can_write_arabic"><option value="">Choose...</option><option>Yes</option><option>No</option><option>A little</option></select></div>
          <div class="col-md-6"><label class="form-label">Child learning goal</label><input class="form-control child-required" name="child_learning_goal" value="<?= htmlspecialchars($purchase['main_goal'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
          <div class="col-md-6"><label class="form-label">Studied Arabic before?</label><select class="form-select child-required" name="studied_arabic_before"><option value="">Choose...</option><option>Yes</option><option>No</option><option>Some exposure</option></select></div>
          <div class="col-md-6"><label class="form-label">Struggles</label><textarea class="form-control child-required" name="struggles" rows="3"></textarea></div>
          <div class="col-md-6"><label class="form-label">Learning style notes</label><textarea class="form-control child-required" name="learning_style_notes" rows="3"></textarea></div>
        </div>

        <div class="col-md-6"><label class="form-label">Scheduling preferences</label><textarea class="form-control" name="scheduling_preferences" rows="3" required></textarea></div>
        <div class="col-md-6"><label class="form-label">Notes for tutor</label><textarea class="form-control" name="notes_for_tutor" rows="3" required></textarea></div>
        <div class="col-12"><button class="btn btn-brand btn-lg" type="submit">Submit and continue to level check</button></div>
      </form>
    </div>
  </div>
</section>
<script>
  const checkoutType = <?= json_encode($checkoutLearnerType) ?>;
  const adultFields = document.getElementById('adultFields');
  const childFields = document.getElementById('childFields');
  const someoneElseType = document.getElementById('someoneElseType');

  function toggleLearnerFields() {
    const type = checkoutType === 'someone_else' ? someoneElseType.value : checkoutType;
    const isChild = type === 'child';
    adultFields.classList.toggle('d-none', isChild);
    childFields.classList.toggle('d-none', !isChild);
    document.querySelectorAll('.adult-required').forEach(el => el.required = !isChild);
    document.querySelectorAll('.child-required').forEach(el => el.required = isChild);
  }

  if (someoneElseType) someoneElseType.addEventListener('change', toggleLearnerFields);
  toggleLearnerFields();
</script>
<?php
render_public_layout('Student Form | Habiba Nabil Arabic Academy', 'Complete your Arabic learner onboarding form after checkout.', ob_get_clean(), false);

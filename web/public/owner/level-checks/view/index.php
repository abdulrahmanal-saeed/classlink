<?php
/**
 * /owner/level-checks/view?id=...
 *
 * Owner detailed review page for adult level checks and child literacy checks.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LevelCheck.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$attemptId = (int) ($_GET['id'] ?? 0);
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $manualScore = $_POST['manual_score'] !== '' ? (float) $_POST['manual_score'] : null;
        level_owner_mark_reviewed(
            $attemptId,
            trim($_POST['final_level'] ?? ''),
            $manualScore,
            trim($_POST['teacher_notes'] ?? ''),
            (int) $user['id']
        );
        $message = 'Level check marked reviewed.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$attempt = level_attempt_detail($attemptId);

if (!$attempt) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Level check attempt not found.</div><a class="btn btn-outline-brand" href="/owner/level-checks">Back</a>';
    render_dashboard_shell($user, 'Level Check Not Found', $content);
    exit;
}

$answers = level_attempt_answers($attemptId);
$uploads = level_attempt_uploads($attemptId);
$intakePayload = json_decode($attempt['raw_payload'] ?? '{}', true);
$intakePayload = is_array($intakePayload) ? $intakePayload : [];
$isChild = $attempt['attempt_type'] === 'child_literacy';
$finalOptions = $isChild
    ? ['Level 0: cannot recognize letters', 'Level 1: recognizes some letters', 'Level 2: recognizes letters but struggles with connections', 'Level 3: can read simple words', 'Level 4: can read/write simple sentences with support']
    : ['A0 Complete Beginner', 'A1', 'A2', 'Strong A2 / B1 Activation', 'B1 / Needs Speaking Assessment', 'B1', 'B2'];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review the attempt and confirm the final level.</p>
    <small class="text-muted">Attempt ID: <?= (int) $attemptId ?></small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/level-checks">Back to level checks</a>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="status-box mb-4">
      <h2 class="h5 fw-bold"><?= $isChild ? 'Child review summary' : 'Adult review summary' ?></h2>
      <dl class="row mb-0 mt-3">
        <dt class="col-sm-4">Student</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['learner_name'] ?? $attempt['full_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">WhatsApp</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['whatsapp'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Plan</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['plan_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Auto score</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($attempt['auto_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>%</dd>
        <?php if (!$isChild): ?>
          <dt class="col-sm-4">Vocabulary</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($attempt['vocabulary_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>%</dd>
          <dt class="col-sm-4">Sentence building</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($attempt['sentence_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>%</dd>
          <dt class="col-sm-4">Reading</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($attempt['reading_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>%</dd>
        <?php else: ?>
          <dt class="col-sm-4">Letter recognition</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($attempt['letter_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>%</dd>
        <?php endif; ?>
        <dt class="col-sm-4">Suggested level</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['suggested_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Recommended first lesson</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['recommended_first_lesson'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    </div>

    <div class="foundation-card mb-4">
      <h2 class="h5 fw-bold mb-3"><?= $isChild ? 'Parent / child info' : 'Student intake answers' ?></h2>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>Field</th><th>Answer</th></tr></thead>
          <tbody>
            <?php foreach ($intakePayload as $key => $value): ?>
              <tr><td><code><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></code></td><td><?= nl2br(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="foundation-card mb-4">
      <h2 class="h5 fw-bold mb-3">Answers</h2>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>Section</th><th>Question</th><th>Answer</th><th>Correct</th><th>Score</th></tr></thead>
          <tbody>
            <?php foreach ($answers as $answer): ?>
              <tr>
                <td><?= htmlspecialchars($answer['section_key'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($answer['question_text'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= nl2br(htmlspecialchars($answer['answer_text'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></td>
                <td><?= htmlspecialchars($answer['correct_answer'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($answer['score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="foundation-card">
      <h2 class="h5 fw-bold mb-3">Uploads</h2>
      <?php if (!$uploads): ?>
        <div class="alert alert-light border">No uploads found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Purpose</th><th>File</th><th>MIME</th><th>Size</th></tr></thead>
            <tbody>
              <?php foreach ($uploads as $upload): ?>
                <tr>
                  <td><?= htmlspecialchars($upload['purpose'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><a href="/<?= htmlspecialchars($upload['file_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($upload['original_filename'] ?? $upload['file_path'], ENT_QUOTES, 'UTF-8') ?></a></td>
                  <td><?= htmlspecialchars($upload['mime_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= round(((int) ($upload['size_bytes'] ?? 0)) / 1024, 1) ?> KB</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Final review</h2>
      <p class="text-muted">Owner final review decides. Do not over-place based on auto score only.</p>
      <form method="post">
        <div class="mb-3"><label class="form-label">Manual score</label><input class="form-control" type="number" step="0.01" min="0" max="100" name="manual_score" value="<?= htmlspecialchars((string) ($attempt['manual_score'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="mb-3"><label class="form-label">Final <?= $isChild ? 'literacy level' : 'level' ?></label><select class="form-select" name="final_level" required><?php foreach ($finalOptions as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= (($attempt['final_level'] ?: $attempt['suggested_level']) === $option) ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label">Teacher notes</label><textarea class="form-control" name="teacher_notes" rows="8"><?= htmlspecialchars($attempt['teacher_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
        <button class="btn btn-brand" type="submit">Mark reviewed</button>
      </form>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Level Check Review', $content);

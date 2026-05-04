<?php
/**
 * /owner/students/practice-words?id={studentUserId}
 * Owner adds and manages practice words for a student.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningEngagement.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$studentId = (int) ($_GET['id'] ?? 0);
$profile = student_portal_profile($studentId);
if (!$profile) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Student not found.</div><a class="btn btn-outline-brand" href="/owner/students">Back</a>';
    render_dashboard_shell($user, 'Student Not Found', $content);
    exit;
}
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        engagement_add_practice_word((int) $user['id'], $studentId, $_POST);
        $message = 'Practice word added.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$words = engagement_all_practice_words($studentId);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Add and review practice words for <?= htmlspecialchars($profile['display_name'], ENT_QUOTES, 'UTF-8') ?>.</p>
    <small class="text-muted">New words become due immediately.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/students/view?id=<?= (int) $studentId ?>">Back to student</a>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<form method="post" class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Add practice word</h2>
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Arabic word</label><input class="form-control" name="word_ar" required></div>
    <div class="col-md-4"><label class="form-label">English meaning</label><input class="form-control" name="word_en"></div>
    <div class="col-md-4"><label class="form-label">Source</label><input class="form-control" name="source" value="owner_manual"></div>
    <div class="col-12"><label class="form-label">Arabic example sentence</label><input class="form-control" name="example_sentence_ar"></div>
    <div class="col-12"><button class="btn btn-brand" type="submit">Add word</button></div>
  </div>
</form>

<div class="foundation-card">
  <h2 class="h5 fw-bold">Practice words</h2>
  <?php if (!$words): ?>
    <div class="alert alert-light border mb-0">No practice words yet.</div>
  <?php else: ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Word</th><th>Meaning</th><th>Mastery</th><th>Next review</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($words as $word): ?>
      <tr>
        <td dir="rtl"><strong><?= htmlspecialchars($word['word_ar'], ENT_QUOTES, 'UTF-8') ?></strong><br><small><?= htmlspecialchars($word['example_sentence_ar'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
        <td><?= htmlspecialchars($word['word_en'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= (int) $word['mastery_level'] ?>/5</td>
        <td><?= htmlspecialchars($word['next_review_at'] ?? 'Due now', ENT_QUOTES, 'UTF-8') ?></td>
        <td><span class="badge text-bg-light border"><?= htmlspecialchars($word['due_status'], ENT_QUOTES, 'UTF-8') ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Student Practice Words', $content);

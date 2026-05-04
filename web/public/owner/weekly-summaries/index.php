<?php
/**
 * /owner/weekly-summaries
 * Generate and review weekly student summaries.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/AITools.php';
require_once __DIR__ . '/../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$students = learning_students_for_select();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $studentId = (int) $_POST['student_user_id'];
        $context = ai_student_context($studentId);
        $draftId = ai_run_preview((int) $user['id'], 'weekly_student_summary', $context, [
            'teacher_notes' => trim($_POST['teacher_notes'] ?? ''),
            'week_focus' => trim($_POST['week_focus'] ?? ''),
        ], 'student', (string) $studentId);
        header('Location: /owner/ai/preview?id=' . $draftId);
        exit;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$summaries = db()->query('SELECT weekly_student_summaries.*, users.display_name AS student_name FROM weekly_student_summaries LEFT JOIN users ON users.id = weekly_student_summaries.student_user_id ORDER BY weekly_student_summaries.created_at DESC LIMIT 100')->fetchAll();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Generate draft weekly summaries with preview and apply/save only.</p>
    <small class="text-muted">Summary includes what went well, focus areas, next week focus, and engagement level.</small>
  </div>
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<form method="post" class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Generate weekly summary</h2>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Student</label><select class="form-select" name="student_user_id" required><?php foreach ($students as $student): ?><option value="<?= (int) $student['id'] ?>"><?= htmlspecialchars($student['display_name'] . ' — ' . $student['email'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Week focus optional</label><input class="form-control" name="week_focus" placeholder="speaking, homework, vocabulary..."></div>
    <div class="col-12"><label class="form-label">Teacher notes</label><textarea class="form-control" name="teacher_notes" rows="4"></textarea></div>
    <div class="col-12"><button class="btn btn-brand" type="submit">Generate preview</button></div>
  </div>
</form>

<div class="foundation-card">
  <h2 class="h5 fw-bold">Saved weekly summaries</h2>
  <?php if (!$summaries): ?>
    <div class="alert alert-light border mb-0">No saved summaries yet.</div>
  <?php else: ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Student</th><th>Week</th><th>Engagement</th><th>Status</th><th>Summary</th></tr></thead><tbody>
    <?php foreach ($summaries as $summary): ?>
      <tr>
        <td><?= htmlspecialchars($summary['student_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(($summary['week_start'] ?? '-') . ' → ' . ($summary['week_end'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        <td><span class="badge text-bg-light border"><?= htmlspecialchars($summary['engagement_level'], ENT_QUOTES, 'UTF-8') ?></span></td>
        <td><?= htmlspecialchars($summary['status'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(mb_strimwidth((string) $summary['summary_text'], 0, 120, '...'), ENT_QUOTES, 'UTF-8') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Weekly Student Summaries', $content);

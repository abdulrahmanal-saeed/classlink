<?php
/**
 * /owner/students/ai?id={studentUserId}
 * Student-specific AI tools.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AITools.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$studentId = (int) ($_GET['id'] ?? 0);
$context = ai_student_context($studentId);
$studentName = $context['profile']['display_name'] ?? 'Student';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tool = $_POST['tool_name'] ?? 'analyze_student';
        $draftId = ai_run_preview((int) $user['id'], $tool, $context, [
            'teacher_notes' => trim($_POST['teacher_notes'] ?? ''),
            'target_sessions' => trim($_POST['target_sessions'] ?? ''),
            'topic' => trim($_POST['topic'] ?? ''),
        ], 'student', (string) $studentId);
        header('Location: /owner/ai/preview?id=' . $draftId);
        exit;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$tools = [
    'analyze_student' => 'Analyze Student',
    'plan_remaining_sessions' => 'Plan Remaining Sessions',
    'prepare_next_lesson' => 'Prepare Next Lesson',
    'generate_homework' => 'Generate Homework',
    'generate_scenario' => 'Generate Scenario',
    'weekly_student_summary' => 'Generate Weekly Student Summary',
];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Run preview-only AI tools for <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>.</p>
    <small class="text-muted">Owner must apply/save after preview. Nothing is auto-published.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/students/view?id=<?= (int) $studentId ?>">Back to student</a>
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<form method="post" class="foundation-card">
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">AI tool</label><select class="form-select" name="tool_name"><?php foreach ($tools as $key => $label): ?><option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Target sessions optional</label><input class="form-control" name="target_sessions" placeholder="e.g. 4, 8, 12"></div>
    <div class="col-md-3"><label class="form-label">Topic optional</label><input class="form-control" name="topic" placeholder="work, travel, daily life"></div>
    <div class="col-12"><label class="form-label">Teacher notes / extra instructions</label><textarea class="form-control" name="teacher_notes" rows="5" placeholder="Add anything the AI should consider."></textarea></div>
    <div class="col-12"><button class="btn btn-brand" type="submit">Generate preview</button></div>
  </div>
</form>

<div class="foundation-card mt-4">
  <h2 class="h5 fw-bold">Context sent to AI</h2>
  <p class="text-muted">The AI uses profile, balance, progress, recent homework, scenarios, reviews, practice words, and session notes.</p>
  <pre class="bg-light border rounded-4 p-3 small" style="white-space:pre-wrap;max-height:360px;overflow:auto;"><?= htmlspecialchars(json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') ?></pre>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Student AI Tools', $content);

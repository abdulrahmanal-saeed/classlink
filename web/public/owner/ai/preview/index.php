<?php
/**
 * /owner/ai/preview?id={draftId}
 * Preview AI output and apply only after Owner confirmation.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AITools.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$draftId = (int) ($_GET['id'] ?? 0);
$draft = ai_draft($draftId);
$message = null;
$error = null;

if (!$draft) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">AI draft not found.</div><a class="btn btn-outline-brand" href="/owner/ai">Back</a>';
    render_dashboard_shell($user, 'AI Draft Not Found', $content);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'apply_homework') {
            $studentId = (int) ($draft['related_type'] === 'student' ? $draft['related_id'] : ($_POST['student_user_id'] ?? 0));
            $createdId = ai_apply_homework_draft($draftId, (int) $user['id'], $studentId);
            $message = 'Applied as draft homework #' . $createdId . '.';
        } elseif ($action === 'apply_scenario') {
            $studentId = (int) ($draft['related_type'] === 'student' ? $draft['related_id'] : ($_POST['student_user_id'] ?? 0));
            $createdId = ai_apply_scenario_draft($draftId, (int) $user['id'], $studentId);
            $message = 'Applied as draft scenario #' . $createdId . '.';
        } elseif ($action === 'apply_summary') {
            $studentId = (int) ($draft['related_type'] === 'student' ? $draft['related_id'] : ($_POST['student_user_id'] ?? 0));
            $createdId = ai_apply_weekly_summary($draftId, (int) $user['id'], $studentId);
            $message = 'Saved weekly summary #' . $createdId . '.';
        } elseif ($action === 'apply_article') {
            $createdId = ai_apply_article_draft($draftId, (int) $user['id']);
            $message = 'Applied as draft article #' . $createdId . '.';
        }
        $draft = ai_draft($draftId);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$toolLabel = ai_tool_labels()[$draft['tool_name']] ?? $draft['tool_name'];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Preview only. Review and edit later before publishing.</p>
    <span class="badge text-bg-light border"><?= htmlspecialchars($draft['status'], ENT_QUOTES, 'UTF-8') ?></span>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-brand" href="/owner/ai">Back to AI</a>
    <a class="btn btn-outline-brand" href="/owner/ai/logs">Logs</a>
  </div>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="foundation-card mb-4">
  <h2 class="h4 fw-bold"><?= htmlspecialchars($toolLabel, ENT_QUOTES, 'UTF-8') ?></h2>
  <div class="small text-muted">Related: <?= htmlspecialchars(($draft['related_type'] ?? '-') . ' #' . ($draft['related_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> · Created: <?= htmlspecialchars($draft['created_at'], ENT_QUOTES, 'UTF-8') ?></div>
</div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">AI Output Preview</h2>
  <pre class="bg-light border rounded-4 p-3" style="white-space:pre-wrap;max-height:640px;overflow:auto;"><?= htmlspecialchars($draft['response_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></pre>
</div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Apply / Save Draft</h2>
  <?php if ($draft['status'] !== 'draft'): ?>
    <div class="alert alert-light border mb-0">This AI draft has already been applied or discarded.</div>
  <?php else: ?>
    <p class="text-muted">Applying creates draft content only. Nothing is published automatically.</p>
    <div class="d-flex gap-2 flex-wrap">
      <?php if ($draft['tool_name'] === 'generate_homework'): ?><form method="post"><input type="hidden" name="action" value="apply_homework"><button class="btn btn-brand">Apply as draft homework</button></form><?php endif; ?>
      <?php if ($draft['tool_name'] === 'generate_scenario'): ?><form method="post"><input type="hidden" name="action" value="apply_scenario"><button class="btn btn-brand">Apply as draft scenario</button></form><?php endif; ?>
      <?php if ($draft['tool_name'] === 'weekly_student_summary'): ?><form method="post"><input type="hidden" name="action" value="apply_summary"><button class="btn btn-brand">Save weekly summary</button></form><?php endif; ?>
      <?php if ($draft['tool_name'] === 'generate_article'): ?><form method="post"><input type="hidden" name="action" value="apply_article"><button class="btn btn-brand">Apply as draft article</button></form><?php endif; ?>
      <?php if (!in_array($draft['tool_name'], ['generate_homework','generate_scenario','weekly_student_summary','generate_article'], true)): ?><div class="alert alert-light border mb-0">This tool output is a preview note. Copy/use it manually or keep it in logs.</div><?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<details class="foundation-card">
  <summary class="fw-bold">Prompt sent to AI</summary>
  <pre class="bg-light border rounded-4 p-3 mt-3 small" style="white-space:pre-wrap;max-height:420px;overflow:auto;"><?= htmlspecialchars($draft['prompt_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></pre>
</details>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'AI Preview', $content);

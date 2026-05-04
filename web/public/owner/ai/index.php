<?php
/**
 * /owner/ai
 * Central AI tools dashboard.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/AITools.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$tools = ai_tool_labels();
$drafts = ai_recent_drafts();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">AI tools are preview-only. Nothing is published automatically.</p>
    <small class="text-muted">Provider: <?= htmlspecialchars(ai_setting('ai_provider', 'anthropic'), ENT_QUOTES, 'UTF-8') ?> · Model: <?= htmlspecialchars(ai_setting('ai_default_model', 'claude-sonnet-4-20250514'), ENT_QUOTES, 'UTF-8') ?></small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-brand" href="/owner/ai/logs">AI logs</a>
    <a class="btn btn-brand" href="/owner/cms/articles/generate">Generate article</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6"><div class="status-box h-100"><h2 class="h5 fw-bold">Student AI Tools</h2><p class="text-muted">Run analysis, plans, lessons, homework, scenarios, and weekly summaries from each student AI page.</p><a class="btn btn-sm btn-outline-brand" href="/owner/students">Choose student</a></div></div>
  <div class="col-md-6"><div class="status-box h-100"><h2 class="h5 fw-bold">Marketing AI Tools</h2><p class="text-muted">Generate draft-only articles and cover image prompts.</p><a class="btn btn-sm btn-outline-brand" href="/owner/cms/articles/generate">Generate article</a></div></div>
</div>

<div class="foundation-card">
  <h2 class="h5 fw-bold mb-3">Recent AI drafts</h2>
  <?php if (!$drafts): ?>
    <div class="alert alert-light border mb-0">No AI drafts yet.</div>
  <?php else: ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>Tool</th><th>Related</th><th>Status</th><th>Action</th></tr></thead><tbody>
    <?php foreach ($drafts as $draft): ?>
      <tr>
        <td><?= htmlspecialchars($draft['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($tools[$draft['tool_name']] ?? $draft['tool_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(($draft['related_type'] ?? '-') . ' #' . ($draft['related_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        <td><span class="badge text-bg-light border"><?= htmlspecialchars($draft['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
        <td><a class="btn btn-sm btn-outline-brand" href="/owner/ai/preview?id=<?= (int) $draft['id'] ?>">Preview</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'AI Teacher & Marketing Tools', $content);

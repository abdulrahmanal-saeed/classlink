<?php
/**
 * /owner/cms/articles/generate
 * Generate article draft and cover image prompt. Preview only, apply as draft article.
 */

require_once __DIR__ . '/../../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../../backend/php/shared/AITools.php';
require_once __DIR__ . '/../../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tool = $_POST['tool_name'] ?? 'generate_article';
        $draftId = ai_run_preview((int) $user['id'], $tool, [], [
            'topic' => trim($_POST['topic'] ?? ''),
            'target_audience' => trim($_POST['target_audience'] ?? ''),
            'cta' => trim($_POST['cta'] ?? 'Book a session'),
            'language_style' => trim($_POST['language_style'] ?? 'Arabic with optional English support'),
            'extra_notes' => trim($_POST['extra_notes'] ?? ''),
        ], 'article', null);
        header('Location: /owner/ai/preview?id=' . $draftId);
        exit;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Generate marketing article drafts. Generated articles are saved as draft only after preview.</p>
    <small class="text-muted">Output includes SEO, excerpt, CTA, keywords, and cover prompt.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/cms/articles">Back to articles</a>
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<form method="post" class="foundation-card">
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Tool</label><select class="form-select" name="tool_name"><option value="generate_article">Generate Article</option><option value="article_cover_prompt">Generate Article Cover Image Prompt Only</option></select></div>
    <div class="col-md-6"><label class="form-label">Topic</label><input class="form-control" name="topic" required placeholder="مثلاً: لماذا لا أبدأ مع كل الطلاب من الصفر؟"></div>
    <div class="col-md-6"><label class="form-label">Target audience</label><input class="form-control" name="target_audience" placeholder="Parents, adult learners, Arabic beginners..."></div>
    <div class="col-md-6"><label class="form-label">CTA</label><input class="form-control" name="cta" value="Book a single session"></div>
    <div class="col-md-6"><label class="form-label">Language style</label><input class="form-control" name="language_style" value="Arabic article with optional English support"></div>
    <div class="col-12"><label class="form-label">Extra notes</label><textarea class="form-control" name="extra_notes" rows="4" placeholder="Brand tone, offer, SEO notes, story angle..."></textarea></div>
    <div class="col-12"><button class="btn btn-brand" type="submit">Generate preview</button></div>
  </div>
</form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Generate Article Draft', $content);

<?php
require_once __DIR__ . '/../../../../backend/php/shared/HelpCenter.php';
require_once __DIR__ . '/../../../../web/components/layout/public_layout.php';
$slug = trim($_GET['role'] ?? 'student');
$role = help_role_from_slug($slug);
$q = trim($_GET['q'] ?? '');
$lang = $_GET['lang'] ?? 'en';
$articles = help_articles_for_role($role, $lang, $q);
$steps = help_tour_steps($role);
ob_start();
?>
<section class="py-5"><div class="container">
  <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4"><div><h1 class="hero-title"><?=htmlspecialchars(help_role_label($role),ENT_QUOTES,'UTF-8')?> Guide</h1><p class="hero-subtitle">Quick start, FAQs, and step-by-step help for this role.</p></div><a class="btn btn-outline-brand" href="/help">All Help</a></div>
  <form class="foundation-card mb-4"><input type="hidden" name="role" value="<?=htmlspecialchars($slug,ENT_QUOTES,'UTF-8')?>"><div class="input-group"><input class="form-control" name="q" value="<?=htmlspecialchars($q,ENT_QUOTES,'UTF-8')?>" placeholder="Search this guide"><button class="btn btn-brand">Search</button></div></form>
  <?php if($steps):?><div class="foundation-card mb-4"><h2 class="h5 fw-bold">Guided tour topics</h2><ol><?php foreach($steps as $step):?><li><?=htmlspecialchars($step,ENT_QUOTES,'UTF-8')?></li><?php endforeach;?></ol></div><?php endif;?>
  <div class="row g-3"><?php foreach($articles as $a):?><div class="col-md-6"><article class="foundation-card h-100"><span class="badge text-bg-light border mb-2"><?=htmlspecialchars($a['category'],ENT_QUOTES,'UTF-8')?></span><h2 class="h5 fw-bold"><?=htmlspecialchars($a['title'],ENT_QUOTES,'UTF-8')?></h2><div style="white-space:pre-wrap"><?=htmlspecialchars($a['content'],ENT_QUOTES,'UTF-8')?></div><?php if($a['video_url']):?><p class="mt-3"><a target="_blank" rel="noopener noreferrer" href="<?=htmlspecialchars($a['video_url'],ENT_QUOTES,'UTF-8')?>">Watch video</a></p><?php endif;?></article></div><?php endforeach;?></div>
  <?php if(!$articles):?><div class="alert alert-light border">No help articles found.</div><?php endif;?>
  <div class="text-center mt-4"><a class="btn btn-outline-brand" href="<?=htmlspecialchars((string)help_setting('help_whatsapp_url','https://wa.me/'),ENT_QUOTES,'UTF-8')?>" target="_blank" rel="noopener noreferrer">Still need help?</a></div>
</div></section>
<?php render_public_layout(help_role_label($role).' Guide | Help Center','Role-based platform guide.',ob_get_clean(),true);
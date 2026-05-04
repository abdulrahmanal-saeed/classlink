<?php
require_once __DIR__ . '/../../../backend/php/shared/HelpCenter.php';
require_once __DIR__ . '/../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$user = auth_user();
$q = trim($_GET['q'] ?? '');
$roles = [
  'owner' => 'Owner / Teacher',
  'student' => 'Student',
  'parent' => 'Parent',
  'academy' => 'Academy Partner',
  'media-buyer' => 'Media Buyer / Marketing Partner',
];
$articles = help_articles_for_role('public', 'en', $q);
ob_start();
?>
<section class="py-5"><div class="container">
  <div class="row align-items-center g-4 mb-4"><div class="col-lg-8"><h1 class="hero-title">Help Center</h1><p class="hero-subtitle">Learn how Habiba Nabil Arabic Academy works and find the right guide for your role.</p></div><div class="col-lg-4"><form><div class="input-group"><input class="form-control" name="q" value="<?=htmlspecialchars($q,ENT_QUOTES,'UTF-8')?>" placeholder="Search help"><button class="btn btn-brand">Search</button></div></form></div></div>
  <div class="row g-3 mb-4"><?php foreach($roles as $slug=>$label):?><div class="col-md-4"><a class="foundation-card d-block text-decoration-none h-100" href="/help/<?=$slug?>"><h2 class="h5 fw-bold"><?=htmlspecialchars($label,ENT_QUOTES,'UTF-8')?></h2><p class="text-muted mb-0">Open role-based guide.</p></a></div><?php endforeach;?></div>
  <div class="foundation-card"><h2 class="h5 fw-bold">General articles</h2><?php if(!$articles):?><div class="alert alert-light border">No articles found.</div><?php else:?><div class="row g-3"><?php foreach($articles as $a):?><div class="col-md-6"><div class="status-box h-100"><span class="badge text-bg-light border mb-2"><?=htmlspecialchars($a['category'],ENT_QUOTES,'UTF-8')?></span><h3 class="h5"><?=htmlspecialchars($a['title'],ENT_QUOTES,'UTF-8')?></h3><p><?=htmlspecialchars(mb_strimwidth($a['content'],0,160,'...'),ENT_QUOTES,'UTF-8')?></p></div></div><?php endforeach;?></div><?php endif;?></div>
  <div class="text-center mt-4"><a class="btn btn-outline-brand" href="<?=htmlspecialchars((string)help_setting('help_whatsapp_url','https://wa.me/'),ENT_QUOTES,'UTF-8')?>" target="_blank" rel="noopener noreferrer">Still need help? Contact us</a></div>
</div></section>
<?php render_public_layout('Help Center | Habiba Nabil Arabic Academy','Role-based help and onboarding guides.',ob_get_clean(),true);
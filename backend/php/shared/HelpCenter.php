<?php
/** Phase 29 Help Center helper. */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';

function help_role_from_slug(string $slug): string { return match($slug){'owner'=>'owner_teacher','student'=>'student','parent'=>'parent','academy'=>'academy_partner','media-buyer','media'=>'media_buyer',default=>'public'}; }
function help_slug_from_role(string $role): string { return match($role){'owner_teacher'=>'owner','student'=>'student','parent'=>'parent','academy_partner'=>'academy','media_buyer'=>'media-buyer',default=>''}; }
function help_role_label(string $role): string { return match($role){'owner_teacher'=>'Owner / Teacher','student'=>'Student','parent'=>'Parent','academy_partner'=>'Academy Partner','media_buyer'=>'Media Buyer / Marketing Partner',default=>'General Help'}; }

function help_articles_for_role(string $role, string $language='en', string $q=''): array {
  $where=['status="published"','(role=:role OR role="public")','(language=:lang OR language="both")']; $p=[':role'=>$role,':lang'=>$language];
  if($q!==''){ $where[]='(title LIKE :q OR category LIKE :q OR content LIKE :q)'; $p[':q']='%'.$q.'%'; }
  $s=db()->prepare('SELECT * FROM help_articles WHERE '.implode(' AND ',$where).' ORDER BY role="public" ASC, sort_order ASC, title ASC'); $s->execute($p); $rows=$s->fetchAll();
  return (!$rows && $language!=='en') ? help_articles_for_role($role,'en',$q) : $rows;
}
function help_owner_articles(): array { return db()->query('SELECT * FROM help_articles ORDER BY role ASC, sort_order ASC, title ASC LIMIT 500')->fetchAll(); }
function help_article_by_id(int $id): ?array { $s=db()->prepare('SELECT * FROM help_articles WHERE id=:id LIMIT 1'); $s->execute([':id'=>$id]); $r=$s->fetch(); return $r?:null; }
function help_save_article(array $d, array $owner): int {
  $id=(int)($d['id']??0); $title=trim($d['title']??''); $slug=strtolower(trim($d['slug']??'')); $content=trim($d['content']??'');
  if(!$title||!$slug||!$content) throw new RuntimeException('Title, slug, and content are required.');
  $p=[':title'=>$title,':slug'=>$slug,':role'=>$d['role']??'public',':category'=>trim($d['category']??'General'),':content'=>$content,':video'=>($d['video_url']??'')?:null,':lang'=>$d['language']??'both',':status'=>$d['status']??'draft',':sort'=>(int)($d['sort_order']??0),':owner'=>(int)$owner['id']];
  if($id>0){ $p[':id']=$id; db()->prepare('UPDATE help_articles SET title=:title,slug=:slug,role=:role,category=:category,content=:content,video_url=:video,language=:lang,status=:status,sort_order=:sort,created_by_user_id=:owner WHERE id=:id')->execute($p); audit_log((int)$owner['id'],'help_article_updated','help_article',(string)$id); return $id; }
  db()->prepare('INSERT INTO help_articles (title,slug,role,category,content,video_url,language,status,sort_order,created_by_user_id) VALUES (:title,:slug,:role,:category,:content,:video,:lang,:status,:sort,:owner)')->execute($p); $new=(int)db()->lastInsertId(); audit_log((int)$owner['id'],'help_article_created','help_article',(string)$new); return $new;
}
function help_checklist_items(string $role): array { return match($role){
  'student'=>['complete_profile'=>'Complete profile','check_lesson'=>'Check upcoming lesson','submit_homework'=>'Submit first homework','review_feedback'=>'Review teacher feedback','review_flashcards'=>'Review flashcards'],
  'parent'=>['open_child_profile'=>'Open child profile','check_lesson'=>'Check upcoming lesson','review_homework'=>'Review homework status','check_balance'=>'Check package balance','contact_teacher'=>'Contact teacher'],
  'owner_teacher'=>['review_payment_settings'=>'Review payment settings','confirm_pricing'=>'Confirm pricing','check_notifications'=>'Check notification settings','create_student'=>'Create first student','create_homework'=>'Create first homework','test_checkout'=>'Test checkout'],
  'academy_partner'=>['complete_profile'=>'Complete academy profile','submit_brief'=>'Submit first brief','track_status'=>'Track brief status'],
  'media_buyer'=>['accept_agreement'=>'Accept agreement','copy_tracking_link'=>'Copy tracking link','create_campaign'=>'Create first campaign','check_order'=>'Check first attributed order'], default=>[]}; }
function help_checklist_progress(int $userId,string $role): array { $items=help_checklist_items($role); $s=db()->prepare('SELECT checklist_key,completed FROM user_onboarding_progress WHERE user_id=:u'); $s->execute([':u'=>$userId]); $done=[]; foreach($s->fetchAll() as $r)$done[$r['checklist_key']]=(bool)$r['completed']; $out=[]; foreach($items as $k=>$v)$out[]=['key'=>$k,'label'=>$v,'completed'=>$done[$k]??false]; return $out; }
function help_mark_checklist(int $userId,string $role,string $key,bool $completed): void { db()->prepare('INSERT INTO user_onboarding_progress (user_id,role,checklist_key,completed,completed_at) VALUES (:u,:r,:k,:c,IF(:c=1,NOW(),NULL)) ON DUPLICATE KEY UPDATE completed=VALUES(completed),completed_at=IF(VALUES(completed)=1,NOW(),NULL)')->execute([':u'=>$userId,':r'=>$role,':k'=>$key,':c'=>$completed?1:0]); }
function help_tour_steps(string $role): array { return match($role){'owner_teacher'=>['KPIs','Pending payments','New submissions','Quick actions','Settings'],'student'=>['Upcoming lesson','Homework','Feedback','Materials','Progress','Notifications'],'parent'=>['Child progress','Homework status','Teacher notes','Package balance','Contact teacher'],'media_buyer'=>['Tracking link','Funnel','Paid orders','Commission','Payouts'],'academy_partner'=>['Submit brief','Brief status','What happens next'],default=>[]}; }
function help_tour_status(int $userId,string $role,string $key): array { $s=db()->prepare('SELECT * FROM user_tour_progress WHERE user_id=:u AND tour_key=:k LIMIT 1'); $s->execute([':u'=>$userId,':k'=>$key]); $r=$s->fetch(); return $r?:['completed'=>0,'skipped'=>0]; }
function help_set_tour_status(int $userId,string $role,string $key,bool $completed,bool $skipped=false): void { db()->prepare('INSERT INTO user_tour_progress (user_id,role,tour_key,completed,skipped,completed_at) VALUES (:u,:r,:k,:c,:s,IF(:c=1,NOW(),NULL)) ON DUPLICATE KEY UPDATE completed=VALUES(completed),skipped=VALUES(skipped),completed_at=IF(VALUES(completed)=1,NOW(),NULL)')->execute([':u'=>$userId,':r'=>$role,':k'=>$key,':c'=>$completed?1:0,':s'=>$skipped?1:0]); }
function help_render_checklist(array $user): string { $items=help_checklist_progress((int)$user['id'],$user['role']); if(!$items)return ''; $html='<div class="foundation-card mb-4"><h2 class="h5 fw-bold">Start here checklist</h2>'; foreach($items as $i){$html.='<div class="d-flex justify-content-between border-bottom py-2"><span>'.($i['completed']?'✅':'⬜').' '.htmlspecialchars($i['label'],ENT_QUOTES,'UTF-8').'</span><a href="/help/'.help_slug_from_role($user['role']).'">How?</a></div>'; } return $html.'</div>'; }
function help_render_tour_card(array $user): string { $steps=help_tour_steps($user['role']); if(!$steps)return ''; $html='<div class="foundation-card mb-4"><h2 class="h5 fw-bold">Guided tour</h2><ol>'; foreach($steps as $s)$html.='<li>'.htmlspecialchars($s,ENT_QUOTES,'UTF-8').'</li>'; return $html.'</ol><a class="btn btn-outline-brand btn-sm" href="/help/'.help_slug_from_role($user['role']).'">Restart from Help Center</a></div>'; }

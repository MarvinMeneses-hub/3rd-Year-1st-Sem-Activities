<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user=current_user(); $pdo=db(); $id=(int)($_GET['id']??0);
$s=$pdo->prepare("SELECT a.id FROM announcements a JOIN teacher_courses tc ON tc.course_id=a.course_id WHERE a.id=? AND tc.teacher_id=? LIMIT 1");
$s->execute([$id,$user['id']]);
if(!$s->fetch()) exit('Announcement not found.');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$pdo->prepare("DELETE FROM announcements WHERE id=?")->execute([$id]);flash('success','Announcement deleted.');redirect('teacher/announcements.php');}
$page_title='Delete Announcement'; require __DIR__.'/../includes/header.php';
?>
<div class="app"><main class="main" style="margin-left:0"><div class="content" style="max-width:720px;margin:auto"><section class="card card-pad"><h2>Delete Announcement</h2><p>This action cannot be undone.</p><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button class="btn btn-danger">Delete</button><a class="btn btn-soft" href="announcements.php">Cancel</a></form></section></div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

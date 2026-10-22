<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user=current_user(); $pdo=db(); $id=(int)($_GET['id']??$_POST['id']??0);
$s=$pdo->prepare("SELECT a.*,c.title course_title FROM announcements a JOIN courses c ON c.id=a.course_id JOIN teacher_courses tc ON tc.course_id=c.id WHERE a.id=? AND tc.teacher_id=? LIMIT 1");
$s->execute([$id,$user['id']]); $row=$s->fetch();
if(!$row) exit('Announcement not found.');

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf(); $title=post('title'); $content=post('content');
    if($title && $content){$pdo->prepare("UPDATE announcements SET title=?,content=? WHERE id=?")->execute([$title,$content,$id]);flash('success','Announcement updated.');redirect('teacher/announcements.php');}
    flash('error','Complete all fields.');
}
$page_title='Edit Announcement'; require __DIR__.'/../includes/header.php';
?>
<div class="app"><main class="main" style="margin-left:0"><div class="content" style="max-width:760px;margin:auto"><section class="card card-pad"><div class="section-head"><h2>Edit Announcement</h2><a class="btn btn-soft" href="announcements.php">Back</a></div><p class="muted"><?=e($row['course_title'])?></p><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><div class="form-group"><label>Title</label><input class="input" name="title" value="<?=e($row['title'])?>" required></div><br><div class="form-group"><label>Message</label><textarea class="textarea" name="content" required><?=e($row['content'])?></textarea></div><br><button class="btn btn-primary">Save Changes</button></form></section></div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

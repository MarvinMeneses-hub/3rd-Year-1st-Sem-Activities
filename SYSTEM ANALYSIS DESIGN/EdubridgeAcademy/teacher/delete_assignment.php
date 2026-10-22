<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user=current_user(); $pdo=db(); $id=(int)($_GET['id']??0);
$s=$pdo->prepare("SELECT a.id,a.activity_id FROM assignments a JOIN activities a2 ON a2.id=a.activity_id JOIN teacher_courses tc ON tc.course_id=a2.course_id WHERE a.id=? AND tc.teacher_id=? LIMIT 1");
$s->execute([$id,$user['id']]); $row=$s->fetch();
if(!$row) exit('Assignment not found or you do not have permission to delete it.');
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $pdo->prepare("DELETE FROM assignments WHERE id=?")->execute([$id]);
    flash('success','Assignment deleted.'); redirect('teacher/assignments.php');
}
$page_title='Delete Assignment'; require __DIR__.'/../includes/header.php';
?>
<div class="app"><main class="main" style="margin-left:0"><div class="content" style="max-width:720px;margin:auto"><section class="card card-pad"><div class="section-head"><h2>Delete Assignment</h2><a class="btn btn-soft" href="activities.php">Cancel</a></div><p>This removes the assignment and its linked activity/submissions. This action cannot be undone.</p><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button class="btn btn-danger">Delete Assignment</button></form></section></div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

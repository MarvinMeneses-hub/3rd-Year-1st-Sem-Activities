<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user=current_user(); $pdo=db(); $id=(int)($_GET['id']??$_POST['id']??0);
$s=$pdo->prepare("SELECT a.id,a.activity_id,a.allow_late,a2.title,a2.instructions,a2.points,a2.due_at,a2.course_id,c.title course_title,c.course_code
                  FROM assignments a JOIN activities a2 ON a2.id=a.activity_id JOIN courses c ON c.id=a2.course_id
                  JOIN teacher_courses tc ON tc.course_id=c.id WHERE a.id=? AND tc.teacher_id=? LIMIT 1");
$s->execute([$id,$user['id']]); $row=$s->fetch();
if(!$row) exit('Assignment not found or you do not have permission to edit it.');

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $title=post('title'); $instructions=post('instructions'); $points=(float)($_POST['points']??0);
    $due=post('due_at')?:null; $late=(int)($_POST['allow_late']??0);
    if(!$title){flash('error','Assignment title is required.');redirect('teacher/edit_assignment.php?id='.$id);}
    $pdo->beginTransaction();
    try{
        $pdo->prepare("UPDATE activities SET title=?,instructions=?,points=?,due_at=? WHERE id=?")->execute([$title,$instructions,$points,$due,$row['activity_id']]);
        $pdo->prepare("UPDATE assignments SET allow_late=? WHERE id=?")->execute([$late,$id]);
        $pdo->commit(); flash('success','Assignment updated.'); redirect('teacher/assignments.php');
    }catch(Throwable $e){$pdo->rollBack();flash('error','Unable to update the assignment.');redirect('teacher/edit_assignment.php?id='.$id);}
}

$page_title='Edit Assignment'; require __DIR__.'/../includes/header.php';
?>
<div class="app"><aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Teacher</span></div></div><nav class="nav"><a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a><a href="activities.php" class="active"><i class="fa-solid fa-list-check"></i>Activities</a><a href="submissions.php"><i class="fa-solid fa-file-circle-check"></i>Submissions</a><a href="analytics.php"><i class="fa-solid fa-chart-line"></i>Analytics</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Edit Assignment</h1></div></header><div class="content">
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow"><?=e($row['course_code'])?></span><h2><?=e($row['course_title'])?></h2></div><a class="btn btn-soft" href="activities.php">Back</a></div>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>">
<div class="form-grid"><div class="form-group"><label>Title</label><input class="input" name="title" required value="<?=e($row['title'])?>"></div><div class="form-group"><label>Points</label><input class="input" type="number" step="0.01" name="points" value="<?=e((string)$row['points'])?>"></div><div class="form-group"><label>Due date</label><input class="input" type="datetime-local" name="due_at" value="<?=e($row['due_at']?date('Y-m-d\TH:i',strtotime($row['due_at'])):'')?>"></div><div class="form-group full"><label>Instructions</label><textarea class="textarea" name="instructions"><?=e($row['instructions'])?></textarea></div></div>
<label><input type="checkbox" name="allow_late" value="1" <?=$row['allow_late']?'checked':''?>> Allow late submissions</label>
<div class="form-actions"><button class="btn btn-primary">Save Changes</button></div></form></section>
</div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

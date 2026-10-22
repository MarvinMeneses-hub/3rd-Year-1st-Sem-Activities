<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user=current_user(); $pdo=db(); $id=(int)($_GET['id']??$_POST['id']??0);
$s=$pdo->prepare("SELECT sub.id,sub.content,sub.file_path,sub.submitted_at,sub.score,sub.feedback,
    CONCAT(u.first_name,' ',u.last_name) student_name,u.id student_id,
    a.id assignment_id,a2.title assignment_title,a2.points max_points,
    c.id course_id,c.title course_title,c.course_code
    FROM submissions sub JOIN users u ON u.id=sub.student_id
    JOIN assignments a ON a.id=sub.assignment_id JOIN activities a2 ON a2.id=a.activity_id
    JOIN courses c ON c.id=a2.course_id JOIN teacher_courses tc ON tc.course_id=c.id
    WHERE sub.id=? AND tc.teacher_id=? LIMIT 1");
$s->execute([$id,$user['id']]); $row=$s->fetch();
if(!$row) exit('Submission not found or you do not have permission to grade it.');

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $score=(float)($_POST['score']??-1); $feedback=post('feedback');
    if($score < 0 || $score > (float)$row['max_points']){flash('error','Score must be between 0 and '.$row['max_points'].'.');redirect('teacher/grade_submission.php?id='.$id);}
    $pdo->prepare("UPDATE submissions SET score=?,feedback=?,graded_at=NOW() WHERE id=?")->execute([$score,$feedback,$id]);
    $pdo->prepare("INSERT INTO notifications(user_id,title,message,type) VALUES(?,?,?,'grade')")
        ->execute([$row['student_id'],'Assignment graded','Your submission for '.$row['assignment_title'].' in '.$row['course_code'].' has been graded: '.$score.' / '.$row['max_points'].'.']);
    flash('success','Grade saved and student notified.'); redirect('teacher/submissions.php');
}

$page_title='Grade Submission'; require __DIR__.'/../includes/header.php';
?>
<div class="app"><aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Teacher</span></div></div><nav class="nav"><a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="activities.php"><i class="fa-solid fa-list-check"></i>Activities</a><a href="submissions.php" class="active"><i class="fa-solid fa-file-circle-check"></i>Submissions</a><a href="analytics.php"><i class="fa-solid fa-chart-line"></i>Analytics</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Grade Submission</h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Teacher</span></div></div></header>
<div class="content">
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow"><?=e($row['course_code'])?></span><h2><?=e($row['assignment_title'])?></h2></div><a class="btn btn-soft" href="submissions.php">Back</a></div>
<div class="grid two"><div><p><strong>Student:</strong> <?=e($row['student_name'])?></p><p><strong>Submitted:</strong> <?=e($row['submitted_at']??'—')?></p><?php if (!empty($row['file_path'])): ?><div class="notice"><i class="fa-solid fa-paperclip"></i> <a href="<?=e(APP_URL.'/'.$row['file_path'])?>" target="_blank" rel="noopener">Open student attachment</a></div><?php endif; ?><h3>Student Submission</h3><div class="card card-pad" style="white-space:pre-wrap"><?=e($row['content'])?></div></div>
<div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><div class="form-group"><label>Score (0 - <?=e((string)$row['max_points'])?>)</label><input class="input" type="number" min="0" max="<?=e((string)$row['max_points'])?>" step="0.01" name="score" value="<?=e($row['score']!==null?(string)$row['score']:'')?>" required></div><br><div class="form-group"><label>Feedback</label><textarea class="textarea" name="feedback"><?=e($row['feedback']??'')?></textarea></div><div class="form-actions"><button class="btn btn-primary">Save Grade</button></div></form></div></div>
</section></div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

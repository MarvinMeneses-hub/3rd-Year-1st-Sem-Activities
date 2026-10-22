<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');

$user=current_user();
$id=(int)($_GET['id']??$_POST['id']??0);
$pdo=db();

$stmt=$pdo->prepare("SELECT a.*,c.title AS course_title,c.course_code,
    ass.id AS assignment_id,
    q.id AS quiz_id,q.time_limit_minutes,q.attempts_allowed
    FROM activities a
    INNER JOIN courses c ON c.id=a.course_id
    LEFT JOIN assignments ass ON ass.activity_id=a.id
    LEFT JOIN quizzes q ON (q.activity_id=a.id OR (q.activity_id IS NULL AND q.course_id=a.course_id AND q.title=a.title))
    WHERE a.id=?
      AND (
          EXISTS (SELECT 1 FROM enrollments e WHERE e.course_id=c.id AND e.student_id=? AND e.status IN ('active','completed'))
          OR EXISTS (SELECT 1 FROM course_enrollments ce WHERE ce.course_id=c.id AND ce.user_id=? AND ce.role='student' AND ce.status='accepted')
      )
    LIMIT 1");
$stmt->execute([$id,$user['id'],$user['id']]);
$a=$stmt->fetch();
if(!$a) exit('Activity not found or you are not enrolled.');

$page_title=$a['title'];
require __DIR__.'/../includes/header.php';
$icons=['assignment'=>'fa-file-pen','quiz'=>'fa-circle-question','project'=>'fa-diagram-project','practical'=>'fa-flask','discussion'=>'fa-comments'];
?>
<div class="app">
<aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Student</span></div></div>
<nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a>
<a href="activities.php" class="active"><i class="fa-solid fa-list-check"></i>Activities</a>
<a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a>
<a href="grades.php"><i class="fa-solid fa-chart-line"></i>Grades</a>
<a href="gamification.php"><i class="fa-solid fa-trophy"></i>Achievements</a>
<a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a>
<a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav></aside>

<main class="main">
<header class="topbar"><div><span class="eyebrow">Student portal</span><h1>Activity</h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Student</span></div></div></header>

<div class="content">
<section class="card card-pad">
<span class="eyebrow"><?=e($a['course_code'])?> · <?=e(ucfirst($a['activity_type']))?></span>
<h2><?=e($a['title'])?></h2>
<p class="muted"><?=nl2br(e($a['instructions']??''))?></p>
<div class="list"><div class="list-row"><div class="stat-icon"><i class="fa-solid <?=e($icons[$a['activity_type']]??'fa-list-check')?>"></i></div><div class="list-main"><strong><?=e((string)$a['points'])?> points</strong><span><?= $a['due_at'] ? 'Due '.e(date('M d, Y h:i A',strtotime($a['due_at']))) : 'No due date' ?></span></div></div></div><br>

<?php if($a['activity_type']==='quiz'): ?>
    <?php if(!empty($a['quiz_id'])): ?>
        <div class="notice"><i class="fa-solid fa-circle-question"></i> This is a quiz. Open it to answer the questions and submit your attempt.</div>
        <a class="btn btn-primary" href="quiz.php?id=<?=$a['quiz_id']?>&activity_id=<?=$a['id']?>"><i class="fa-solid fa-circle-question"></i> Open Quiz</a>
    <?php else: ?>
        <div class="notice">This quiz activity has not been connected to a quiz question set yet.</div>
    <?php endif; ?>
<?php elseif($a['assignment_id']): ?>
    <a class="btn btn-primary" href="submit.php?id=<?=$a['assignment_id']?>"><i class="fa-solid fa-paper-plane"></i> Submit Work</a>
<?php else: ?>
    <div class="notice">This activity has not been configured for student submission yet.</div>
<?php endif; ?>
</section>
</div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

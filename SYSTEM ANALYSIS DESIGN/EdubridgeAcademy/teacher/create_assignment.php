<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$courseId=(int)($_GET['course_id']??$_POST['course_id']??0);
if($courseId){ redirect('teacher/activity_form.php?course_id='.$courseId); }

$user=current_user(); $pdo=db();
$s=$pdo->prepare("SELECT c.id,c.title,c.course_code FROM courses c JOIN teacher_courses tc ON tc.course_id=c.id WHERE tc.teacher_id=? ORDER BY c.title");
$s->execute([$user['id']]); $courses=$s->fetchAll();

$page_title='Create Activity';
require __DIR__.'/../includes/header.php';
?>
<div class="app"><aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Teacher</span></div></div><nav class="nav"><a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a><a href="activities.php" class="active"><i class="fa-solid fa-list-check"></i>Activities</a><a href="submissions.php"><i class="fa-solid fa-file-circle-check"></i>Submissions</a><a href="analytics.php"><i class="fa-solid fa-chart-line"></i>Analytics</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Create Activity</h1></div></header><div class="content">
<section class="card card-pad"><div class="section-head"><div><h2>Select a Course</h2><p class="muted">Choose one of your accepted courses to create an assignment, quiz, project, or other activity.</p></div><a class="btn btn-soft" href="activities.php">Back</a></div>
<div class="grid two"><?php foreach($courses as $c):?><a class="card card-pad" style="text-decoration:none" href="activity_form.php?course_id=<?=$c['id']?>"><span class="eyebrow"><?=e($c['course_code'])?></span><h3><?=e($c['title'])?></h3><span class="btn btn-primary">Create Activity</span></a><?php endforeach;?><?php if(!$courses):?><div class="empty">No accepted courses are available.</div><?php endif;?></div>
</section></div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

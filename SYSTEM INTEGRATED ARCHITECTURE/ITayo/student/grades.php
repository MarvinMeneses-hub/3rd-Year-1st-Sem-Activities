<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
$user=current_user();
$page_title='Grades';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Student</span></div></div>
<nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a>
<a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a>
<a href="grades.php"><i class="fa-solid fa-chart-line"></i>Grades</a>
<a href="gamification.php"><i class="fa-solid fa-trophy"></i>Achievements</a>
<a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a>
<a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Student portal</span><h1>Grades</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Student</span></div></div></header>
<div class="content"><?php $s=db()->prepare("SELECT s.score,s.graded_at,a.title activity_title,c.title course_title FROM submissions s JOIN assignments ass ON ass.id=s.assignment_id JOIN activities a ON a.id=ass.activity_id JOIN courses c ON c.id=a.course_id WHERE s.student_id=? ORDER BY s.graded_at DESC");$s->execute([$user['id']]);$rows=$s->fetchAll();?>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Performance</span><h2>Grades</h2></div></div><div class="table-wrap"><table class="table"><thead><tr><th>Course</th><th>Activity</th><th>Score</th><th>Graded</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['course_title'])?></td><td><?=e($r['activity_title'])?></td><td><?= $r['score']===null?'Pending':e((string)$r['score']) ?></td><td><?=e($r['graded_at']??'—')?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="4" class="empty">No graded submissions yet.</td></tr><?php endif;?></tbody></table></div></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

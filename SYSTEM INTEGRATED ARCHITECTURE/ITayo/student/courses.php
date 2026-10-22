<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
$user=current_user();
$page_title='My Courses';
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
<header class="topbar"><div><span class="eyebrow">Student portal</span><h1>My Courses</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Student</span></div></div></header>
<div class="content"><?php $s=db()->prepare("SELECT DISTINCT c.*,s.name subject_name,sec.name section_name,sec.school_year FROM courses c LEFT JOIN enrollments e ON e.course_id=c.id AND e.student_id=? AND e.status IN ('active','completed') LEFT JOIN course_enrollments ce ON ce.course_id=c.id AND ce.user_id=? AND ce.role='student' AND ce.status='accepted' LEFT JOIN subjects s ON s.id=c.subject_id LEFT JOIN sections sec ON sec.id=c.section_id WHERE e.id IS NOT NULL OR ce.id IS NOT NULL ORDER BY c.title");$s->execute([$user['id'],$user['id']]);$rows=$s->fetchAll();?>
<div class="section-head"><div><span class="eyebrow">Learning</span><h2>My Courses</h2></div></div><div class="grid three"><?php foreach($rows as $r):?><a class="card card-pad" style="text-decoration:none" href="course.php?id=<?=$r['id']?>"><span class="eyebrow"><?=e($r['course_code'])?></span><h3><?=e($r['title'])?></h3><p class="muted"><?=e($r['subject_name']??'')?></p><?php if(!empty($r['section_name'])):?><small class="muted"><i class="fa-solid fa-layer-group"></i> <?=e($r['section_name'])?> · <?=e($r['school_year'])?></small><?php endif;?></a><?php endforeach;?></div>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
$user=current_user();
$page_title='Announcements';
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
<header class="topbar"><div><span class="eyebrow">Student portal</span><h1>Announcements</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Student</span></div></div></header>
<div class="content"><?php $s=db()->prepare("SELECT a.*,c.title course_title FROM announcements a JOIN courses c ON c.id=a.course_id JOIN enrollments e ON e.course_id=c.id WHERE e.student_id=? ORDER BY a.created_at DESC");$s->execute([$user['id']]);$rows=$s->fetchAll();?>
<div class="section-head"><div><span class="eyebrow">Updates</span><h2>Announcements</h2></div></div><div class="list"><?php foreach($rows as $r):?><article class="card card-pad"><span class="eyebrow"><?=e($r['course_title'])?></span><h3><?=e($r['title'])?></h3><p class="muted"><?=e($r['content'])?></p><small class="muted"><?=e($r['created_at'])?></small></article><?php endforeach;?><?php if(!$rows):?><div class="card empty">No announcements yet.</div><?php endif;?></div>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

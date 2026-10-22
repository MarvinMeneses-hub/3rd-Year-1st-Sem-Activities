<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
$user=current_user();
$page_title='Students';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Teacher</span></div></div>
<nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a>
<a href="activities.php"><i class="fa-solid fa-list-check"></i>Activities</a>
<a href="submissions.php"><i class="fa-solid fa-file-circle-check"></i>Submissions</a>
<a href="analytics.php"><i class="fa-solid fa-chart-line"></i>Analytics</a>
<a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a>
<a href="students.php"><i class="fa-solid fa-user-graduate"></i>Students</a>
<a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a>
<a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Students</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Teacher</span></div></div></header>
<div class="content"><?php $s=db()->prepare("SELECT DISTINCT u.id,u.first_name,u.last_name,u.email,sec.name section_name FROM users u JOIN enrollments e ON e.student_id=u.id JOIN teacher_courses tc ON tc.course_id=e.course_id LEFT JOIN courses c ON c.id=e.course_id LEFT JOIN sections sec ON sec.id=c.section_id WHERE tc.teacher_id=? ORDER BY u.last_name,u.first_name");$s->execute([$user['id']]);$rows=$s->fetchAll();?>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">People</span><h2>My Students</h2></div></div><div class="table-wrap"><table class="table"><thead><tr><th>Student</th><th>Email</th><th>Section</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><b><?=e($r['first_name'].' '.$r['last_name'])?></b></td><td><?=e($r['email'])?></td><td><?=e($r['section_name']??'—')?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="3" class="empty">No students enrolled in your courses.</td></tr><?php endif;?></tbody></table></div></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
$user=current_user();
$page_title='Dashboard';
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
<a href="../chat/"><i class="fa-solid fa-comments"></i>Messages</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Dashboard</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Teacher</span></div></div></header>
<div class="content"><?php
$pdo=db();$s=$pdo->prepare("SELECT COUNT(*) FROM teacher_courses WHERE teacher_id=?");$s->execute([$user['id']]);$courses=(int)$s->fetchColumn();
$s=$pdo->prepare("SELECT COUNT(DISTINCT e.student_id) FROM enrollments e JOIN teacher_courses tc ON tc.course_id=e.course_id WHERE tc.teacher_id=?");$s->execute([$user['id']]);$students=(int)$s->fetchColumn();
$s=$pdo->prepare("SELECT COUNT(*) FROM activities a JOIN teacher_courses tc ON tc.course_id=a.course_id WHERE tc.teacher_id=?");$s->execute([$user['id']]);$activities=(int)$s->fetchColumn();
$s=$pdo->prepare("SELECT c.* FROM courses c JOIN teacher_courses tc ON tc.course_id=c.id WHERE tc.teacher_id=? ORDER BY c.created_at DESC LIMIT 5");$s->execute([$user['id']]);$rows=$s->fetchAll();
?>
<?php
$invStmt=$pdo->prepare("SELECT COUNT(*) FROM course_enrollments WHERE user_id=? AND role=? AND status='pending'");
$invStmt->execute([$user['id'], 'teacher']);
$pendingInvitations=(int)$invStmt->fetchColumn();
$notifStmt=$pdo->prepare("SELECT id,title,message,created_at FROM notifications WHERE user_id=? AND is_read=0 ORDER BY created_at DESC LIMIT 5");
$notifStmt->execute([$user['id']]);
$notifications=$notifStmt->fetchAll();
?>
<?php if($pendingInvitations || $notifications): ?>
<section class="card card-pad" style="margin-bottom:18px"><div class="section-head"><div><span class="eyebrow">Notifications</span><h3>Action required</h3></div><span class="badge purple"><?=number_format($pendingInvitations + count($notifications))?> new</span></div>
<?php if($pendingInvitations): ?><div class="notice"><i class="fa-solid fa-envelope-open-text"></i> You have <b><?=number_format($pendingInvitations)?></b> pending course invitation(s). <a href="invitations.php">Review invitations</a>.</div><?php endif; ?>
<?php foreach($notifications as $n): ?><div class="list-row"><div class="stat-icon"><i class="fa-solid fa-bell"></i></div><div class="list-main"><strong><?=e($n['title'])?></strong><span><?=e($n['message'])?></span></div><small class="muted"><?=e(date('M d, h:i A',strtotime($n['created_at'])))?></small></div><?php endforeach; ?>
</section>
<?php endif; ?>
<section class="card welcome"><div><span class="eyebrow">Teacher portal</span><h2>Welcome, <?=e($user['first_name'])?>.</h2><p class="muted">Manage your courses, learning materials and students.</p></div></section>
<div class="grid three"><div class="card stat"><div class="stat-icon"><i class="fa-solid fa-book-open"></i></div><div><small>My Courses</small><strong><?=$courses?></strong></div></div><div class="card stat"><div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div><div><small>Students</small><strong><?=$students?></strong></div></div><div class="card stat"><div class="stat-icon"><i class="fa-solid fa-list-check"></i></div><div><small>Activities</small><strong><?=$activities?></strong></div></div></div><br>
<section class="card card-pad"><div class="section-head"><h3>Teaching Tools</h3><div class="actions"><a class="btn btn-soft" href="activities.php">Activities</a><a class="btn btn-soft" href="submissions.php">Submissions</a><a class="btn btn-soft" href="analytics.php">Analytics</a></div></div></section><br>
<section class="card card-pad"><div class="section-head"><h3>My Courses</h3><a class="btn btn-soft" href="courses.php">View all</a></div><div class="list"><?php foreach($rows as $r):?><a class="list-row" style="text-decoration:none" href="course.php?id=<?=$r['id']?>"><div class="stat-icon"><i class="fa-solid fa-book-open"></i></div><div class="list-main"><strong><?=e($r['title'])?></strong><span><?=e($r['course_code'])?></span></div><i class="fa-solid fa-chevron-right muted"></i></a><?php endforeach;?></div></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

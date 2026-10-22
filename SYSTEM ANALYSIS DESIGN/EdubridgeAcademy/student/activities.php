<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');

$user = current_user();
$pdo = db();
$courseId = (int)($_GET['course_id'] ?? 0);

$sql = "SELECT a.id,a.course_id,a.module_id,a.activity_type,a.title,a.instructions,a.points,a.due_at,
               c.title AS course_title,c.course_code,m.title AS module_title
        FROM activities a
        INNER JOIN courses c ON c.id=a.course_id
        LEFT JOIN modules m ON m.id=a.module_id
        WHERE (
            EXISTS (SELECT 1 FROM enrollments e WHERE e.course_id=c.id AND e.student_id=? AND e.status IN ('active','completed'))
            OR EXISTS (SELECT 1 FROM course_enrollments ce WHERE ce.course_id=c.id AND ce.user_id=? AND ce.role='student' AND ce.status='accepted')
        )";
$params = [$user['id'],$user['id']];
if ($courseId > 0) {
    $sql .= " AND c.id=?";
    $params[] = $courseId;
}
$sql .= " ORDER BY a.due_at IS NULL ASC,a.due_at ASC,c.title,a.created_at DESC,a.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll();

$icons = ['assignment'=>'fa-file-pen','quiz'=>'fa-circle-question','project'=>'fa-diagram-project','practical'=>'fa-flask','discussion'=>'fa-comments'];
$page_title='Activities';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Student</span></div></div>
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
<header class="topbar"><div><span class="eyebrow">Student portal</span><h1>Activities</h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Student</span></div></div></header>
<div class="content">
<div class="section-head"><div><span class="eyebrow">Assessment</span><h2><?= $courseId ? 'Course Activities' : 'My Activities' ?></h2><p class="muted">Assignments, quizzes, projects and tasks assigned by your teachers.</p></div><a class="btn btn-soft" href="courses.php"><i class="fa-solid fa-book-open"></i> My Courses</a></div>
<section class="card card-pad">
<div class="list">
<?php foreach ($activities as $a): ?>
<a class="list-row" style="text-decoration:none" href="activity.php?id=<?=$a['id']?>">
<div class="stat-icon"><i class="fa-solid <?=e($icons[$a['activity_type']] ?? 'fa-list-check')?>"></i></div>
<div class="list-main"><strong><?=e($a['title'])?></strong><span><?=e($a['course_code'])?> · <?=e($a['course_title'])?> · <?=e(ucfirst($a['activity_type']))?> · <?=e((string)$a['points'])?> points<?= $a['module_title'] ? ' · '.e($a['module_title']) : '' ?></span></div>
<div style="text-align:right"><small class="muted"><?= $a['due_at'] ? 'Due '.e(date('M d, Y h:i A',strtotime($a['due_at']))) : 'No due date' ?></small><br><i class="fa-solid fa-chevron-right muted"></i></div>
</a>
<?php endforeach; ?>
<?php if (!$activities): ?><div class="empty"><i class="fa-solid fa-list-check"></i><br>No activities have been assigned to your enrolled courses yet.</div><?php endif; ?>
</div>
</section>
</div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

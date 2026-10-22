<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');

$user = current_user();
$pdo = db();

$s=$pdo->prepare("SELECT COUNT(DISTINCT e.student_id) FROM enrollments e JOIN teacher_courses tc ON tc.course_id=e.course_id WHERE tc.teacher_id=? AND e.status='active'");
$s->execute([$user['id']]); $totalStudents=(int)$s->fetchColumn();

$s=$pdo->prepare("SELECT COUNT(*) FROM assignments a JOIN activities act ON act.id=a.activity_id JOIN teacher_courses tc ON tc.course_id=act.course_id WHERE tc.teacher_id=?");
$s->execute([$user['id']]); $totalAssignments=(int)$s->fetchColumn();

$s=$pdo->prepare("SELECT COUNT(*) FROM submissions sub JOIN assignments a ON a.id=sub.assignment_id JOIN activities act ON act.id=a.activity_id JOIN teacher_courses tc ON tc.course_id=act.course_id WHERE tc.teacher_id=?");
$s->execute([$user['id']]); $totalSubmissions=(int)$s->fetchColumn();

$s=$pdo->prepare("SELECT COUNT(*) FROM submissions sub JOIN assignments a ON a.id=sub.assignment_id JOIN activities act ON act.id=a.activity_id JOIN teacher_courses tc ON tc.course_id=act.course_id WHERE tc.teacher_id=? AND sub.score IS NOT NULL");
$s->execute([$user['id']]); $gradedSubmissions=(int)$s->fetchColumn();

$s=$pdo->prepare("SELECT COALESCE(AVG(sub.score),0) FROM submissions sub JOIN assignments a ON a.id=sub.assignment_id JOIN activities act ON act.id=a.activity_id JOIN teacher_courses tc ON tc.course_id=act.course_id WHERE tc.teacher_id=? AND sub.score IS NOT NULL");
$s->execute([$user['id']]); $averageGrade=round((float)$s->fetchColumn(),2);

$s=$pdo->prepare("SELECT c.id,c.title,c.course_code,
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id AND e.status='active') students,
    (SELECT COUNT(*) FROM assignments a JOIN activities act2 ON act2.id=a.activity_id WHERE act2.course_id=c.id) assignments,
    (SELECT COUNT(*) FROM submissions sub JOIN assignments a2 ON a2.id=sub.assignment_id JOIN activities act3 ON act3.id=a2.activity_id WHERE act3.course_id=c.id) submissions
    FROM courses c JOIN teacher_courses tc ON tc.course_id=c.id
    WHERE tc.teacher_id=? ORDER BY c.title");
$s->execute([$user['id']]); $courses=$s->fetchAll();

$page_title='Analytics';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar"><div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Teacher</span></div></div>
<nav class="nav"><a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a><a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a><a href="activities.php"><i class="fa-solid fa-list-check"></i>Activities</a><a href="submissions.php"><i class="fa-solid fa-file-circle-check"></i>Submissions</a><a href="analytics.php" class="active"><i class="fa-solid fa-chart-line"></i>Analytics</a><a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a><a href="students.php"><i class="fa-solid fa-user-graduate"></i>Students</a><a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav></aside>
<main class="main"><header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Analytics</h1></div><div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Teacher</span></div></div></header>
<div class="content">
<section class="card welcome"><div><span class="eyebrow">Performance overview</span><h2>Teacher Analytics</h2><p class="muted">Overview of students, assignments, submissions and grades across your accepted courses.</p></div><div class="stat-icon"><i class="fa-solid fa-chart-line"></i></div></section><br>
<div class="grid stats">
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div><div><small>Total Students</small><strong><?=$totalStudents?></strong></div></div>
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-list-check"></i></div><div><small>Total Assignments</small><strong><?=$totalAssignments?></strong></div></div>
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-file-arrow-up"></i></div><div><small>Total Submissions</small><strong><?=$totalSubmissions?></strong></div></div>
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-check-double"></i></div><div><small>Graded Submissions</small><strong><?=$gradedSubmissions?></strong></div></div>
</div><br>
<section class="card card-pad"><div class="section-head"><h3>Average Grade</h3><span class="badge green"><?=$averageGrade?>%</span></div><div style="font-size:2rem;font-weight:700"><?=number_format($averageGrade,2)?>%</div><p class="muted">Calculated from graded submissions in your courses.</p></section><br>
<section class="card card-pad"><div class="section-head"><h3>Course Statistics</h3><a class="btn btn-soft" href="courses.php">My Courses</a></div>
<div class="table-wrap"><table class="table"><thead><tr><th>Course</th><th>Students</th><th>Assignments</th><th>Submissions</th></tr></thead><tbody>
<?php foreach($courses as $c):?><tr><td><b><?=e($c['title'])?></b><br><small class="muted"><?=e($c['course_code'])?></small></td><td><?=number_format($c['students'])?></td><td><?=number_format($c['assignments'])?></td><td><?=number_format($c['submissions'])?></td></tr><?php endforeach;?>
<?php if(!$courses):?><tr><td colspan="4" class="empty">No accepted courses yet.</td></tr><?php endif;?>
</tbody></table></div></section>
</div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

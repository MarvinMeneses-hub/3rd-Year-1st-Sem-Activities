<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');

$user=current_user();
$page_title='Dashboard';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Student</span></div></div>
<nav class="nav">
<a href="dashboard.php" class="active"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a>
<a href="activities.php"><i class="fa-solid fa-list-check"></i>Activities</a>
<a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a>
<a href="grades.php"><i class="fa-solid fa-chart-line"></i>Grades</a>
<a href="gamification.php"><i class="fa-solid fa-trophy"></i>Achievements</a>
<a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a>
<a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<a href="../chat/"><i class="fa-solid fa-comments"></i>Messages</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav>
</aside>

<main class="main">
<header class="topbar"><div><span class="eyebrow">Student portal</span><h1>Dashboard</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Student</span></div></div></header>

<div class="content">
<?php
$pdo=db();

$s=$pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id=? AND status='active'");
$s->execute([$user['id']]);
$courses=(int)$s->fetchColumn();

$s=$pdo->prepare("SELECT COUNT(*) FROM progress WHERE student_id=? AND completed=1");
$s->execute([$user['id']]);
$completed=(int)$s->fetchColumn();

$s=$pdo->prepare("SELECT COUNT(*) FROM submissions WHERE student_id=? AND submitted_at IS NOT NULL");
$s->execute([$user['id']]);
$submissions=(int)$s->fetchColumn();

$s=$pdo->prepare("SELECT c.* FROM courses c JOIN enrollments e ON e.course_id=c.id WHERE e.student_id=? AND e.status IN ('active','completed') ORDER BY e.enrolled_at DESC LIMIT 6");
$s->execute([$user['id']]);
$rows=$s->fetchAll();

$invStmt=$pdo->prepare("SELECT COUNT(*) FROM course_enrollments WHERE user_id=? AND role=? AND status='pending'");
$invStmt->execute([$user['id'], 'student']);
$pendingInvitations=(int)$invStmt->fetchColumn();

$notifStmt=$pdo->prepare("SELECT id,title,message,created_at FROM notifications WHERE user_id=? AND is_read=0 ORDER BY created_at DESC LIMIT 5");
$notifStmt->execute([$user['id']]);
$notifications=$notifStmt->fetchAll();

/*
 * Dashboard activities:
 * Show activities assigned in courses where the student is either enrolled
 * through the original enrollments table OR has accepted a course invitation.
 * This keeps the dashboard in sync with both enrollment workflows.
 */
$activitySql="
    SELECT
        a.id,
        a.title,
        a.activity_type,
        a.points,
        a.due_at,
        a.course_id,
        c.title AS course_title,
        c.course_code,
        COALESCE(sub.id, 0) AS submission_id,
        sub.submitted_at,
        COALESCE(qa.id, 0) AS quiz_attempt_id,
        qa.submitted_at AS quiz_submitted_at
    FROM activities a
    INNER JOIN courses c ON c.id=a.course_id
    LEFT JOIN assignments ass ON ass.activity_id=a.id
    LEFT JOIN submissions sub ON sub.assignment_id=ass.id AND sub.student_id=?
    LEFT JOIN quizzes q ON q.activity_id=a.id
    LEFT JOIN quiz_attempts qa ON qa.quiz_id=q.id AND qa.student_id=?
    WHERE (
        EXISTS (
            SELECT 1 FROM enrollments e
            WHERE e.course_id=a.course_id
              AND e.student_id=?
              AND e.status IN ('active','completed')
        )
        OR EXISTS (
            SELECT 1 FROM course_enrollments ce
            WHERE ce.course_id=a.course_id
              AND ce.user_id=?
              AND ce.role='student'
              AND ce.status='accepted'
        )
    )
    ORDER BY
        CASE WHEN a.due_at IS NULL THEN 1 ELSE 0 END,
        a.due_at ASC,
        a.id DESC
    LIMIT 6
";
$activityStmt=$pdo->prepare($activitySql);
$activityStmt->execute([$user['id'],$user['id'],$user['id'],$user['id']]);
$dashboardActivities=$activityStmt->fetchAll();

$activityIcons=[
    'assignment'=>'fa-file-pen',
    'quiz'=>'fa-circle-question',
    'project'=>'fa-diagram-project',
    'practical'=>'fa-flask',
    'discussion'=>'fa-comments'
];
?>

<?php if($pendingInvitations || $notifications): ?>
<section class="card card-pad" style="margin-bottom:18px">
<div class="section-head"><div><span class="eyebrow">Notifications</span><h3>Action required</h3></div><span class="badge purple"><?=number_format($pendingInvitations + count($notifications))?> new</span></div>
<?php if($pendingInvitations): ?><div class="notice"><i class="fa-solid fa-envelope-open-text"></i> You have <b><?=number_format($pendingInvitations)?></b> pending course invitation(s). <a href="invitations.php">Review invitations</a>.</div><?php endif; ?>
<?php foreach($notifications as $n): ?><div class="list-row"><div class="stat-icon"><i class="fa-solid fa-bell"></i></div><div class="list-main"><strong><?=e($n['title'])?></strong><span><?=e($n['message'])?></span></div><small class="muted"><?=e(date('M d, h:i A',strtotime($n['created_at'])))?></small></div><?php endforeach; ?>
</section>
<?php endif; ?>

<section class="card welcome"><div><span class="eyebrow">Student portal</span><h2>Welcome, <?=e($user['first_name'])?>.</h2><p class="muted">Continue your learning journey.</p></div></section>

<div class="grid three">
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-book-open"></i></div><div><small>My Courses</small><strong><?=$courses?></strong></div></div>
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div><div><small>Completed Lessons</small><strong><?=$completed?></strong></div></div>
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-file-arrow-up"></i></div><div><small>Submissions</small><strong><?=$submissions?></strong></div></div>
</div>
<br>

<!-- Restored Student Activities -->
<section class="card card-pad">
<div class="section-head">
<div><span class="eyebrow">Assessment</span><h3>Student Activities</h3></div>
<a class="btn btn-soft" href="activities.php"><i class="fa-solid fa-list-check"></i> View all</a>
</div>
<div class="list">
<?php foreach($dashboardActivities as $a):
    $icon=$activityIcons[$a['activity_type']] ?? 'fa-list-check';
    $completedActivity=false;
    if($a['activity_type']==='assignment') {
        $completedActivity=!empty($a['submission_id']) && !empty($a['submitted_at']);
    } elseif($a['activity_type']==='quiz') {
        $completedActivity=!empty($a['quiz_attempt_id']) && !empty($a['quiz_submitted_at']);
    }
?>
<a class="list-row" style="text-decoration:none" href="activity.php?id=<?=$a['id']?>">
<div class="stat-icon"><i class="fa-solid <?=e($icon)?>"></i></div>
<div class="list-main">
<strong><?=e($a['title'])?></strong>
<span><?=e($a['course_code'])?> · <?=e($a['course_title'])?> · <?=e(ucfirst($a['activity_type']))?> · <?=e((string)$a['points'])?> points</span>
</div>
<div style="text-align:right;min-width:105px">
<?php if($completedActivity): ?>
<span class="badge green">Submitted</span>
<?php elseif($a['due_at']): ?>
<small class="muted">Due <?=e(date('M d, Y',strtotime($a['due_at'])))?></small>
<?php else: ?>
<small class="muted">No due date</small>
<?php endif; ?>
</div>
</a>
<?php endforeach; ?>
<?php if(!$dashboardActivities): ?>
<div class="empty"><i class="fa-solid fa-list-check"></i><br>No activities have been assigned to your enrolled courses yet.</div>
<?php endif; ?>
</div>
</section>
<br>

<section class="card card-pad"><div class="section-head"><h3>Student Tools</h3><div class="actions"><a class="btn btn-soft" href="gamification.php"><i class="fa-solid fa-trophy"></i> Achievements</a><a class="btn btn-soft" href="grades.php"><i class="fa-solid fa-chart-line"></i> Grades</a></div></div></section>
<br>

<section class="card card-pad"><div class="section-head"><h3>Continue Learning</h3><a class="btn btn-soft" href="courses.php">All courses</a></div><div class="grid three"><?php foreach($rows as $r):?><a class="card card-pad" style="text-decoration:none" href="course.php?id=<?=$r['id']?>"><span class="eyebrow"><?=e($r['course_code'])?></span><h3><?=e($r['title'])?></h3><span class="badge <?=$r['status']==='published'?'green':'gray'?>"><?=e(ucfirst($r['status']))?></span></a><?php endforeach;?></div></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

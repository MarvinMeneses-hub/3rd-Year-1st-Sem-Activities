<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');

$user = current_user();
$pdo = db();

$s = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE student_id=? AND submitted_at IS NOT NULL");
$s->execute([$user['id']]);
$totalSubmissions = (int)$s->fetchColumn();

$s = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE student_id=? AND score IS NOT NULL");
$s->execute([$user['id']]);
$totalGraded = (int)$s->fetchColumn();

$submissionPoints = $totalSubmissions * 10;
$gradingPoints = $totalGraded * 5;
$totalPoints = $submissionPoints + $gradingPoints;

$badges = [
    ['fa-flag-checkered','Getting Started','Submit your first assignment.',$totalSubmissions >= 1],
    ['fa-book-open-reader','Active Learner','Submit 3 assignments.',$totalSubmissions >= 3],
    ['fa-star','Dedicated Student','Submit 5 assignments.',$totalSubmissions >= 5],
    ['fa-graduation-cap','Achievement Unlocked','Get 3 assignments graded.',$totalGraded >= 3],
    ['fa-trophy','Edubridge Star','Get 5 assignments graded.',$totalGraded >= 5],
];

$page_title='Achievements';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Student</span></div></div>
<nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a>
<a href="invitations.php"><i class="fa-solid fa-envelope-open-text"></i>Course Invitations</a>
<a href="grades.php"><i class="fa-solid fa-chart-line"></i>Grades</a>
<a href="gamification.php" class="active"><i class="fa-solid fa-trophy"></i>Achievements</a>
<a href="announcements.php"><i class="fa-solid fa-bullhorn"></i>Announcements</a>
<a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a>
</nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Student portal</span><h1>Achievements</h1></div>
<div class="user-chip"><div class="avatar"><?=e(strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)))?></div><div><b><?=e($user['first_name'].' '.$user['last_name'])?></b><span>Student</span></div></div></header>
<div class="content">
<section class="card welcome"><div><span class="eyebrow">Gamification</span><h2>My Achievements</h2><p class="muted">Complete assignments and get your work graded to earn points and unlock badges.</p></div><div class="stat-icon"><i class="fa-solid fa-trophy"></i></div></section><br>
<div class="grid three">
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-star"></i></div><div><small>Total Points</small><strong><?=$totalPoints?></strong></div></div>
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-file-arrow-up"></i></div><div><small>Assignments Submitted</small><strong><?=$totalSubmissions?></strong></div></div>
<div class="card stat"><div class="stat-icon"><i class="fa-solid fa-check-double"></i></div><div><small>Assignments Graded</small><strong><?=$totalGraded?></strong></div></div>
</div><br>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Badges</span><h3>Milestones</h3></div></div>
<div class="grid three">
<?php foreach($badges as [$icon,$title,$desc,$unlocked]): ?>
<article class="card card-pad" style="text-align:center;opacity:<?=$unlocked?'1':'.58'?>">
<div class="stat-icon" style="margin:0 auto 14px"><i class="fa-solid <?=$icon?>"></i></div>
<h3><?=e($title)?></h3><p class="muted"><?=e($desc)?></p>
<span class="badge <?=$unlocked?'green':'gray'?>"><?=$unlocked?'Unlocked':'Locked'?></span>
</article>
<?php endforeach; ?>
</div></section><br>
<section class="card card-pad"><h3>How to Earn Points</h3><div class="list">
<div class="list-row"><div class="stat-icon"><i class="fa-solid fa-file-arrow-up"></i></div><div class="list-main"><strong>+10 points</strong><span>Submit an assignment</span></div></div>
<div class="list-row"><div class="stat-icon"><i class="fa-solid fa-check"></i></div><div class="list-main"><strong>+5 points</strong><span>Get an assignment graded</span></div></div>
</div></section>
</div></main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

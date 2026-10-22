<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
$user=current_user();
$page_title='Lesson';
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
<header class="topbar"><div><span class="eyebrow">Student portal</span><h1>Lesson</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Student</span></div></div></header>
<div class="content"><?php
$id=(int)($_GET['id']??0);$s=db()->prepare("SELECT l.*,m.course_id,c.title course_title FROM lessons l JOIN modules m ON m.id=l.module_id JOIN courses c ON c.id=m.course_id JOIN enrollments e ON e.course_id=c.id WHERE l.id=? AND e.student_id=? AND l.is_published=1");$s->execute([$id,$user['id']]);$lesson=$s->fetch();if(!$lesson)exit('Lesson not found.');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();db()->prepare("INSERT INTO progress(student_id,course_id,lesson_id,completed,completed_at) VALUES(?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE completed=1,completed_at=NOW()")->execute([$user['id'],$lesson['course_id'],$id,1]);flash('success','Lesson marked complete.');redirect('student/lesson.php?id='.$id);}
?>
<section class="card card-pad"><span class="eyebrow"><?=e($lesson['course_title'])?></span><h2><?=e($lesson['title'])?></h2><hr><div style="line-height:1.8"><?= $lesson['content'] ?></div><br><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button class="btn btn-primary"><i class="fa-solid fa-check"></i> Mark as complete</button></form></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

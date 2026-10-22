<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
$user=current_user();
$page_title='Lesson Editor';
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
<header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Lesson Editor</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Teacher</span></div></div></header>
<div class="content"><?php
$module_id=(int)($_GET['module_id']??$_POST['module_id']??0);$s=db()->prepare("SELECT m.*,c.title course_title FROM modules m JOIN courses c ON c.id=m.course_id JOIN teacher_courses tc ON tc.course_id=c.id WHERE m.id=? AND tc.teacher_id=?");$s->execute([$module_id,$user['id']]);$m=$s->fetch();if(!$m)exit('Module not found.');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$title=post('title');$content=(string)($_POST['content']??'');$published=(int)($_POST['is_published']??0);db()->prepare("INSERT INTO lessons(module_id,title,content,is_published) VALUES(?,?,?,?)")->execute([$module_id,$title,$content,$published]);flash('success','Lesson created.');redirect('teacher/course.php?id='.$m['course_id']);}
?>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow"><?=e($m['course_title'])?></span><h2>Add Lesson</h2></div><a class="btn btn-soft" href="course.php?id=<?=$m['course_id']?>">Back</a></div>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="module_id" value="<?=$module_id?>"><div class="form-group"><label>Lesson title</label><input class="input" name="title" required></div><br><div class="form-group"><label>Lesson content</label><textarea class="textarea" style="min-height:330px" name="content" placeholder="Write lesson content here..."></textarea></div><br><label><input type="checkbox" name="is_published" value="1"> Publish lesson</label><div class="form-actions"><button class="btn btn-primary">Save Lesson</button></div></form></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

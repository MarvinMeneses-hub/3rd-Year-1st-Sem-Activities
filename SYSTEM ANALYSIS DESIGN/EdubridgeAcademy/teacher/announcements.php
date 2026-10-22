<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
$user=current_user();
$page_title='Announcements';
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
<header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Announcements</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Teacher</span></div></div></header>
<div class="content"><?php
$pdo=db();$s=$pdo->prepare("SELECT c.id,c.title FROM courses c JOIN teacher_courses tc ON tc.course_id=c.id WHERE tc.teacher_id=? ORDER BY c.title");$s->execute([$user['id']]);$courses=$s->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$cid=(int)$_POST['course_id'];$title=post('title');$content=post('content');$ok=$pdo->prepare("SELECT 1 FROM teacher_courses WHERE teacher_id=? AND course_id=?");$ok->execute([$user['id'],$cid]);if($ok->fetch()&&$title&&$content){$pdo->prepare("INSERT INTO announcements(course_id,author_id,title,content) VALUES(?,?,?,?)")->execute([$cid,$user['id'],$title,$content]);flash('success','Announcement posted.');redirect('teacher/announcements.php');}}
?>
<div class="grid two"><section class="card card-pad"><h3>New Announcement</h3><br><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-group"><label>Course</label><select class="select" name="course_id" required><?php foreach($courses as $c):?><option value="<?=$c['id']?>"><?=e($c['title'])?></option><?php endforeach;?></select></div><br><div class="form-group"><label>Title</label><input class="input" name="title" required></div><br><div class="form-group"><label>Message</label><textarea class="textarea" name="content" required></textarea></div><br><button class="btn btn-primary">Publish</button></form></section>
<section class="card card-pad"><h3>Recent Announcements</h3><br><?php $a=$pdo->prepare("SELECT a.*,c.title course_title FROM announcements a JOIN courses c ON c.id=a.course_id WHERE a.author_id=? ORDER BY a.created_at DESC");$a->execute([$user['id']]);foreach($a->fetchAll() as $x):?><div class="list-row"><div class="list-main"><strong><?=e($x['title'])?></strong><span><?=e($x['course_title'])?> · <?=e($x['content'])?></span></div><div class="actions"><a class="btn btn-soft" href="edit_announcement.php?id=<?=$x['id']?>">Edit</a><a class="btn btn-danger" data-confirm="Delete this announcement?" href="delete_announcement.php?id=<?=$x['id']?>">Delete</a></div></div><?php endforeach;?></section></div>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

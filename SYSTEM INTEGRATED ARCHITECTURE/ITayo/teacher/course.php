<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
$user=current_user();
$page_title='Course';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Teacher</span></div></div>
<nav class="nav">
<a href="dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="courses.php"><i class="fa-solid fa-book-open"></i>My Courses</a>
<a href="activities.php" class="active"><i class="fa-solid fa-list-check"></i>Activities</a>
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
<header class="topbar"><div><span class="eyebrow">Teacher portal</span><h1>Course</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Teacher</span></div></div></header>
<div class="content"><?php
$id=(int)($_GET['id']??0);$s=db()->prepare("SELECT c.* FROM courses c JOIN teacher_courses tc ON tc.course_id=c.id WHERE c.id=? AND tc.teacher_id=?");$s->execute([$id,$user['id']]);$course=$s->fetch();if(!$course)exit('Course not found.');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$title=post('title');$desc=post('description');$order=(int)($_POST['sort_order']??0);db()->prepare("INSERT INTO modules(course_id,title,description,sort_order) VALUES(?,?,?,?)")->execute([$id,$title,$desc,$order]);flash('success','Module added.');redirect('teacher/course.php?id='.$id);}
$m=db()->prepare("SELECT * FROM modules WHERE course_id=? ORDER BY sort_order,id");$m->execute([$id]);$modules=$m->fetchAll();
?>
<div class="section-head"><div><span class="eyebrow"><?=e($course['course_code'])?></span><h2><?=e($course['title'])?></h2><p class="muted"><?=e($course['description'])?></p></div><a class="btn btn-soft" href="courses.php">Back</a></div>
<div class="grid two"><section class="card card-pad"><h3>Course Modules</h3><br><div class="list"><?php foreach($modules as $mod):?><div class="list-row"><div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div><div class="list-main"><strong><?=e($mod['title'])?></strong><span><?=e($mod['description']??'')?></span></div><a class="btn btn-soft" href="lesson_form.php?module_id=<?=$mod['id']?>">Add Lesson</a></div><?php endforeach;?><?php if(!$modules):?><div class="empty">No modules yet.</div><?php endif;?></div></section>
<section class="card card-pad"><div class="section-head"><h3>Activities</h3><a class="btn btn-soft" href="activity_form.php?course_id=<?=$id?>">Add Activity</a></div>
<?php $as=$m=db(); $asStmt=$as->prepare("SELECT a.id,a.activity_id,a.allow_late,a2.title,a2.activity_type,a2.points,a2.due_at FROM assignments a JOIN activities a2 ON a2.id=a.activity_id WHERE a2.course_id=? ORDER BY a2.due_at IS NULL,a2.due_at ASC"); $asStmt->execute([$id]); foreach($asStmt->fetchAll() as $ass):?><div class="list-row"><div class="stat-icon"><i class="fa-solid fa-file-pen"></i></div><div class="list-main"><strong><?=e($ass['title'])?></strong><span><?=e(ucfirst($ass['activity_type']))?> · <?=e((string)$ass['points'])?> points · <?=e($ass['due_at']?date('M d, Y h:i A',strtotime($ass['due_at'])):'No due date')?></span></div></div><?php endforeach; if(!$asStmt->rowCount()):?><div class="empty">No assignments yet.</div><?php endif;?></section>
<section class="card card-pad"><h3>Add Module</h3><br><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-group"><label>Module title</label><input class="input" name="title" required></div><br><div class="form-group"><label>Description</label><textarea class="textarea" name="description"></textarea></div><br><div class="form-group"><label>Order</label><input class="input" type="number" name="sort_order" value="0"></div><br><button class="btn btn-primary">Add Module</button></form></section></div>
<section class="card card-pad" id="teacher-assignment-list">
<div class="section-head"><h3>Activities</h3><a class="btn btn-soft" href="activity_form.php?course_id=<?=$id?>"><i class="fa-solid fa-plus"></i>Add Activity</a></div>
<?php
$assignmentStmt=db()->prepare("SELECT a.id,a.activity_id,a.allow_late,a2.title,a2.activity_type,a2.points,a2.due_at FROM assignments a JOIN activities a2 ON a2.id=a.activity_id WHERE a2.course_id=? ORDER BY a2.due_at IS NULL,a2.due_at ASC");
$assignmentStmt->execute([$id]); $courseAssignments=$assignmentStmt->fetchAll();
?>
<div class="list">
<?php foreach($courseAssignments as $ass): ?>
<div class="list-row"><div class="stat-icon"><i class="fa-solid fa-file-pen"></i></div><div class="list-main"><strong><?=e($ass['title'])?></strong><span><?=e(ucfirst($ass['activity_type']))?> · <?=e((string)$ass['points'])?> points · <?=e($ass['due_at']?date('M d, Y h:i A',strtotime($ass['due_at'])):'No due date')?></span></div><a class="btn btn-soft" href="edit_assignment.php?id=<?=$ass['id']?>">Edit</a></div>
<?php endforeach; ?>
<?php if(!$courseAssignments): ?><div class="empty">No assignments yet.</div><?php endif; ?>
</div></section><br>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

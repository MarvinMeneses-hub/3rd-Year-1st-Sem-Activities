<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
$user=current_user();
$page_title='Courses';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Admin</span></div></div>
<nav class="nav"><a href="dashboard.php" class=""><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="users.php" class=""><i class="fa-solid fa-users"></i>Users</a>
<a href="courses.php" class="active"><i class="fa-solid fa-book-open"></i>Courses</a>
<a href="course_enrollments.php"><i class="fa-solid fa-user-plus"></i>Course Enrollment</a>
<a href="subjects.php" class=""><i class="fa-solid fa-book"></i>Subjects</a>
<a href="sections.php" class=""><i class="fa-solid fa-layer-group"></i>Sections</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Admin portal</span><h1>Courses</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Admin</span></div></div></header>
<div class="content"><?php
$pdo=db();$rows=$pdo->query("SELECT c.*,s.name subject_name,sec.name section_name,(SELECT COUNT(*) FROM teacher_courses tc WHERE tc.course_id=c.id) teacher_count FROM courses c LEFT JOIN subjects s ON s.id=c.subject_id LEFT JOIN sections sec ON sec.id=c.section_id ORDER BY c.created_at DESC")->fetchAll();
?>
<div class="section-head"><div><span class="eyebrow">Learning</span><h2>Courses</h2></div><a class="btn btn-primary" href="course_form.php"><i class="fa-solid fa-plus"></i>Create Course</a></div>
<section class="card card-pad"><div class="table-wrap"><table class="table"><thead><tr><th>Course</th><th>Code</th><th>Subject</th><th>Section</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><b><?=e($r['title'])?></b><br><small class="muted"><?=e($r['description']??'')?></small></td><td><?=e($r['course_code'])?></td><td><?=e($r['subject_name']??'—')?></td><td><?=e($r['section_name']??'—')?></td><td><span class="badge <?= $r['status']==='published'?'green':'gray'?>"><?=e(ucfirst($r['status']))?></span></td><td><a class="btn btn-soft" href="course_form.php?id=<?=$r['id']?>">Edit</a></td></tr><?php endforeach;?></tbody></table></div></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

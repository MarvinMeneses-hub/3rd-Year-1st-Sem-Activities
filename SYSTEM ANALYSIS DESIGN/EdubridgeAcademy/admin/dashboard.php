<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
$user=current_user();
$page_title='Dashboard';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>EdubridgeAcademy</strong><span>Admin</span></div></div>
<nav class="nav"><a href="dashboard.php" class="active"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="users.php" class=""><i class="fa-solid fa-users"></i>Users</a>
<a href="courses.php" class=""><i class="fa-solid fa-book-open"></i>Courses</a>
<a href="course_enrollments.php"><i class="fa-solid fa-user-plus"></i>Course Enrollment</a>
<a href="subjects.php" class=""><i class="fa-solid fa-book"></i>Subjects</a>
<a href="sections.php" class=""><i class="fa-solid fa-layer-group"></i>Sections</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<a href="../chat/"><i class="fa-solid fa-comments"></i>Messages</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Admin portal</span><h1>Dashboard</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Admin</span></div></div></header>
<div class="content"><?php
$pdo=db();
$stats=[
'users'=>(int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
'teachers'=>(int)$pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='teacher'")->fetchColumn(),
'students'=>(int)$pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='student'")->fetchColumn(),
'courses'=>(int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
'subjects'=>(int)$pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
'sections'=>(int)$pdo->query("SELECT COUNT(*) FROM sections")->fetchColumn(),
'enrollments'=>(int)$pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn(),
'pending_invites'=>(int)$pdo->query("SELECT COUNT(*) FROM course_enrollments WHERE status='pending'")->fetchColumn()];
$recent=$pdo->query("SELECT u.first_name,u.last_name,u.email,r.name role,u.status FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.created_at DESC LIMIT 6")->fetchAll();
?>
<section class="card welcome"><div><span class="eyebrow">Overview</span><h2>Welcome back, <?=e($user['first_name'])?>.</h2><p class="muted">Manage people, courses and the learning environment.</p></div><div class="stat-icon"><i class="fa-solid fa-chart-simple"></i></div></section>
<div class="grid stats">
<?php foreach([['users','Total Users','fa-users'],['teachers','Teachers','fa-chalkboard-user'],['students','Students','fa-user-graduate'],['courses','Courses','fa-book-open']] as $s):?><div class="card stat"><div class="stat-icon"><i class="fa-solid <?=$s[2]?>"></i></div><div><small><?=$s[1]?></small><strong><?=number_format($stats[$s[0]])?></strong><small>Current records</small></div></div><?php endforeach;?>
</div>
<br><div class="grid three">
<?php foreach([['subjects','Subjects','fa-book'],['sections','Sections','fa-layer-group'],['enrollments','Enrollments','fa-user-check']] as $s):?><div class="card stat"><div class="stat-icon"><i class="fa-solid <?=$s[2]?>"></i></div><div><small><?=$s[1]?></small><strong><?=number_format($stats[$s[0]])?></strong></div></div><?php endforeach;?>
</div><br>
<section class="card card-pad"><div class="section-head"><div><span class="eyebrow">Enrollment workflow</span><h3>Course Invitations</h3></div><a class="btn btn-soft" href="course_enrollments.php">Manage invitations</a></div><div class="grid three"><div class="stat"><div class="stat-icon"><i class="fa-solid fa-envelope-open-text"></i></div><div><small>Pending Invitations</small><strong><?=number_format($stats['pending_invites'])?></strong><small>Awaiting teacher/student response</small></div></div></div></section>
<br>
<section class="card card-pad"><div class="section-head"><h3>Recent Users</h3><a class="btn btn-soft" href="users.php">Manage users</a></div><div class="table-wrap"><table class="table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead><tbody>
<?php foreach($recent as $r):?><tr><td><b><?=e($r['first_name'].' '.$r['last_name'])?></b></td><td><?=e($r['email'])?></td><td><span class="badge"><?=e(ucfirst($r['role']))?></span></td><td><span class="badge <?= $r['status']==='active'?'green':'gray'?>"><?=e(ucfirst($r['status']))?></span></td></tr><?php endforeach;?>
</tbody></table></div></section>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

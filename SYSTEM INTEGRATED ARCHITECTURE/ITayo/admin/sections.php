<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
$user=current_user();
$page_title='Sections';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Admin</span></div></div>
<nav class="nav"><a href="dashboard.php" class=""><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="users.php" class=""><i class="fa-solid fa-users"></i>Users</a>
<a href="courses.php" class=""><i class="fa-solid fa-book-open"></i>Courses</a>
<a href="subjects.php" class=""><i class="fa-solid fa-book"></i>Subjects</a>
<a href="sections.php" class="active"><i class="fa-solid fa-layer-group"></i>Sections</a><a href="section_members.php"><i class="fa-solid fa-users-rectangle"></i>Section Members</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Admin portal</span><h1>Sections</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Admin</span></div></div></header>
<div class="content"><?php
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$name=post('name');$year=post('school_year');if($name){$pdo->prepare("INSERT INTO sections(name,school_year) VALUES(?,?)")->execute([$name,$year]);flash('success','Section created.');redirect('admin/sections.php');}}
$rows=$pdo->query("SELECT s.*, COUNT(DISTINCT CASE WHEN sm.role='student' AND sm.status='active' THEN sm.user_id END) student_count, COUNT(DISTINCT CASE WHEN sm.role='teacher' AND sm.status='active' THEN sm.user_id END) teacher_count FROM sections s LEFT JOIN section_members sm ON sm.section_id=s.id GROUP BY s.id ORDER BY s.school_year DESC,s.name")->fetchAll();
?>
<div class="section-head"><div><span class="eyebrow">Academic setup</span><h2>Sections</h2></div></div>
<div class="grid two"><section class="card card-pad"><h3>Add Section</h3><br><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-group"><label>Section name</label><input class="input" name="name" required></div><br><div class="form-group"><label>School year</label><input class="input" name="school_year" placeholder="2026-2027"></div><br><button class="btn btn-primary">Add Section</button></form></section>
<section class="card card-pad"><h3>Section List</h3><br><div class="list"><?php foreach($rows as $r):?><div class="list-row"><div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div><div class="list-main"><strong><?=e($r['name'])?></strong><span><?=e($r['school_year'])?> · <?=number_format((int)$r['student_count'])?> students · <?=number_format((int)$r['teacher_count'])?> teachers</span></div><a class="btn btn-soft" href="section_members.php?section_id=<?=$r['id']?>"><i class="fa-solid fa-users-rectangle"></i>Manage Roster</a></div><?php endforeach;?></div></section></div>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>

<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
$user=current_user();
$page_title='Subjects';
require __DIR__.'/../includes/header.php';
?>
<div class="app">
<aside class="sidebar">
<div class="brand"><div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div><div><strong>ITayo</strong><span>Admin</span></div></div>
<nav class="nav"><a href="dashboard.php" class=""><i class="fa-solid fa-grid-2"></i>Dashboard</a>
<a href="users.php" class=""><i class="fa-solid fa-users"></i>Users</a>
<a href="courses.php" class=""><i class="fa-solid fa-book-open"></i>Courses</a>
<a href="subjects.php" class="active"><i class="fa-solid fa-book"></i>Subjects</a>
<a href="sections.php" class=""><i class="fa-solid fa-layer-group"></i>Sections</a><a href="../google_account.php"><i class="fa-brands fa-google"></i>Google Account</a>
<div class="nav-sep"></div><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sign out</a></nav>
</aside>
<main class="main">
<header class="topbar"><div><span class="eyebrow">Admin portal</span><h1>Subjects</h1></div><div class="user-chip"><div class="avatar"><?= e(strtoupper(substr($user["first_name"],0,1).substr($user["last_name"],0,1))) ?></div><div><b><?= e($user['first_name'].' '.$user['last_name']) ?></b><span>Admin</span></div></div></header>
<div class="content"><?php
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$name=post('name');$code=post('code');$desc=post('description');if($name&&$code){$pdo->prepare("INSERT INTO subjects(name,code,description) VALUES(?,?,?)")->execute([$name,$code,$desc]);flash('success','Subject created.');redirect('admin/subjects.php');}}
$rows=$pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
?>
<div class="section-head"><div><span class="eyebrow">Academic setup</span><h2>Subjects</h2></div></div>
<div class="grid two"><section class="card card-pad"><h3>Add Subject</h3><br><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-group"><label>Name</label><input class="input" name="name" required></div><br><div class="form-group"><label>Code</label><input class="input" name="code" required></div><br><div class="form-group"><label>Description</label><textarea class="textarea" name="description"></textarea></div><br><button class="btn btn-primary">Add Subject</button></form></section>
<section class="card card-pad"><h3>Subject List</h3><br><div class="list"><?php foreach($rows as $r):?><div class="list-row"><div class="stat-icon"><i class="fa-solid fa-book"></i></div><div class="list-main"><strong><?=e($r['name'])?></strong><span><?=e($r['code'])?></span></div></div><?php endforeach;?></div></section></div>
</div>
</main></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
